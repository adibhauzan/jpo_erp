<?php

namespace App\Repositories\Warehouse;

use App\Models\Warehouse;
use App\Repositories\Warehouse\WarehouseRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function create(array $data)
    {
        return Warehouse::create($data);
    }

    public function update(string $warehouseId, array $data)
    {
        $warehouse = $this->find($warehouseId);
        $warehouse->update($data);
        $warehouse->refresh();
        return $warehouse;
    }

    public function find(string $warehouseId)
    {
        return Warehouse::findOrFail($warehouseId);
    }
    public function findAll()
    {
        return Warehouse::orderBy('created_at', 'desc')->get();
    }



    // public function findByParameters(array $parameters)
    // {
    //     $query = warehouse::query();

    //     foreach ($parameters as $key => $value) {
    //         $query->where($key, $value);
    //     }

    //     return $query->get();
    // }

    public function delete(string $warehouseId)
    {
        $warehouse = $this->find($warehouseId);
        $warehouse->delete();
    }

    public function banWarehouse(string $warehouseId)
    {
        $warehouse = $this->find($warehouseId);
        $warehouse->ban();
    }

    public function unBanWarehouse(string $warehouseId)
    {
        $warehouse = $this->find($warehouseId);
        $warehouse->unban();
    }

    public function getByLoggedInUser()
    {
        $storeId = Auth::user()->store_id;
        return Warehouse::where('store_id', $storeId)
            ->where('status', 'active')
            ->get();
    }
}
