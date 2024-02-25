<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Ramsey\Uuid\Uuid;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mchev\Banhammer\Traits\Bannable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, Bannable;

    public $incrementing = false; // Non-incrementing primary key
    protected $keyType = 'string'; // Primary key type

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'convection_id',
        'name',
        'roles',
        'phone_number',
        'username',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    // public function isActive($status)
    // {
    //     return $this->status === $status;
    // }

    // public function setStatusAttribute($value)
    // {
    //     if ($value === 'active') {
    //         $this->attributes['roles'] = $value;
    //     }
    // }

    public function hasRole($role)
    {
        return $this->roles === $role;
    }

    public function setRolesAttribute($value)
    {
        if ($value === 'superadmin' || $value === 'store' || $value === 'convection') {
            $this->attributes['roles'] = $value;
        }
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
    public function convection()
    {
        return $this->belongsTo(Convection::class, 'convection_id', 'id');
    }
}