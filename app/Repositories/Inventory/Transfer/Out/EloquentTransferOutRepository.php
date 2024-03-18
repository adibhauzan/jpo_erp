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
            'stock_out',
            'stock_rib_out',
            'attachment_image',
            'price',
            'status',
            'type',
        )->findOrFail($poId);

        return $transferIn;
    }

    public function findAll()
    {
        $transferOut = SalesOrder::all();

        return $transferOut;
    }

    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }
    // public function receive(string $poId, int $quantityStockReceived, int $quantityRibReceived)
    // {
    //     $purchaseOrder = PurchaseOrder::findOrFail($poId);

    //     if ($quantityStockReceived > $purchaseOrder->stock && $quantityRibReceived > $purchaseOrder->stock_rib) {
    //         throw new \Exception('Quantity received exceeds available stock');
    //     } else if ($quantityStockReceived > $purchaseOrder->stock) {
    //         throw new \Exception('Quantity Stock Receive exceeds available stock');
    //     } else if ($quantityRibReceived > $purchaseOrder->stock_rib) {
    //         throw new \Exception('Quantity rib received exceeds available stock rib');
    //     }

    //     $purchaseOrder->stock -= $quantityStockReceived;
    //     $purchaseOrder->stock_rib -= $quantityRibReceived;

    //     $purchaseOrder->stock_rev += $quantityStockReceived;
    //     $purchaseOrder->stock_rib_rev += $quantityRibReceived;

    //     if ($purchaseOrder->stock == 0 && $purchaseOrder->stock_rib == 0) {
    //         $purchaseOrder->status = 'done';
    //     } else {
    //         $purchaseOrder->status = 'received';
    //     }

    //     $purchaseOrder->save();

    //     return $purchaseOrder;
    // }
}
