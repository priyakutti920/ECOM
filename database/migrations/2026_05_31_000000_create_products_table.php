<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('sku')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->decimal('special_price', 10, 2)->nullable();
                $table->dateTime('special_price_start')->nullable();
                $table->dateTime('special_price_end')->nullable();
                $table->string('image')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->tinyInteger('status')->default(1);
                $table->longText('description')->nullable();
                $table->string('video_url')->nullable();
                $table->string('seo_url')->nullable();
                $table->string('url')->nullable();
                $table->string('slug')->nullable()->unique();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('manage_inventory')->default(false);
                $table->string('stock_status')->default('in_stock');
                $table->integer('qty')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('related_products')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
