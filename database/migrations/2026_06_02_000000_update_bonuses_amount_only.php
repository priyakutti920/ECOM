<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bonuses', function (Blueprint $table) {
            // Drop old qty-based columns if they exist
            if (Schema::hasColumn('bonuses', 'bonus_type')) {
                $table->dropColumn(['bonus_type', 'bonus_value', 'apply_on', 'min_order_amount']);
            }
            // Ensure percent and min_amount columns exist
            if (!Schema::hasColumn('bonuses', 'bonus_percent')) {
                $table->decimal('bonus_percent', 5, 2)->default(0)->comment('percentage off on entire amount');
            }
            if (!Schema::hasColumn('bonuses', 'min_amount')) {
                $table->decimal('min_amount', 10, 2)->default(0)->comment('minimum purchase to qualify');
            }
        });
    }

    public function down(): void
    {
        // No rollback needed — this is a simplification
    }
};