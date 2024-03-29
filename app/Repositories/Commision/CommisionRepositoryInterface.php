<?php

namespace App\Repositories\Commision;

interface CommisionRepositoryInterface
{
    // public function create(array $data);

    // public function update(string $bankId, array $data);

    public function find(string $commisionId);

    public function findAll();

    // public function findByParameters(array $parameters);

    // public function delete(string $bankId);

    public function pay(string $commisionId, $paid_price, $nama_bank, $nama_rekening, $no_rekening);
}
