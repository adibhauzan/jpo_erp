<?php

namespace App\Repositories\Inventory\Transfer\In;


interface TransferInRepositoryInterface
{
    public function create(array $data);

    public function update(string $inId, array $data);

    public function find(string $poId);

    public function findAll();

    public function receive(string $poId, float $quantityStockRollReceived, float $quantityKgReceived, float $quantityRibReceived, string $date_received);


    // public function delete(string $poId);
}
