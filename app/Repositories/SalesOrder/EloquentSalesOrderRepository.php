<?php

namespace App\Repositories\SalesOrder;

use App\Models\Warehouse;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\SalesOrder\SalesOrderRepositoryInterface;

class EloquentSalesOrderRepository implements SalesOrderRepositoryInterface
{


    public function create(array $data)
    {
        $stockRev = (int)$data['stock_rev'];
        $stockRibRev = (int)$data['stock_rib_rev'];

        DB::table('purchase_orders AS po')
            ->where('po.sku', $data['sku'])
            ->update([
                'po.stock_rev' => DB::raw("po.stock_rev - $stockRev"),
                'po.stock_rib_rev' => DB::raw("po.stock_rib_rev - $stockRibRev")
            ]);

        return SalesOrder::create($data);
    }


    public function find(string $soId)
    {
        $salesOrder =  DB::table('sales_orders')
            ->join('purchase_orders', 'purchase_orders.sku', '=', 'sales_orders.sku')
            ->select('sales_orders.broker_fee', 'purchase_orders.stock_rev', 'purchase_orders.stock_rib_rev', 'purchase_orders.price', 'sales_orders.id')
            ->where('sales_orders.id', '=', $soId)->get();

        return $salesOrder;
    }
    public function findAll()
    {
        return SalesOrder::all();
    }


    public function getBySku(string $sku)
    {
        $skuData = DB::table('purchase_orders')
            ->join('contacts', 'purchase_orders.contact_id', '=', 'contacts.id')
            ->join('warehouses', 'purchase_orders.warehouse_id', '=', 'warehouses.id')
            ->select('purchase_orders.no_do', 'purchase_orders.sku', 'purchase_orders.warehouse_id', 'warehouses.name as from', 'purchase_orders.contact_id', 'contacts.name as to', 'purchase_orders.stock_rev', 'purchase_orders.price')
            ->where('purchase_orders.sku', '=', $sku)->get();

        return $skuData;
    }

    public function update(string $soId, array $data)
    {
        $updateSo = DB::table('purchase_orders AS po')
            ->join('sales_orders AS so', 'po.sku', '=', 'so.sku')
            ->where('so.id', $soId)
            ->update(['po.price' => $data['price'], 'so.broker_fee' => $data['broker_fee'], 'po.stock_rev' => $data['stock_rev']]);

        return $updateSo;
    }
}
