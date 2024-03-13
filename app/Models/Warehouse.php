<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mchev\Banhammer\Traits\Bannable;


class Warehouse extends Model
{
    use HasFactory, Bannable;

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
        'name',
        'address',
        'phone_number',
        'store_id',
        'convection_id',
        'status',
    ];


    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'warehouse_id');
    }

  
}