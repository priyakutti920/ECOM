<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();

            // 'flat' = fixed amount off (e.g. ₹50 off), 'percent' = % off the order
            $table->enum('type', ['flat', 'percent'])->default('flat');

            $table->decimal('value', 10, 2)->default(0);          // discount value
            $table->decimal('min_amount', 10, 2)->default(100);    // min cart total to redeem (₹100+)
            $table->decimal('max_discount', 10, 2)->nullable();    // optional cap (for % type)

            // Ownership / source
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('bonus_id')->nullable()->constrained('bonuses')->nullOnDelete();

            // Lifecycle — coupons are issued but never expire (per requirement)
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();          // NULL = never expires
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_in_order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('label', 200)->nullable();              // e.g. "Bonus coupon — 10% off"
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
            $table->index(['is_active', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
