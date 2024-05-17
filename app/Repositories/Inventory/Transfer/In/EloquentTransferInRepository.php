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

        DB::transaction(function () use ($poId, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived, $date_received, &$purchaseOrder) {
            $purchaseOrder = PurchaseOrder::findOrFail($poId);
            $existingStock = DB::table('stocks')->where('po_id', $poId)->first();

            $totalStockRollReceived = $existingStock ? $existingStock->stock_roll + $quantityStockRollReceived : $quantityStockRollReceived;
            $totalStockKgReceived = $existingStock ? $existingStock->stock_kg + $quantityKgReceived : $quantityKgReceived;
            $totalStockRibReceived = $existingStock ? $existingStock->stock_rib + $quantityRibReceived : $quantityRibReceived;

            $this->validateStockReceived($totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived, $purchaseOrder);

            if (!$existingStock) {
                DB::table('stocks')->insert($this->prepareStockInsertData($poId, $purchaseOrder->warehouse_id, $purchaseOrder->sku, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived, $date_received));
            } else {
                DB::table('stocks')->where('po_id', $poId)->update($this->prepareStockUpdateData($totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived, $date_received));
            }

            $this->updatePurchaseOrderStatus($purchaseOrder, $totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived);
            $this->createBill($purchaseOrder, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived);
        });

        return $purchaseOrder;
    }

    private function validateStockReceived($totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived, $purchaseOrder)
    {
        if ($totalStockRollReceived > $purchaseOrder->stock_roll) {
            throw new \Exception('Jumlah Stock Roll yang diterima melebihi stok roll yang tersedia');
        }

        if ($totalStockKgReceived > $purchaseOrder->stock_kg) {
            throw new \Exception('Jumlah kg yang diterima melebihi stok kg yang tersedia');
        }

        if ($totalStockRibReceived > $purchaseOrder->stock_rib) {
            throw new \Exception('Jumlah rib yang diterima melebihi stok rib yang tersedia');
        }
    }

    private function prepareStockInsertData($poId, $warehouseId, $sku, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived, $date_received)
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'po_id' => $poId,
            'warehouse_id' => $warehouseId,
            'sku' => $sku,
            'stock_roll' => $quantityStockRollReceived,
            'stock_kg' => $quantityKgReceived,
            'stock_rib' => $quantityRibReceived,
            'date_received' => $date_received,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function prepareStockUpdateData($totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived, $date_received)
    {
        return [
            'stock_roll' => $totalStockRollReceived,
            'stock_kg' => $totalStockKgReceived,
            'stock_rib' => $totalStockRibReceived,
            'date_received' => $date_received,
            'updated_at' => now(),
        ];
    }

    private function updatePurchaseOrderStatus($purchaseOrder, $totalStockRollReceived, $totalStockKgReceived, $totalStockRibReceived)
    {
        $purchaseOrder->status = ($totalStockRollReceived == $purchaseOrder->stock_roll &&
            $totalStockKgReceived == $purchaseOrder->stock_kg &&
            $totalStockRibReceived == $purchaseOrder->stock_rib) ? 'done' : 'received';
        $purchaseOrder->save();
    }

    private function createBill($purchaseOrder, $quantityStockRollReceived, $quantityKgReceived, $quantityRibReceived)
    {
        $uuid = Uuid::uuid4()->toString();
        $currentDate = now();
        $no_bill = 'BILL/' . $currentDate->format('Y') . '/' . $currentDate->format('m') . '/' . $currentDate->format('d') . '/' . (Bill::count() + 1);

        DB::table('bills')->insert([
            'id' => $uuid,
            'no_bill' => $no_bill,
            'purchase_id' => $purchaseOrder->id,
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
    }
}
