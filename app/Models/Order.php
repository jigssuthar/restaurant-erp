<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const TYPES = ['dine_in', 'takeaway', 'delivery'];

    public const STATUSES = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

    public const PAYMENT_METHODS = ['cash', 'card', 'upi'];

    protected $fillable = [
        'branch_id',
        'dining_table_id',
        'user_id',
        'order_number',
        'order_type',
        'status',
        'payment_status',
        'payment_method',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'subtotal',
        'currency_code',
        'tax_label',
        'tax_rate',
        'tax_amount',
        'service_charge_rate',
        'service_charge',
        'delivery_fee',
        'total',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
