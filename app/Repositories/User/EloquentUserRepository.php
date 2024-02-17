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
        return $user;
    }

    public function find(string $userId)
    {
        return User::findOrFail($userId);
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
}

?>