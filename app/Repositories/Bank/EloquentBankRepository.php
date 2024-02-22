<?php

namespace App\Repositories\Bank;

use App\Models\Bank;

class EloquentBankRepository implements BankRepositoryInterface
{

    public function create(array $data)
    {
        return Bank::create($data);
    }

    public function update(string $bankId, array $data)
    {
        $bank = $this->find($bankId);
        $bank->update($data);
        $bank->refresh();
        return $bank;
    }

    public function find(string $bankId)
    {
        return Bank::findOrFail($bankId);
    }

    public function findAll()
    {
        return Bank::all();
    }

    public function delete(string $bankId)
    {
        $bank = $this->find($bankId);
        $bank->delete();
    }
}
