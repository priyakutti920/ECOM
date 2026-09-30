<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add status and payment_status indexes if not already present
            try {
                $table->index(['status', 'created_at'], 'orders_status_created_at_idx');
            } catch (\Throwable $e) {}

            try {
                $table->index(['payment_status', 'created_at'], 'orders_payment_status_created_at_idx');
            } catch (\Throwable $e) {}
        });

        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->index(['product_id', 'quantity'], 'order_items_product_id_qty_idx');
            } catch (\Throwable $e) {}
        });

        Schema::table('products', function (Blueprint $table) {
            try {
                $table->index(['status', 'is_active', 'is_featured'], 'products_active_featured_idx');
            } catch (\Throwable $e) {}

            try {
                $table->index(['status', 'is_active', 'sort_order'], 'products_active_sort_idx');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropIndex('orders_status_created_at_idx');
                $table->dropIndex('orders_payment_status_created_at_idx');
            } catch (\Throwable $e) {}
        });

        Schema::table('order_items', function (Blueprint $table) {
            try {
                $table->dropIndex('order_items_product_id_qty_idx');
            } catch (\Throwable $e) {}
        });

        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropIndex('products_active_featured_idx');
                $table->dropIndex('products_active_sort_idx');
            } catch (\Throwable $e) {}
        });
    }
};
