<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('organization_subscriptions', 'last_webhook_at')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->timestamp('last_webhook_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('organization_subscriptions', 'last_webhook_at')) {
            Schema::table('organization_subscriptions', function (Blueprint $table) {
                $table->dropColumn('last_webhook_at');
            });
        }
    }
};
