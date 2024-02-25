<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function create(array $data);

    public function update(string $userId, array $data);

    public function find(string $userId);

    public function findAll();
    
    public function findByParameters(array $parameters);

    // public function findByRoles(string $roles);

    // public function findByStatus(string $status);

    public function delete(string $userId);

    public function attemptLogin(array $credentials);

    public function banUser(string $userId);
    
    public function unBanUser(string $userId);
}