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
        'contact_id',
        'warehouse_id',
        'no_po',
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
        'stock_roll_rev',
        'stock_kg_rev',
        'stock_rib_rev',
        'attachment_image',
        'price',
        'date_received',
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
}