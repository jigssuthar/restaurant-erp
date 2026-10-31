<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('dining_table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 40);
            $table->string('order_type', 30);
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('pending');
            $table->string('payment_method', 30)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('currency_code', 3)->default('INR');
            $table->string('tax_label', 60)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'order_number']);
            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
