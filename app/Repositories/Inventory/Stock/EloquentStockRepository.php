<?php

namespace App\Repositories\Inventory\Stock;

use App\Models\Stock;
use Ramsey\Uuid\Uuid;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\Stock\StockRepositoryInterface;

class EloquentStockRepository implements StockRepositoryInterface
{

    public function findAll()
    {
        $stock = Stock::select(
            'id',
            'warehouse_id',
            'po_id',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'date_received',
            'created_at',
            'updated_at'
        )->orderBy('created_at', 'desc')->get();
        return $stock;
    }

    public function find(string $stockId)
    {
        $stock = Stock::select(
            'id',
            'warehouse_id',
            'po_id',
            'nama_barang',
            'grade',
            'sku',
            'description',
            'attachment_image',
            'ketebalan',
            'setting',
            'gramasi',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'price',
        )->where('id', $stockId)->first();

        return $stock;
    }

    public function delete(string $stockId)
    {
        $stock = $this->find($stockId);
        $stock->delete();
    }

    public function update(string $stockId, array $data)
    {
        $stock = Stock::all();
        $stock->update($data);
        $stock->refresh();
        return $stock;
    }


    public function getWarehouseIdsWithStock($sku)
    {
        return Stock::with(['warehouse:id,name'])
            ->where('sku', $sku)
            ->where(function ($query) {
                $query->where('stock_roll', '<>', 0.00)
                    ->orWhere('stock_kg', '<>', 0.00)
                    ->orWhere('stock_rib', '<>', 0.00);
            })
            ->get(['id', 'warehouse_id']);
    }

    public function getAllStocksIdAndSku()
    {
        return DB::select('SELECT DISTINCT sku FROM stocks');
    }

    public function getWarehouseByLoggedInUser()
    {
        $storeId = Auth::user()->store_id;
        return Warehouse::where('store_id', $storeId)
            ->where('status', 'active')
            ->get();
    }

    public function transferStock($sku, $warehouseIdFrom, $warehouseIdTo, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib, $tanggalDiterima)
    {
        DB::transaction(function () use ($sku, $warehouseIdFrom, $warehouseIdTo, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib, $tanggalDiterima) {
            if ($jumlahStockRoll < 0 || $jumlahStockKg < 0 || $jumlahStockRib < 0) {
                throw new \Exception('Nilai stok yang ditransfer tidak boleh negatif');
            }

            $stokExistingFrom = $this->getExistingStock($sku, $warehouseIdFrom);

            $this->validateStock($stokExistingFrom, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib);

            $this->updateSourceWarehouseStock($stokExistingFrom, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib);

            $this->updateOrInsertDestinationWarehouseStock($sku, $warehouseIdTo, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib, $tanggalDiterima);
        });
    }

    private function getExistingStock($sku, $warehouseId)
    {
        $stokExisting = DB::table('stocks')
            ->where('sku', $sku)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stokExisting) {
            throw new \Exception('Stok tidak ditemukan di gudang asal');
        }

        return $stokExisting;
    }

    private function validateStock($stokExisting, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib)
    {
        if ($stokExisting->stock_roll < $jumlahStockRoll || $stokExisting->stock_kg < $jumlahStockKg || $stokExisting->stock_rib < $jumlahStockRib) {
            throw new \Exception('Jumlah stok yang diminta untuk transfer melebihi stok yang tersedia di gudang asal');
        }
    }

    private function updateSourceWarehouseStock($stokExisting, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib)
    {
        DB::table('stocks')->where('id', $stokExisting->id)->update([
            'stock_roll' => $stokExisting->stock_roll - $jumlahStockRoll,
            'stock_kg' => $stokExisting->stock_kg - $jumlahStockKg,
            'stock_rib' => $stokExisting->stock_rib - $jumlahStockRib,
            'updated_at' => now(),
        ]);
    }

    private function updateOrInsertDestinationWarehouseStock($sku, $warehouseIdTo, $jumlahStockRoll, $jumlahStockKg, $jumlahStockRib, $tanggalDiterima)
    {
        $poId = DB::table('stocks')->where('sku', $sku)->value('po_id');

        if (!$poId) {
            throw new \Exception('po_id tidak ditemukan untuk sku yang diberikan');
        }

        $stokExistingTo = DB::table('stocks')
            ->where('po_id', $poId)
            ->where('sku', $sku)
            ->where('warehouse_id', $warehouseIdTo)
            ->first();

        if ($stokExistingTo) {
            DB::table('stocks')->where('id', $stokExistingTo->id)->update([
                'stock_roll' => $stokExistingTo->stock_roll + $jumlahStockRoll,
                'stock_kg' => $stokExistingTo->stock_kg + $jumlahStockKg,
                'stock_rib' => $stokExistingTo->stock_rib + $jumlahStockRib,
                'date_received' => $tanggalDiterima,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('stocks')->insert([
                'id' => Uuid::uuid4()->toString(),
                'po_id' => $poId,
                'warehouse_id' => $warehouseIdTo,
                'sku' => $sku,
                'stock_roll' => $jumlahStockRoll,
                'stock_kg' => $jumlahStockKg,
                'stock_rib' => $jumlahStockRib,
                'date_received' => $tanggalDiterima,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
