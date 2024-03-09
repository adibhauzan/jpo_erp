<?php

namespace App\Repositories\SalesOrder;


interface SalesOrderRepositoryInterface
{
    public function create(array $data);

    public function update(string $soId, array $data);

    public function find(string $poId);

    public function findAll();

    // public function delete(string $poId);
    public function getBySku(string $sku);
}