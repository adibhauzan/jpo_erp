<?php

namespace App\Repositories\Store;

use App\Models\Store;
use App\Repositories\Store\StoreRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function create(array $data)
    {
        return Store::create($data);
    }

    public function update(string $storeId, array $data)
    {
        $store = $this->find($storeId);
        $store->update($data);
        $store->refresh();
        return $store;
    }

    public function find(string $storeId)
    {
        return Store::findOrFail($storeId);
    }
    public function findAll()
    {
        return Store::all();
    }



    // public function findByParameters(array $parameters)
    // {
    //     $query = Store::query();

    //     foreach ($parameters as $key => $value) {
    //         $query->where($key, $value);
    //     }

    //     return $query->get();
    // }

    public function delete(string $storeId)
    {
        $store = $this->find($storeId);
        $store->delete();
    }

    public function banStore($storeId)
    {
        $store = $this->find($storeId);
        $store->ban();
    }
    
    public function unBanStore(string $storeId)
    {
        $store = $this->find($storeId);
        $store->unban();
    }

}