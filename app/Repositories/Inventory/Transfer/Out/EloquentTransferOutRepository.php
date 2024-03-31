<?php

namespace App\Repositories\Inventory\Transfer\Out;

use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\Transfer\Out\TransferOutRepositoryInterface;

class EloquentTransferOutRepository implements TransferOutRepositoryInterface
{

    public function create(array $data)
    {
        $user = Auth::user();

        $warehouseStoreId = Warehouse::find($data['warehouse_id'])->store->id;

        if ($user->store_id !== $warehouseStoreId) {
            throw new \Exception('The selected warehouse is not associated with your store.');
        }
        return  PurchaseOrder::create($data);
    }

    public function update(string $poId, array $data)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($poId);
        $purchaseOrder->update($data);
        return $purchaseOrder;
    }

    public function find(string $poId)
    {
        $transferIn = SalesOrder::select(
            'id',
            'contact_id',
            'broker',
            'warehouse_id',
            'no_so',
            'no_do',
            'date',
            'nama_barang',
            'grade',
            'sku',
            'description',
            'ketebalan',
            'setting',
            'gramasi',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'attachment_image',
            'price',
            'status',
        )->findOrFail($poId);

        return $transferIn;
    }

    public function findAll()
    {
        $transferOut = SalesOrder::select(
            'id',
            'contact_id',
            'warehouse_id',
            'no_so',
            'no_do',
            'date',
            'nama_barang',
            'grade',
            'sku',
            'description',
            'ketebalan',
            'setting',
            'gramasi',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'attachment_image',
            'price',
            'status',
            'created_at',
            'updated_at'
        )
            ->with(['contact:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $transferOut;
    }

    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }

    public function receive(string $soId, float $quantityStockRollReceived, float $quantityKgReceived, float $quantityRibReceived, string $date_received)
    {
        $salesOrder = SalesOrder::findOrFail($soId);

        if ($quantityStockRollReceived > $salesOrder->stock_roll || $quantityKgReceived > $salesOrder->stock_kg || $quantityRibReceived > $salesOrder->stock_rib) {
            throw new \Exception('Quantity received exceeds available stock');
        }

        if ($quantityStockRollReceived > $salesOrder->stock_roll) {
            throw new \Exception('Quantity Stock Roll Receive exceeds available stock roll');
        }

        if ($quantityKgReceived > $salesOrder->stock_kg) {
            throw new \Exception('Quantity kg received exceeds available stock kg');
        }

        if ($quantityRibReceived > $salesOrder->stock_rib) {
            throw new \Exception('Quantity rib received exceeds available stock rib');
        }

        // Menambahkan stok yang diterima ke stok revisi
        $salesOrder->stock_roll_rev += $quantityStockRollReceived;
        $salesOrder->stock_kg_rev += $quantityKgReceived;
        $salesOrder->stock_rib_rev += $quantityRibReceived;
        $salesOrder->date_received = $date_received;

        // Memperbarui status pesanan berdasarkan stok yang tersisa
        if ($salesOrder->stock_roll == 0 && $salesOrder->stock_kg == 0 && $salesOrder->stock_rib == 0) {
            $salesOrder->status = 'done';
        } else {
            $salesOrder->status = 'received';
        }

        $salesOrder->save();

        return $salesOrder;
    }
}
