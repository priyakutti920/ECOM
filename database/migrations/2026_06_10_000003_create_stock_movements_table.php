<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
                $table->string('type', 30)->comment('PURCHASE, RESTOCK, RETURN, ADJUSTMENT, CANCELLED_ORDER');
                $table->integer('quantity')->comment('Positive for additions, negative for deductions');
                $table->integer('previous_qty')->default(0);
                $table->integer('new_qty')->default(0);
                $table->string('reference_type', 50)->nullable()->comment('order, return, manual, etc.');
                $table->string('reference_id', 100)->nullable();
                $table->string('reason', 255)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('idempotency_key', 100)->nullable()->unique();
                $table->timestamps();

                $table->index(['product_id', 'created_at']);
                $table->index(['variation_id', 'created_at']);
                $table->index('reference_id');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
