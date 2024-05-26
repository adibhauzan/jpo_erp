<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
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
    protected $casts = [
        'stock_roll' => 'float',
        'stock_kg' => 'float',
        'stock_rib' => 'float',
    ];

    protected $fillable = [
        'id',
        'warehouse_id',
        'sku',
        'po_id',
        'stock_roll',
        'stock_kg',
        'stock_rib',
        'date_received',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
