<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DiningTable extends Model
{
    protected $fillable = [
        'branch_id',
        'table_number',
        'capacity',
        'status',
        'qr_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (DiningTable $table) {
            $table->qr_token ??= Str::random(64);
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(TableServiceRequest::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function rotateQrToken(): void
    {
        $this->forceFill(['qr_token' => Str::random(64)])->save();
    }

    public function ensureQrToken(): void
    {
        if (! $this->qr_token) {
            $this->rotateQrToken();
        }
    }
}