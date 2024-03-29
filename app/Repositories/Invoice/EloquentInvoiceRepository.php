<?php

namespace App\Repositories\Invoice;

use App\Models\Bank;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

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
        return Invoice::select(
            'id',
            'no_invoice',
            'sales_order_id',
            'warehouse_id',
            'contact_id',
            'bank_id',
            'sku',
            'nama_barang',
            'sell_price',
            'ketebalan',
            'setting',
            'gramasi',
            'stock_roll',
            'stock_kg',
            'stock_rib',
            'sell_price',
            'payment',
            'is_broker',
            'broker',
            'broker_fee',
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
    //

    public function pay(string $invoiceId, $paid_price, $bank_id)
    {
        $invoice = null;

        DB::transaction(function () use ($invoiceId, $paid_price, $bank_id) {
            $invoice = Invoice::findOrFail($invoiceId);
            $bank = Bank::findOrFail($bank_id);

            if ($paid_price > $invoice->sell_price) {
                throw new \Exception('Uang yang dibayar melebihi sell price');
            }

            $invoice->payment += $paid_price;

            if ($bank->payment > $invoice->sell_price) {
                throw new \Exception('Uang yang dibayar melebihi sell price');
            }

            if ($invoice->sell_price == $invoice->payment) {
                $invoice->paid_status = 'paid';
            } else {
                $invoice->paid_status = 'partialy_paid';
            }

            $invoice->bank_id = $bank->id;



            $invoice->save();
        });

        return $invoice;
    }
}
