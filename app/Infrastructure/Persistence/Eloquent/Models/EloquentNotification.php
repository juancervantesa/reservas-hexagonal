<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EloquentNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'sent',
        'sent_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array',
        'sent' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }

    public function scopePending($query)
    {
        return $query->where('sent', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
