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
    Schema::create('products', function (Blueprint $table) {
        $table->id();

        $table->foreignId('branch_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('category_id')
            ->constrained()
            ->restrictOnDelete();

        $table->string('name');
        $table->string('sku')->nullable();
        $table->text('description')->nullable();

        $table->decimal('price', 12, 2)->default(0);
        $table->decimal('cost_price', 12, 2)->default(0);

        $table->string('type')->default('food');
        $table->string('image')->nullable();

        $table->boolean('is_available')->default(true);

        $table->timestamps();

        $table->unique(['branch_id', 'sku']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
