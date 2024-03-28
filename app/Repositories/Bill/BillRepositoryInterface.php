<?php

namespace App\Repositories\Bill;

interface BillRepositoryInterface
{
    // public function create(array $data);

    // public function update(string $bankId, array $data);

    public function find(string $billId);

    public function findAll();

    // public function findByParameters(array $parameters);

    // public function delete(string $bankId);
}
