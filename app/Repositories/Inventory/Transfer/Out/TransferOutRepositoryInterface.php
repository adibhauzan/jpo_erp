<?php

namespace App\Repositories\Inventory\Transfer\Out;


interface TransferOutRepositoryInterface
{
    public function create(array $data);

    public function update(string $outId, array $data);

    public function find(string $poId);

    public function findAll();

    public function receive(string $soId, float $quantityStockRollReceived, float $quantityKgReceived, float $quantityRibReceived, string $date_received);

    // public function delete(string $poId);
}
