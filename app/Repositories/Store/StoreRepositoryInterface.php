<?php

namespace App\Repositories\Store;

interface StoreRepositoryInterface
{
    public function create(array $data);

    public function update(string $storeId, array $data);

    public function find(string $storeId);

    public function findAll();
    
    // public function findByParameters(array $parameters);

    public function delete(string $storeId);


}