<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove foreign key on wa_transactions if it exists
        if (Schema::hasTable('wa_transactions')) {
            Schema::table('wa_transactions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['wa_api_settings_id']);
                } catch (\Throwable $e) {}
                try {
                    $table->dropColumn('wa_api_settings_id');
                } catch (\Throwable $e) {}
            });
        }

        // 2. Drop wa_api_settings table completely
        Schema::dropIfExists('wa_api_settings');

        // 3. Remove WhatsApp API credential keys from store_settings table if present
        try {
            DB::table('store_settings')->whereIn('key', [
                'wa_fast2sms_auth_key',
                'wa_phone_number_id',
                'wa_sender_number',
                'wa_admin_phone',
                'wa_notif_global_status',
                'wa_notif_customer_notify',
                'wa_engine_disabled',
                'wa_engine_enabled',
            ])->delete();
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
