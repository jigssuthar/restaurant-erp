<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantEvent extends Model
{
    public const TYPES = ['general', 'private_event', 'maintenance', 'holiday', 'promotion'];

    protected $fillable = [
        'branch_id',
        'created_by',
        'title',
        'event_type',
        'description',
        'starts_at',
        'ends_at',
        'all_day',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
