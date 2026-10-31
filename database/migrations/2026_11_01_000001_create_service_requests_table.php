<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dining_table_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('request_type', 30);
            $table->string('status', 20)->default('pending');
            $table->string('note', 500)->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status', 'created_at'], 'table_service_request_queue_idx');
            $table->index(['dining_table_id', 'request_type', 'status'], 'table_service_request_dedupe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_service_requests');
    }
};
