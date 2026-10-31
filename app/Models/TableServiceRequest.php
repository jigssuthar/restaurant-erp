<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableServiceRequest extends Model
{
    public const TYPES = ['call_waiter', 'request_bill', 'request_water', 'other'];

    public const STATUSES = ['pending', 'acknowledged', 'completed'];

    protected $fillable = [
        'branch_id',
        'dining_table_id',
        'acknowledged_by',
        'completed_by',
        'request_type',
        'status',
        'note',
        'acknowledged_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
