<?php

namespace App\Repositories\Inventory\Stock;

use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\Stock\StockRepositoryInterface;

class EloquentStockRepository implements StockRepositoryInterface
{
    // public function create(array $data)
    // {
    //     $user = Auth::user();

    //     $warehouseStoreId = Warehouse::find($data['warehouse_id'])->store->id;

    //     if ($user->store_id !== $warehouseStoreId) {
    //         throw new \Exception( 'The selected warehouse is not associated with your store.');
    //     }
    //   return  PurchaseOrder::create($data);
    // }

    // public function update(string $poId, array $data)
    // {
    //     $po = $this->find($poId);
    //     $po->update($data);
    //     $po->refresh();
    //     return $po;
    // }

    public function find(string $poId)
    {
        // Perbaikan sintaksis dalam kueri
        $stock = PurchaseOrder::select(
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
            'stock_roll_rev',
            'stock_kg_rev',
            'stock_rib_rev',
            'attachment_image',
            'price',
            'status'
        )->where('id', $poId)->first(); // Perbaikan sintaksis where dan gunakan first() untuk mengambil satu baris

        return $stock;
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
            'stock_roll_rev',
            'stock_kg_rev',
            'stock_rib_rev',
            'attachment_image',
            'price',
            'status',
            'created_at',
            'updated_at'
        )->orderBy('created_at', 'desc')->get();
        return $transferIn;
    }

    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }

    public function update(string $poId, array $data)
    {
        $po = PurchaseOrder::all();
        $po->update($data);
        $po->refresh();
        return $po;
    }
}
