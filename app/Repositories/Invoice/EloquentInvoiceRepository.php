<?php

namespace App\Repositories\Invoice;

use App\Models\Invoice;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
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

    public function find(string $invId)
    {
        return Invoice::findOrFail($invId);
    }

    public function findAll()
    {
        return Invoice::orderBy('created_at', 'desc')->get();
    }

    //     public function delete(string $contactId)
    //     {
    //         $contact = $this->find($contactId);
    //         $contact->delete();
    //     }
    // }
}
