<?php

namespace App\Repositories\Bill;

use App\Models\Bill;

class EloquentBillRepository implements BillRepositoryInterface
{

    // public function create(array $data)
    // {
    //     return Contact::create($data);
    // }

    // public function update(string $contactId, array $data)
    // {
    //     $contact = $this->find($contactId);
    //     $contact->update($data);
    //     $contact->refresh();
    //     return $contact;
    // }

    public function find(string $billId)
    {
        return Bill::findOrFail($billId);
    }

    public function findAll()
    {
        return Bill::orderBy('created_at', 'desc')->get();
    }

    //     public function delete(string $contactId)
    //     {
    //         $contact = $this->find($contactId);
    //         $contact->delete();
    //     }
    // }
}
