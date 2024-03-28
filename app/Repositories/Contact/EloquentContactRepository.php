<?php

namespace App\Repositories\Contact;

use App\Models\Contact;

class EloquentContactRepository implements ContactRepositoryInterface
{

    public function create(array $data)
    {
        return Contact::create($data);
    }

    public function update(string $contactId, array $data)
    {
        $contact = $this->find($contactId);
        $contact->update($data);
        $contact->refresh();
        return $contact;
    }

    public function find(string $contactId)
    {
        return Contact::findOrFail($contactId);
    }

    public function findAll()
    {
        return Contact::orderBy('created_at', 'desc')->get();
    }

    public function delete(string $contactId)
    {
        $contact = $this->find($contactId);
        $contact->delete();
    }
}
