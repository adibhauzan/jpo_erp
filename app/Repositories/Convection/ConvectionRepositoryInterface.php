<?php

namespace App\Repositories\Convection;

interface ConvectionRepositoryInterface
{
    public function create(array $data);

    public function update(string $convectionId, array $data);

    public function find(string $convectionId);

    public function findAll();

    // public function findByParameters(array $parameters);

    public function delete(string $convectionId);

    public function banConvection(string $convectionId);

    public function unBanConvection(string $convectionId);
}