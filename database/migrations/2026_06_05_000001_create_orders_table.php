<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Friendly human-facing code: NS0001, NS0002 ... NS99999 ... NS999999 ...
            $table->string('order_code', 20)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();

            // Contact snapshot (from the buy-now contact section)
            $table->string('contact_name', 120);
            $table->string('contact_mobile', 10);
            $table->string('contact_email', 120)->nullable();

            // Address snapshot — kept on the row so future address edits/deletes don't change history
            $table->string('addr_full_name', 120);
            $table->string('addr_line_1', 255);
            $table->string('addr_line_2', 255)->nullable();
            $table->string('addr_city', 120);
            $table->string('addr_state', 120);
            $table->string('addr_pincode', 10);
            $table->string('addr_mobile_primary', 10);
            $table->string('addr_mobile_alternate', 10)->nullable();
            $table->string('addr_type', 10)->default('home');

            // Money
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // Payment
            $table->string('payment_method', 30)->default('upi');
            $table->string('payment_status', 20)->default('paid')->comment('paid | failed | refunded');
            $table->string('payment_gateway', 30)->nullable()->comment('upicheckout slug');
            $table->string('payment_order_id', 100)->nullable()->comment('temp id sent to gateway');
            $table->string('payment_utr', 50)->nullable();
            $table->timestamp('paid_at')->nullable();

            // Fulfilment
            $table->string('status', 20)->default('placed')->comment('placed | packed | shipped | delivered | cancelled');

            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index('payment_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
