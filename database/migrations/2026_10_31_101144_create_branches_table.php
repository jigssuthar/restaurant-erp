<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('branches', function (Blueprint $table) {
        $table->id();

        $table->foreignId('organization_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('name');
        $table->string('branch_code', 30);
        $table->string('email')->nullable();
        $table->string('phone', 20)->nullable();
        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();
        $table->string('pincode', 10)->nullable();

        $table->string('status')->default('active');

        $table->timestamps();

        $table->unique(['organization_id', 'branch_code']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
