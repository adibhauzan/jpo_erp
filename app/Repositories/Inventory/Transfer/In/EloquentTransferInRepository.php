<?php

namespace App\Repositories\Inventory\Transfer\In;

use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\Transfer\In\TransferInRepositoryInterface;

class EloquentTransferInRepository implements TransferInRepositoryInterface
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
        $transferIn = PurchaseOrder::select(
            'id',
            'contact_id',
            'warehouse_id',
            'no_po',
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
        $transferIn = PurchaseOrder::select(
            'id',
            'contact_id',
            'warehouse_id',
            'no_po',
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
        )->get();

        return $transferIn;
    }

    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }
    public function receive(string $poId, int $quantityStockRollReceived, int $quantityKgReceived, int $quantityRibReceived)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($poId);

        // Memeriksa apakah jumlah yang diterima melebihi stok yang tersedia
        if ($quantityStockRollReceived > $purchaseOrder->stock_roll || $quantityKgReceived > $purchaseOrder->stock_kg || $quantityRibReceived > $purchaseOrder->stock_rib) {
            throw new \Exception('Quantity received exceeds available stock');
        }

        // Memeriksa apakah jumlah yang diterima melebihi stok roll yang tersedia
        if ($quantityStockRollReceived > $purchaseOrder->stock_roll) {
            throw new \Exception('Quantity Stock Roll Receive exceeds available stock roll');
        }

        // Memeriksa apakah jumlah yang diterima melebihi stok kg yang tersedia
        if ($quantityKgReceived > $purchaseOrder->stock_kg) {
            throw new \Exception('Quantity kg received exceeds available stock kg');
        }

        // Memeriksa apakah jumlah yang diterima melebihi stok rib yang tersedia
        if ($quantityRibReceived > $purchaseOrder->stock_rib) {
            throw new \Exception('Quantity rib received exceeds available stock rib');
        }

        // Mengurangi stok yang diterima dari stok utama
        $purchaseOrder->stock_roll -= $quantityStockRollReceived;
        $purchaseOrder->stock_kg -= $quantityKgReceived;
        $purchaseOrder->stock_rib -= $quantityRibReceived;

        // Menambahkan stok yang diterima ke stok revisi
        $purchaseOrder->stock_roll_rev += $quantityStockRollReceived;
        $purchaseOrder->stock_kg_rev += $quantityKgReceived;
        $purchaseOrder->stock_rib_rev += $quantityRibReceived;

        // Memperbarui status pesanan berdasarkan stok yang tersisa
        if ($purchaseOrder->stock_roll == 0 && $purchaseOrder->stock_kg == 0 && $purchaseOrder->stock_rib == 0) {
            $purchaseOrder->status = 'done';
        } else {
            $purchaseOrder->status = 'received';
        }

        $purchaseOrder->save();

        return $purchaseOrder;
    }
}
