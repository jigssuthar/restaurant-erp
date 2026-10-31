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
    Schema::create('dining_tables', function (Blueprint $table) {
        $table->id();

        $table->foreignId('branch_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('table_number');
        $table->unsignedInteger('capacity')->default(4);

        $table->string('status')->default('available');

        $table->timestamps();

        $table->unique(['branch_id', 'table_number']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
