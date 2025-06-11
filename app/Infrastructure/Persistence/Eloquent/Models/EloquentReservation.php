<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EloquentReservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'space_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'purpose'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'space_id' => 'integer',
        'reservation_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }

    public function space()
    {
        return $this->belongsTo(EloquentSpace::class, 'space_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    public function scopeForSpace($query, $spaceId)
    {
        return $query->where('space_id', $spaceId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
