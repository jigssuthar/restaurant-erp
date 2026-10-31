<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'gst_number',
        'logo',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (Organization $organization): void {
            $organization->subscriptions()->create([
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 30)),
            ]);
        });
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizationSetting::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(OrganizationSubscription::class)->latestOfMany();
    }
}