<?php

namespace App\Repositories\Inventory;

interface InventoryRepositoryInterface
{
    public function create(array $data);

    public function update(string $inventoryId, array $data);

    public function find(string $inventoryId);

    public function findAll();

    public function delete(string $inventoryId);
}
