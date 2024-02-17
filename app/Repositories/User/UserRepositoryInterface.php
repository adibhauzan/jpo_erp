<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function create(array $data);

    public function update(string $userId, array $data);

    public function find(string $userId);

    public function delete(string $userId);

    public function attemptLogin(array $credentials);
}