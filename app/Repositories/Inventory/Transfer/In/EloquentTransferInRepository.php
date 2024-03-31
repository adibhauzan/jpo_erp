<?php

namespace App\Repositories\Inventory\Transfer\In;

use App\Models\Bill;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\Transfer\In\TransferInRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

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
            'created_at',
            'updated_at'
        )->with(['contact:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $transferIn;
    }

    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }

    public function receive(string $poId, float $quantityStockRollReceived, float $quantityKgReceived, float $quantityRibReceived, string $date_received)
    {

        $purchaseOrder = null;

        DB::transaction(function () use ($poId, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived, $date_received) {
            $purchaseOrder = PurchaseOrder::findOrFail($poId);

            if ($quantityStockRollReceived > $purchaseOrder->stock_roll || $quantityKgReceived > $purchaseOrder->stock_kg || $quantityRibReceived > $purchaseOrder->stock_rib) {
                throw new \Exception('Quantity received exceeds available stock');
            }

            if ($quantityStockRollReceived > $purchaseOrder->stock_roll) {
                throw new \Exception('Quantity Stock Roll Receive exceeds available stock roll');
            }

            if ($quantityKgReceived > $purchaseOrder->stock_kg) {
                throw new \Exception('Quantity kg received exceeds available stock kg');
            }

            if ($quantityRibReceived > $purchaseOrder->stock_rib) {
                throw new \Exception('Quantity rib received exceeds available stock rib');
            }

            // Menambahkan stok yang diterima ke stok revisi
            $purchaseOrder->stock_roll_rev += $quantityStockRollReceived;
            $purchaseOrder->stock_kg_rev += $quantityKgReceived;
            $purchaseOrder->stock_rib_rev += $quantityRibReceived;

            $purchaseOrder->date_received = $date_received;

            // Memperbarui status pesanan berdasarkan stok yang tersisa
            if ($purchaseOrder->stock_roll == $purchaseOrder->stock_roll_rev  && $purchaseOrder->stock_kg ==  $purchaseOrder->stock_kg_rev && $purchaseOrder->stock_rib ==  $purchaseOrder->stock_rib_rev) {
                $purchaseOrder->status = 'done';
            } else {
                $purchaseOrder->status = 'received';
            }

            // Simpan perubahan pada pesanan pembelian
            $purchaseOrder->save();

            $uuid = Uuid::uuid4()->toString();

            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $totalOrders = Bill::count();

            $sequence = $totalOrders + 1;
            $no_bill = 'BILL/' . $year . '/' . $month . '/' . $day . '/' . $sequence;

            DB::table('bills')->insert([
                'id' =>  $uuid,
                'no_bill' => $no_bill,
                'purchase_id' => $poId,
                'nama_barang' => $purchaseOrder->nama_barang,
                'nama_bank' => "",
                'nama_rekening' => "",
                'no_rekening' => "",
                'contact_id' => $purchaseOrder->contact_id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'sku' => $purchaseOrder->sku,
                'ketebalan' => $purchaseOrder->ketebalan,
                'setting' => $purchaseOrder->setting,
                'gramasi' => $purchaseOrder->gramasi,
                'bill_price' => $purchaseOrder->price,
                'stock_roll' => $quantityStockRollReceived,
                'stock_kg' => $quantityKgReceived,
                'stock_rib' => $quantityRibReceived,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $purchaseOrder;
    }
}
