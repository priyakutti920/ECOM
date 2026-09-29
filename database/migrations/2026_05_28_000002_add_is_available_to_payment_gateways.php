<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!\Schema::hasColumn('payment_gateways', 'is_available')) {
                $table->tinyInteger('is_available')->default(0)->after('icon');
            }
            if (!\Schema::hasColumn('payment_gateways', 'method_name')) {
                $table->string('method_name', 100)->nullable()->after('is_available');
            }
            if (!\Schema::hasColumn('payment_gateways', 'description')) {
                $table->text('description')->nullable()->after('method_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (\Schema::hasColumn('payment_gateways', 'is_available')) {
                $table->dropColumn('is_available');
            }
            if (\Schema::hasColumn('payment_gateways', 'method_name')) {
                $table->dropColumn('method_name');
            }
            if (\Schema::hasColumn('payment_gateways', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};