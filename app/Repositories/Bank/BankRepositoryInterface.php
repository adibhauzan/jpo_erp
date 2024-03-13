<?php

namespace App\Repositories\Bank;

interface BankRepositoryInterface
{
    public function create(array $data);

    public function update(string $bankId, array $data);

    public function find(string $bankId);

    public function findAll();

    // public function findByParameters(array $parameters);

    public function delete(string $bankId);
}
