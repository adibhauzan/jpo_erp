<?php

namespace App\Repositories\Inventory\Transfer\In;


interface TransferInRepositoryInterface
{
    public function create(array $data);

    public function update(string $inId, array $data);

    public function find(string $poId);

    public function findAll();

    public function receive(string $poId, int $quantityStockRollReceived,  int $quantityKgReceivedint, int $quantityRibReceived);


    // public function delete(string $poId);
}