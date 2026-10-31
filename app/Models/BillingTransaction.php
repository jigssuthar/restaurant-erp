<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    protected $fillable = [
        'organization_subscription_id',
        'provider_payment_id',
        'provider_invoice_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'details' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(OrganizationSubscription::class, 'organization_subscription_id');
    }
}
