<?php

namespace App\Repositories\PurchaseOrder;

interface PurchaseOrderRepositoryInterface
{
    public function create(array $data);

    public function update(string $poId, array $data, $token);

    public function find(string $poId);

    public function findAll();

    public function delete(string $poId);
}
