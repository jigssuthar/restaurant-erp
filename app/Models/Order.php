<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const NET_PAYMENT_SQL = "COALESCE(payment_totals.net_received, CASE WHEN orders.payment_status = 'paid' THEN orders.total ELSE 0 END)";

    public const TYPES = ['dine_in', 'takeaway', 'delivery'];

    public const STATUSES = ['pending', 'held', 'preparing', 'ready', 'completed', 'cancelled'];

    public const PAYMENT_METHODS = ['cash', 'card', 'upi', 'other'];

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
        'invoice_number',
        'discount_type',
        'discount_value',
        'discount_amount',
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
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
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

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class)->orderBy('recorded_at');
    }

    public function cashPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class)
            ->where('transaction_type', 'payment')
            ->where('payment_method', 'cash');
    }

    public function scopeJoinPaymentTotals(Builder $query): Builder
    {
        $paymentTotals = DB::table('order_payments')
            ->select('order_id')
            ->selectRaw("SUM(CASE WHEN transaction_type = 'payment' THEN amount WHEN transaction_type = 'refund' THEN -amount ELSE 0 END) as net_received")
            ->groupBy('order_id');

        return $query->leftJoinSub($paymentTotals, 'payment_totals', function (JoinClause $join) {
            $join->on('payment_totals.order_id', '=', 'orders.id');
        });
    }
}
