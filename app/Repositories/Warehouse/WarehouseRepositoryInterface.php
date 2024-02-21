<?php

namespace App\Repositories\Warehouse;

interface WarehouseRepositoryInterface
{
    public function create(array $data);

    public function update(string $warehouseId, array $data);

    public function find(string $warehouseId);

    public function findAll();
    
    // public function findByParameters(array $parameters);

    public function delete(string $warehouseId);


}