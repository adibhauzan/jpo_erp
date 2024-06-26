<?php

namespace App\Repositories\SalesOrder;


interface SalesOrderRepositoryInterface
{
    public function create(array $data);

    public function update(string $soId, array $data, $token);

    public function find(string $soId);

    public function findAll();

    public function getBySku(string $sku);

    public function getAllSku();
}