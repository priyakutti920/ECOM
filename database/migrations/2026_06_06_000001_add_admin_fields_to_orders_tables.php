<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Order-level admin fields
        Schema::table('orders', function (Blueprint $t) {
            // Free-form, admin-entered status messages (e.g. "Out for delivery — 3:40 PM")
            $t->json('custom_statuses')->nullable()->after('status');
            // Audit trail of every status change with timestamp + admin user
            $t->json('status_history')->nullable()->after('custom_statuses');
            // Soft-cancel reason + when
            $t->string('cancelled_reason', 500)->nullable()->after('status_history');
            $t->timestamp('cancelled_at')->nullable()->after('cancelled_reason');
            // Refund bookkeeping
            $t->decimal('refunded_amount', 10, 2)->nullable()->after('cancelled_at');
            $t->timestamp('refunded_at')->nullable()->after('refunded_amount');
            $t->string('refund_reference', 100)->nullable()->after('refunded_at');
            // Dispatch metadata (when the order leaves the warehouse)
            $t->string('dispatched_via', 100)->nullable()->after('refund_reference');
            $t->string('tracking_number', 200)->nullable()->after('dispatched_via');
            $t->timestamp('dispatched_at')->nullable()->after('tracking_number');
            // Cash-on-delivery mark-paid (override the gateway)
            $t->timestamp('marked_paid_at')->nullable()->after('dispatched_at');
            $t->unsignedBigInteger('marked_paid_by')->nullable()->after('marked_paid_at');
        });

        // Item-level provider snapshot so the order view is stable even if the
        // product_provider pivot later changes.
        Schema::table('order_items', function (Blueprint $t) {
            $t->json('provider_names')->nullable()->after('product_image');
            $t->json('provider_ids')->nullable()->after('provider_names');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropColumn([
                'custom_statuses', 'status_history',
                'cancelled_reason', 'cancelled_at',
                'refunded_amount', 'refunded_at', 'refund_reference',
                'dispatched_via', 'tracking_number', 'dispatched_at',
                'marked_paid_at', 'marked_paid_by',
            ]);
        });
        Schema::table('order_items', function (Blueprint $t) {
            $t->dropColumn(['provider_names', 'provider_ids']);
        });
    }
};
