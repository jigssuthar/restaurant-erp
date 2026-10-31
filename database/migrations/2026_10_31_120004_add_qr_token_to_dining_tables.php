<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique();
        });

        DB::table('dining_tables')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->chunkById(100, function ($tables) {
                foreach ($tables as $table) {
                    DB::table('dining_tables')
                        ->where('id', $table->id)
                        ->update(['qr_token' => Str::random(64)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
