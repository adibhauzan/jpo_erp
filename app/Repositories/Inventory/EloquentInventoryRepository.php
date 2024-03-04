<?php

namespace App\Repositories\Inventory;

use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Inventory\InventoryRepositoryInterface;


class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function create(array $data)
    {
        $user = Auth::user();

        $warehouseStoreId = Warehouse::find($data['warehouse_id'])->store->id;

        if ($user->store_id !== $warehouseStoreId) {
            throw new \Exception('The selected warehouse is not associated with your store.');
        }
        return  Inventory::create($data);
    }


    public function update(string $inventoryId, array $data)
    {
        $inventory = $this->find($inventoryId);
        $inventory->update($data);
        $inventory->refresh();
        return $inventory;
    }

    public function find(string $inventoryId)
    {
        return Inventory::findOrFail($inventoryId);
    }
    public function findAll()
    {
        return Inventory::all();
    }

    public function delete(string $inventoryId)
    {
        $inventory = $this->find($inventoryId);
        $inventory->delete();
    }
}