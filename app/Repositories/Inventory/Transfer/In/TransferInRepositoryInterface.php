<?php

namespace App\Repositories\Inventory\Transfer\In;


interface TransferInRepositoryInterface
{
    // public function create(array $data);

    // public function update(string $poId, array $data);

    public function find(string $poId);

    public function findAll();

    // public function delete(string $poId);
}