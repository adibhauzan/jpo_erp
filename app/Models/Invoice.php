<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Invoice extends Model
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
        'no_invoice',
        'sales_order_id',
        'warehouse_id',
        'contact_id',
        'bank_id',
        'sku',
        'sell_price',
        'ketebalan',
        'setting',
        'gramasi',
        'stock_roll',
        'stock_kg',
        'stock_rib',
        'bill_price',
        'payment',
        'is_broker',
        'broker',
        'broker_fee',
        'paid_status'
    ];

    protected $casts = [
        'is_broker' => 'boolean', // Mengkonversi is_broker ke tipe data boolean
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

    public function sales_order()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function commisions()
    {
        return $this->hasMany(Commision::class);
    }
}