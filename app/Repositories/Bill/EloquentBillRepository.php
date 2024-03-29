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
        return Bill::select(
            'id',
            'no_bill',
            'nama_barang',
            'purchase_id',
            'nama_bank',
            'nama_rekening',
            'no_rekening',
            'contact_id',
            'warehouse_id',
            'sku',
            'ketebalan',
            'setting',
            'gramasi',
            'payment',
            'bill_price',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'paid_status',
            'created_at',
            'updated_at'
        )->with(['contact:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    //     public function delete(string $contactId)
    //     {
    //         $contact = $this->find($contactId);
    //         $contact->delete();
    //     }
    // }
    //

    public function pay(string $billId, $paid_price, $nama_bank, $nama_rekening, $no_rekening)

    {
        $bill = null;

        DB::transaction(function () use ($billId, $paid_price, $nama_bank, $nama_rekening, $no_rekening) {
            $bill = Bill::findOrFail($billId);

            if ($paid_price > $bill->bill_price) {
                throw new \Exception('Uang yang dibayar melebihi bill price');
            }

            $bill->payment += $paid_price;
            $bill->nama_bank = $nama_bank;
            $bill->nama_rekening = $nama_rekening;
            $bill->no_rekening = $no_rekening;

            if ($bill->payment > $bill->bill_price) {
                throw new \Exception('Uang yang dibayar melebihi bill price');
            }

            if ($bill->bill_price == $bill->payment) {
                $bill->paid_status = 'paid';
            } else {
                $bill->paid_status = 'partialy_paid';
            }

            $bill->save();
        });

        return $bill;
    }
}
