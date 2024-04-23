<?php

namespace App\Repositories\PurchaseOrder;

use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
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

    public function update(string $poId, array $data, string $token)
    {
        $tokenInput = DB::table('tokens')
            ->where('token_update', $token)
            ->where('status', 'not')
            ->first();

        if (!$tokenInput) {
            throw new \Exception("Token yang dimasukkan tidak valid atau sudah digunakan");
        }

        $po = $this->find($poId);

        $updateData = [
            'stock_roll' => $data['stock_roll'] ?? $po->stock_roll,
            'stock_kg' => $data['stock_kg'] ?? $po->stock_kg,
            'stock_rib' => $data['stock_rib'] ?? $po->stock_rib,
        ];

        $updateResult = $po->update($updateData);

        if (!$updateResult) {
            throw new \Exception("Gagal memperbarui Purchase Order. Kemungkinan ID tidak ditemukan atau tidak ada perubahan yang dimasukkan.");
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception("Tidak ada pengguna yang masuk ketika mencoba memperbarui Purchase Order dengan token: $token");
        }

        $userId = $user->id;
        DB::table('tokens')
            ->where('token_update', $tokenInput->token_update)
            ->update(['status' => 'used', 'user_id' => $userId]);

        return $updateData;
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

        $purchaseOrders->transform(function ($item, $key) {
            $item->stock_roll = (float) $item->stock_roll;
            $item->stock_kg = (float) $item->stock_kg;
            $item->stock_rib = (float) $item->stock_rib;
            return $item;
        });

        return $purchaseOrders;
    }


    public function delete(string $poId)
    {
        $po = $this->find($poId);
        $po->delete();
    }
}
