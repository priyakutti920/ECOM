<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'gateway_payment_id')) {
                $table->string('gateway_payment_id', 100)->nullable()->after('payment_order_id')->index();
            }
            if (!Schema::hasColumn('orders', 'gateway_signature')) {
                $table->string('gateway_signature', 255)->nullable()->after('gateway_payment_id');
            }
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('orders', 'shipment_id')) {
                $table->string('shipment_id', 100)->nullable()->after('dispatched_via');
            }
            if (!Schema::hasColumn('orders', 'awb_code')) {
                $table->string('awb_code', 100)->nullable()->after('shipment_id')->index();
            }
            if (!Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name', 100)->nullable()->after('awb_code');
            }
            if (!Schema::hasColumn('orders', 'courier_company_id')) {
                $table->integer('courier_company_id')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('orders', 'shipping_label_url')) {
                $table->string('shipping_label_url', 500)->nullable()->after('courier_company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_payment_id',
                'gateway_signature',
                'tax_amount',
                'shipment_id',
                'awb_code',
                'courier_name',
                'courier_company_id',
                'shipping_label_url',
            ]);
        });
    }
};
