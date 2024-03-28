<?php

namespace App\Repositories\Bill;

use App\Models\Bank;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;

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
    //

    public function pay(string $billId, $paid_price, $bank_id)
    {
        $bill = null;

        DB::transaction(function () use ($billId, $paid_price, $bank_id) {
            $bill = Bill::findOrFail($billId);
            $bank = Bank::findOrFail($bank_id);

            if ($paid_price > $bill->bill_price) {
                throw new \Exception('Uang yang dibayar melebihi bill price');
            }

            $bill->payment += $paid_price;

            if($bill->payment > $bill->bill_price){
                throw new \Exception('Uang yang dibayar melebihi bill price');
            }

            if ($bill->bill_price == $bill->payment) {
                $bill->paid_status = 'paid';
            } else {
                $bill->paid_status = 'partialy_paid';
            }

            $bill->bank_id = $bank->id;



            $bill->save();

        });

        return $bill;

    }
}