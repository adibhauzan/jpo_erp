<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
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
        'status',
        'contact_id',
        'warehouse_id',
        'no_po',
        'no_do',
        'date',
        'nama_barang',
        'grade',
        'sku',
        'description',
        'attachment_image',
        'ketebalan',
        'setting',
        'gramasi',
        'price',
        'stock_roll',
        'stock_kg',
        'stock_rib',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
