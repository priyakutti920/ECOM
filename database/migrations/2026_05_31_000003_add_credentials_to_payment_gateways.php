<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_gateways', 'credentials')) {
                $table->json('credentials')->nullable()->after('description');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_gateways', 'is_available')) {
                $table->tinyInteger('is_available')->default(0)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('payment_gateways', 'credentials')) {
                $table->dropColumn('credentials');
            }
        });
    }
};