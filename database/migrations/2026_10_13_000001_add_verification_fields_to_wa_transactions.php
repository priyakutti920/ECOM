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
        if (Schema::hasTable('wa_transactions')) {
            Schema::table('wa_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('wa_transactions', 'verified_amount')) {
                    $table->decimal('verified_amount', 12, 2)->nullable()->after('amount');
                }
                if (!Schema::hasColumn('wa_transactions', 'gateway_status')) {
                    $table->string('gateway_status', 50)->nullable()->after('status');
                }
                if (!Schema::hasColumn('wa_transactions', 'raw_gateway_response')) {
                    $table->text('raw_gateway_response')->nullable()->after('gateway_status');
                }
            });

            // Index payment_reference if not already indexed
            try {
                Schema::table('wa_transactions', function (Blueprint $table) {
                    $table->index('payment_reference');
                });
            } catch (\Throwable $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wa_transactions')) {
            Schema::table('wa_transactions', function (Blueprint $table) {
                try {
                    $table->dropIndex(['payment_reference']);
                } catch (\Throwable $e) {}

                $columnsToDrop = [];
                if (Schema::hasColumn('wa_transactions', 'verified_amount')) {
                    $columnsToDrop[] = 'verified_amount';
                }
                if (Schema::hasColumn('wa_transactions', 'gateway_status')) {
                    $columnsToDrop[] = 'gateway_status';
                }
                if (Schema::hasColumn('wa_transactions', 'raw_gateway_response')) {
                    $columnsToDrop[] = 'raw_gateway_response';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
