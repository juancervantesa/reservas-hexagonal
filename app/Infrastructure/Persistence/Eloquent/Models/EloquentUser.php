<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EloquentUser extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function reservations()
    {
        return $this->hasMany(EloquentReservation::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(EloquentNotification::class, 'user_id');
    }
}
