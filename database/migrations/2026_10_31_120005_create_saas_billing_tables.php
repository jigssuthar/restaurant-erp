<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('billing_interval')->default('monthly');
            $table->string('razorpay_plan_id')->nullable()->unique();
            $table->unsignedInteger('max_branches')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_tables')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saas_plan_id')->nullable()->constrained('saas_plans')->nullOnDelete();
            $table->string('provider')->default('razorpay');
            $table->string('provider_subscription_id')->nullable()->unique();
            $table->string('provider_customer_id')->nullable();
            $table->string('status')->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_subscription_id')
                ->constrained('organization_subscriptions')
                ->cascadeOnDelete();
            $table->string('provider_payment_id')->nullable()->unique();
            $table->string('provider_invoice_id')->nullable()->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });

        DB::table('organizations')->orderBy('id')->chunkById(100, function ($organizations): void {
            foreach ($organizations as $organization) {
                DB::table('organization_subscriptions')->insert([
                    'organization_id' => $organization->id,
                    'provider' => 'razorpay',
                    'status' => 'trialing',
                    'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
        Schema::dropIfExists('organization_subscriptions');
        Schema::dropIfExists('saas_plans');
    }
};
