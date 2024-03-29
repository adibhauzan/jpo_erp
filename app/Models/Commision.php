<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commision extends Model
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
        'nama_bank',
        'nama_rekening',
        'no_rekening',
        'no_commision',
        'ref_dokumen_id',
        'broker',
        'broker_fee',
        'payment',
        'paid_status'
    ];

    public function broker()
    {
        return $this->belongsTo(Contact::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}