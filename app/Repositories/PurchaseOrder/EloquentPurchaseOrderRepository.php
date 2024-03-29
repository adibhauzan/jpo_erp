<?php

namespace App\Repositories\PurchaseOrder;

use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Repositories\po\poRepositoryInterface;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
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
        $po = $this->find($poId);
        $po->update($data);
        $po->refresh();
        return $po;
    }

    public function find(string $poId)
    {
        return PurchaseOrder::findOrFail($poId);
    }
    public function findAll()
    {
        $purchaseOrders = PurchaseOrder::select(
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
        )
            ->with(['contact:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $purchaseOrders;
    }


    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }
}
