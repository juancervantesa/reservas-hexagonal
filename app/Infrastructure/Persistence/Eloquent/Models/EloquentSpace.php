<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EloquentSpace extends Model
{
    use HasFactory;

    protected $table = 'spaces';

    protected $fillable = [
        'name',
        'type',
        'capacity',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function reservations()
    {
        return $this->hasMany(EloquentReservation::class, 'space_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCapacity($query, $minCapacity, $maxCapacity = null)
    {
        $query->where('capacity', '>=', $minCapacity);

        if ($maxCapacity) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query;
    }
}
