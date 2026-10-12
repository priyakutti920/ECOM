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
        if (!Schema::hasTable('wa_api_settings')) {
            Schema::create('wa_api_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('global_status')->default(false);
                $table->string('admin_phone', 25)->nullable();
                $table->boolean('customer_notify_enabled')->default(false);
                $table->string('auth_key', 255)->nullable();
                $table->string('phone_number_id', 100)->nullable();
                $table->string('sender_number', 25)->nullable();
                $table->string('message_id', 50)->default('24059');
                $table->string('template_name', 100)->default('order_notify');
                $table->decimal('wallet_balance', 12, 2)->default(0.00);
                $table->dateTime('last_transaction_at')->nullable();
                $table->timestamps();
            });

            // Insert default row
            DB::table('wa_api_settings')->insert([
                'id' => 1,
                'global_status' => 1,
                'admin_phone' => '',
                'customer_notify_enabled' => 0,
                'auth_key' => 'ikbpoF8Ds9z8us7f6FTsMysRZ5lSynykZT5PRSw3wykzdACmRlbveh6qmgHj',
                'phone_number_id' => '767998026401419',
                'sender_number' => '6374731493',
                'message_id' => '24059',
                'template_name' => 'order_notify',
                'wallet_balance' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('wa_transactions')) {
            Schema::create('wa_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wa_api_settings_id')->default(1)->constrained('wa_api_settings')->cascadeOnDelete();
                $table->string('type', 20)->comment('credit | debit');
                $table->decimal('amount', 12, 2);
                $table->text('description')->nullable();
                $table->string('payment_gateway', 50)->nullable()->comment('upi | manual | system');
                $table->string('payment_reference', 255)->nullable();
                $table->string('status', 20)->default('completed')->comment('pending | completed | failed');
                $table->string('related_type', 50)->nullable()->comment('topup | order | manual');
                $table->string('related_invoice', 100)->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('related_invoice');
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('wa_message_logs')) {
            Schema::create('wa_message_logs', function (Blueprint $table) {
                $table->id();
                $table->string('telephone', 25);
                $table->string('template_name', 100)->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->string('status', 20)->default('failed');
                $table->string('source', 50)->nullable();
                $table->text('response_body')->nullable();
                $table->timestamps();

                $table->index('telephone');
                $table->index('status');
                $table->index('order_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_message_logs');
        Schema::dropIfExists('wa_transactions');
        Schema::dropIfExists('wa_api_settings');
    }
};
