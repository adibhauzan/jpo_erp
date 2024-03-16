<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends Model
{
    use HasFactory;

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
        'id',
        'contact_id',
        'warehouse_id',
        'no_so',
        'no_do',
        'date',
        'nama_barang',
        'grade',
        'sku',
        'description',
        'ketebalan',
        'setting',
        'gramasi',
        'stock_roll',
        'stock_kg',
        'stock_rib',
        'attachment_image',
        'price',
        'broker_fee',
        'broker',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
