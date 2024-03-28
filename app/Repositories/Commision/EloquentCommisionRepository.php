<?php

namespace App\Repositories\Commision;

use App\Models\Bank;
use App\Models\Commision;
use Illuminate\Support\Facades\DB;

class EloquentCommisionRepository implements CommisionRepositoryInterface
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

    public function find(string $commisionId)
    {
        return Commision::findOrFail($commisionId);
    }

    public function findAll()
    {
        return Commision::orderBy('created_at', 'desc')->get();
    }

    //     public function delete(string $contactId)
    //     {
    //         $contact = $this->find($contactId);
    //         $contact->delete();
    //     }
    // }
    //

    public function pay(string $commisionId, $paid_price, $bank_id)
    {
        $commision = null;

        DB::transaction(function () use ($commisionId, $paid_price, $bank_id) {
            $commision = Commision::findOrFail($commisionId);
            $bank = Bank::findOrFail($bank_id);

            if ($paid_price > $commision->broker_fee) {
                throw new \Exception('Uang yang dibayar melebihi broker_fee');
            }

            $commision->payment += $paid_price;

            if ($commision->payment > $commision->broker_fee) {
                throw new \Exception('Uang yang dibayar melebihi broker_fee');
            }

            if ($commision->broker_fee == $commision->payment) {
                $commision->paid_status = 'paid';
            } else {
                $commision->paid_status = 'partialy_paid';
            }

            $commision->bank_id = $bank->id;



            $commision->save();
        });

        return $commision;
    }
}
