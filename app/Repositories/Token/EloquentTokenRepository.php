<?php

namespace App\Repositories\Token;

use App\Models\Token;

class EloquentTokenRepository implements TokenRepositoryInterface
{

    public function create(array $data)
    {
        return Token::create($data);
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
        return Token::findOrFail($tokenId);
    }

    public function findAll()
    {
        return Token::all();
    }

    public function delete(string $tokenId)
    {
        $token = $this->find($tokenId);
        $token->delete();
    }
}
