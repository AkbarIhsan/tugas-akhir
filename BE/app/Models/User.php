<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'id_role',
        'id_branch',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role() {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function branch() {
        return $this->belongsTo(Branch::class, 'id_branch');
    }

    public function transferStocksSent(){
        return $this->hasMany(TransferStock::class, 'id_user');
    }

    // Transfer stok yang diterima oleh user ini
    public function transferStocksReceived(){
        return $this->hasMany(TransferStock::class, 'id_user_2');
    }

    public function moneyFlow(){
        return $this->hasMany(MoneyFlow::class, 'id_user');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
