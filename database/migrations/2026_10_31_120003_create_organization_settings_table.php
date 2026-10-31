<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3)->default('INR');
            $table->string('tax_label', 60)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->string('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
