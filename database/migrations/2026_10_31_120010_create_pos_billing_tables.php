<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('opening_float', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('counted_cash', 12, 2)->nullable();
            $table->decimal('cash_difference', 12, 2)->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'opened_at', 'closed_at'], 'register_branch_session_idx');
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_register_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('transaction_type', 20);
            $table->string('payment_method', 20);
            $table->decimal('amount', 12, 2);
            $table->string('reference', 120)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['order_id', 'transaction_type', 'recorded_at'], 'order_payment_ledger_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_number', 40)->nullable()->unique();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'discount_type', 'discount_value', 'discount_amount']);
        });
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('cash_register_sessions');
    }
};
