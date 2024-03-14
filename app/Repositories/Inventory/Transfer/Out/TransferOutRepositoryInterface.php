<?php

namespace App\Repositories\Inventory\Transfer\Out;


interface TransferOutRepositoryInterface
{
    public function create(array $data);

    public function update(string $outId, array $data);

    public function find(string $poId);

    public function findAll();

    // public function receive(string $poId, int $quantityReceived, int $quantityRibReceived);

    // public function delete(string $poId);
}