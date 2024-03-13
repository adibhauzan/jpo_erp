<?php

namespace App\Repositories\Contact;

interface ContactRepositoryInterface
{
    public function create(array $data);

    public function update(string $contactId, array $data);

    public function find(string $contactId);

    public function findAll();

    public function delete(string $contactId);
}
