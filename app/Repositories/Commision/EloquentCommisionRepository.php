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
        $commissions = DB::table('commisions')
            ->select(
                'commisions.id',
                'commisions.nama_bank',
                'commisions.nama_rekening',
                'commisions.no_rekening',
                'commisions.no_commision',
                'commisions.ref_dokumen_id',
                'commisions.broker',
                'commisions.broker_fee',
                'commisions.payment',
                'commisions.paid_status',
                'contacts.name as broker_name',
                'commisions.created_at',
                'commisions.updated_at',
            )
            ->join('contacts', 'commisions.broker', '=', 'contacts.id')
            ->orderBy('commisions.created_at', 'desc')
            ->get();

        return $commissions;
    }

    //     public function delete(string $contactId)
    //     {
    //         $contact = $this->find($contactId);
    //         $contact->delete();
    //     }
    // }
    //

    public function pay(string $commisionId, $paid_price, $nama_bank, $nama_rekening, $no_rekening)
    {
        $commision = null;

        DB::transaction(function () use ($commisionId, $paid_price, $nama_bank, $nama_rekening, $no_rekening) {
            $commision = Commision::findOrFail($commisionId);

            if ($paid_price > $commision->broker_fee) {
                throw new \Exception('Uang yang dibayar melebihi broker_fee');
            }

            $commision->payment += $paid_price;
            $commision->nama_bank = $nama_bank;
            $commision->nama_rekening = $nama_rekening;
            $commision->no_rekening = $no_rekening;
            if ($commision->payment > $commision->broker_fee) {
                throw new \Exception('Uang yang dibayar melebihi broker_fee');
            }

            if ($commision->broker_fee == $commision->payment) {
                $commision->paid_status = 'paid';
            } else {
                $commision->paid_status = 'partialy_paid';
            }




            $commision->save();
        });

        return $commision;
    }
}
