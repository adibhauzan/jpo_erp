<?php

namespace App\Repositories\SalesOrder;

use Ramsey\Uuid\Uuid;
use App\Models\Invoice;
use App\Models\Commision;
use App\Models\Warehouse;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Token;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\SalesOrder\SalesOrderRepositoryInterface;

class EloquentSalesOrderRepository implements SalesOrderRepositoryInterface
{
    public function create(array $data)
    {
        // Mengambil data purchase order berdasarkan SKU
        $purchaseOrderData = PurchaseOrder::where('sku', $data['sku'])->first();

        // Jika tidak ada data purchase order, throw exception
        if (!$purchaseOrderData) {
            throw new \Exception("Data purchase order tidak ditemukan.");
        }


        // Mengurangi stok purchase order berdasarkan SKU dalam satu transaksi
        $salesOrder = DB::transaction(function () use ($purchaseOrderData, $data) {

            // Validasi agar contact_id dan broker tidak sama
            if ($data['contact_id'] == $data['broker']) { // Perbaikan 1: Menggunakan operator perbandingan
                throw new \Exception("Contact dan broker tidak boleh sama.");
            }


            if (
                $data['stock_roll'] > $purchaseOrderData->stock_roll_rev &&
                $data['stock_kg'] > $purchaseOrderData->stock_kg_rev &&
                $data['stock_rib'] > $purchaseOrderData->stock_rib_rev
            ) {
                throw new \Exception("Stok yang diminta melebihi stok yang tersedia dalam purchase order.");
            } else if ($data['stock_roll'] > $purchaseOrderData->stock_roll_rev) {
                throw new \Exception("stok_roll yang diminta melebihi stok_roll yang tersedia dalam Stock.");
            } else if ($data['stock_kg'] > $purchaseOrderData->stock_kg_rev) {
                throw new \Exception("stok_kg yang diminta melebihi stok_kg yang tersedia dalam Stock.");
            } else if ($data['stock_rib'] > $purchaseOrderData->stock_rib_rev) {
                throw new \Exception("stock_rib yang diminta melebihi stock_rib yang tersedia dalam Stock.");
            }
            $purchaseOrderData->stock_roll_rev -= $data['stock_roll'];
            $purchaseOrderData->stock_kg_rev -= $data['stock_kg'];
            $purchaseOrderData->stock_rib_rev -= $data['stock_rib'];
            $purchaseOrderData->save();


            $isBroker = ($data['broker'] != null && $data['broker_fee'] != null) ? 1 : 0;
            // Membuat sales order baru dengan menggunakan data yang diperoleh
            $salesOrder = SalesOrder::create([
                'sku' => $data['sku'],
                'no_so' => $data['no_so'],
                'no_do' => $data['no_do'],
                'date' => $data['date'],
                'contact_id' => $data['contact_id'],
                'broker' => $data['broker'],
                'broker_fee' => $data['broker_fee'],
                'price' => $data['price'],
                'nama_barang' => $purchaseOrderData->nama_barang,
                'ketebalan' => $purchaseOrderData->ketebalan,
                'setting' => $purchaseOrderData->setting,
                'gramasi' => $purchaseOrderData->gramasi,
                'grade' => $purchaseOrderData->grade,
                'description' => $purchaseOrderData->description,
                'nama_barang' => $purchaseOrderData->nama_barang,
                'attachment_image' => $purchaseOrderData->attachment_image,
                'stock_roll' => $data['stock_roll'],
                'stock_kg' => $data['stock_kg'],
                'stock_rib' => $data['stock_rib'],
                'warehouse_id' => $data['warehouse_id'],
            ]);

            // Membuat invoice baru
            $uuid = Uuid::uuid4()->toString();
            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $totalOrders = Invoice::count();

            $sequence = $totalOrders + 1;
            $no_invoice = 'INVOICE/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
            $no_commision = 'COMMISIONS/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
            $invoiceData = [
                'id' => $uuid,
                'sales_order_id' => $salesOrder->id,
                'no_invoice' => $no_invoice,
                'warehouse_id' => $salesOrder->warehouse_id,
                'contact_id' => $salesOrder->contact_id,
                'bank_id' => $salesOrder->bank_id,
                'sku' => $salesOrder->sku,
                'nama_barang' => $salesOrder->nama_barang,
                'sell_price' => $salesOrder->price,
                'ketebalan' => $salesOrder->ketebalan,
                'setting' => $salesOrder->setting,
                'gramasi' => $salesOrder->gramasi,
                'stock_roll' => $salesOrder->stock_roll,
                'stock_kg' => $salesOrder->stock_kg,
                'stock_rib' => $salesOrder->stock_rib,
                'paid_price' => 0,
                'is_broker' => $isBroker,
                'broker' => $salesOrder->broker,
                'broker_fee' => $salesOrder->broker_fee,
                'paid_status' => 'unpaid',
                'created_at' => $currentDate,
                'updated_at' => $currentDate
            ];

            Invoice::create($invoiceData);

            if ($isBroker) {
                $commision = Commision::create([
                    'no_commision' => $no_commision,
                    'ref_dokumen_id' => $invoiceData['no_invoice'],
                    'broker' => $data['broker'],
                    'nama_bank' => "",
                    'nama_rekening' => "",
                    'no_rekening' => "",
                    'broker_fee' => $data['broker_fee'],
                    'paid_price' => 0,
                    'paid_status' => 'unpaid',
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate
                ]);
            }
            return $salesOrder;
        });

        return $salesOrder;
    }

    public function find(string $soId)
    {
        $salesOrder = SalesOrder::findOrFail($soId);

        return $salesOrder;
    }
    public function findAll()
    {
        $salesOrders = SalesOrder::select(
            'id',
            'status',
            'contact_id',
            'warehouse_id',
            'no_so',
            'no_do',
            'date',
            'broker',
            'broker_fee',
            'sku',
            'nama_barang',
            'grade',
            'description',
            'attachment_image',
            'ketebalan',
            'setting',
            'gramasi',
            'price',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'stock_roll_rev',
            'stock_kg_rev',
            'stock_rib_rev',
            'date_received',
            'created_at',
            'updated_at'
        )
            ->with(['contact:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $salesOrders;
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

    public function update(string $soId, array $data, $token)
    {
        $tokenInput = DB::table('tokens')->select('token', 'status')->where('token', '=', $token)->first();

        if (!$tokenInput) {
            throw new \Exception("Token yang diinput tidak valid");
        } else if ($tokenInput->status !== 'not') {
            throw new \Exception("Token yang diinput sudah digunakan");
        }

        $salesOrder = SalesOrder::where('id', $soId)
            ->select('id', 'broker_fee', 'harga_jual', 'stock_roll', 'stock_kg', 'stock_rib')
            ->first();

        if (!$salesOrder) {
            throw new \Exception("Id Sales Order Tidak Ditemukan");
        }

        $updateSo = $salesOrder->update([
            'broker_fee' => $data['broker_fee'],
            'harga_jual' => $data['harga_jual'],
            'stock_roll' => $data['stock_roll'],
            'stock_kg' => $data['stock_kg'],
            'stock_rib' => $data['stock_rib']
        ]);

        if ($updateSo) {
            DB::table('tokens')->where('token', $token)->update(['status' => 'used']);
            return $updateSo;
        } else {
            throw new \Exception("Gagal memperbarui sales order.");
        }
    }


    public function getAllSku()
    {
        $skus = DB::table('purchase_orders')->pluck('sku');

        return $skus;
    }
}