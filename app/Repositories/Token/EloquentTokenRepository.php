<?php

namespace App\Repositories\Token;

use Exception;
use App\Models\Token;
use App\Models\ValidationTokenUpdate;

class EloquentTokenRepository implements TokenRepositoryInterface
{

    public function create(array $data)
    {
        return ValidationTokenUpdate::create($data);
    }

    public function update(string $tokenId, array $data)
    {
        $token = $this->find($tokenId);
        $token->update($data);
        $token->refresh();
        return $token;
    }

    public function find(string $tokenId)
    {
        return ValidationTokenUpdate::findOrFail($tokenId);
    }

    public function findTokenUpdate(string $tokenUpdate)
    {
        try {
            $token = ValidationTokenUpdate::where('token_update', $tokenUpdate)->firstOrFail();

            if ($token->status == "used") {
                throw new \Exception('Token sudah digunakan');
            }

            return $token;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            throw new \Exception('Token tidak ditemukan');
        }
    }

    public function findAll()
    {
        return ValidationTokenUpdate::orderBy('created_at', 'desc')->get();
    }

    public function delete(string $tokenId)
    {
        $token = $this->find($tokenId);
        $token->delete();
    }
}
