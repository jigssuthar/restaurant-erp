<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationSubscription extends Model
{
    public const STATUSES = ['trialing', 'active', 'past_due', 'cancelled', 'expired'];

    protected $fillable = [
        'organization_id',
        'saas_plan_id',
        'provider',
        'provider_subscription_id',
        'provider_customer_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'ends_at',
        'last_webhook_at',
        'cancel_at_period_end',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'ends_at' => 'datetime',
            'last_webhook_at' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class);
    }

    public function grantsAccess(): bool
    {
        if ($this->status === 'trialing') {
            return $this->trial_ends_at?->isFuture() ?? false;
        }

        if ($this->status === 'active') {
            return ! $this->current_period_end || $this->current_period_end->isFuture();
        }

        if ($this->status === 'cancelled') {
            return $this->ends_at?->isFuture() ?? false;
        }

        return false;
    }
}
