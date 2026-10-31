<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSetting extends Model
{
    protected $fillable = [
        'organization_id',
        'currency_code',
        'tax_label',
        'tax_rate',
        'service_charge_rate',
        'delivery_fee',
        'receipt_header',
        'receipt_footer',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
