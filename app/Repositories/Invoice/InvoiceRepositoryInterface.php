<?php

namespace App\Repositories\Invoice;

interface InvoiceRepositoryInterface
{
    // public function create(array $data);

    // public function update(string $bankId, array $data);

    public function find(string $invId);

    public function findAll();

    // public function findByParameters(array $parameters);

    // public function delete(string $bankId);
}
