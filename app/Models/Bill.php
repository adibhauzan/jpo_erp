<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Bill extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'no_bill',
        'nama_barang',
        'purchase_id',
        'id',
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
    ];


    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
