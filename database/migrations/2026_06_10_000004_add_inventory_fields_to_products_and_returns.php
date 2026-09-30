<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('qty');
            }
        });

        Schema::table('product_variations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variations', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('qty');
            }
        });

        Schema::table('order_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('order_returns', 'quantity')) {
                $table->integer('quantity')->default(1)->after('customer_id');
            }
            if (!Schema::hasColumn('order_returns', 'restocked_at')) {
                $table->timestamp('restocked_at')->nullable()->after('resolved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
        });

        Schema::table('product_variations', function (Blueprint $table) {
            if (Schema::hasColumn('product_variations', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
        });

        Schema::table('order_returns', function (Blueprint $table) {
            if (Schema::hasColumn('order_returns', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('order_returns', 'restocked_at')) {
                $table->dropColumn('restocked_at');
            }
        });
    }
};
