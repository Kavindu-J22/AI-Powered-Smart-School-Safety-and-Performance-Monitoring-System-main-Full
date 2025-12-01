<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'entity_type',
        'entity_id',
        'user_id',
        'user_name',
        'is_read',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute()
    {
        return match ($this->type) {
            'created' => 'add_circle',
            'updated' => 'edit',
            'deleted' => 'delete',
            default => 'notifications'
        };
    }

    public function getColorAttribute()
    {
        return match ($this->type) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            default => 'info'
        };
    }
}
