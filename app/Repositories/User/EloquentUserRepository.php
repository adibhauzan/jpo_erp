<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(string $userId, array $data)
    {
        $user = $this->find($userId);
        $user->update($data);
        $user->refresh();
        return $user;
    }

    public function findAll()
    {
        return User::all();
    }

    public function find(string $userId)
    {
        return User::findOrFail($userId);
    }

    // public function findByRoles(string $roles)
    // {
    //     return User::where('roles', $roles)->get();
    // }

    // public function findByStatus(string $status)
    // {
    //     return User::where('status', $status)->get();

    // }


    public function findByParameters(array $parameters)
    {
        $query = User::query();

        foreach ($parameters as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get();
    }

    public function delete(string $userId)
    {
        $user = $this->find($userId);
        $user->delete();
    }

    public function attemptLogin(array $credentials)
    {
        if (Auth::attempt($credentials)) {
            return auth()->attempt($credentials);
        }

        return null;
    }
    
    public function banUser($userId)
    {
        $user = $this->find($userId);
        $user->ban();
    }}