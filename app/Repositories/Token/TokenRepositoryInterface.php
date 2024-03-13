<?php

namespace App\Repositories\Token;

interface TokenRepositoryInterface
{
    public function create(array $data);

    public function update(string $tokenId, array $data);

    public function find(string $tokenId);

    public function findAll();

    // public function findByParameters(array $parameters);

    public function delete(string $tokenId);
}
