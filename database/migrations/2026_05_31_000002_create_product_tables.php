<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('special_price', 10, 2)->nullable();
            $table->date('special_price_start')->nullable();
            $table->date('special_price_end')->nullable();
            $table->boolean('manage_inventory')->default(false);
            $table->integer('qty')->default(0);
            $table->string('stock_status')->default('in_stock');
            $table->timestamps();
        });

        Schema::create('product_variation_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('related_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_id')->constrained('products')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add missing columns to existing products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'code')) {
                $table->string('code', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'url')) {
                $table->string('url', 255)->nullable()->after('seo_url');
            }
            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug', 255)->nullable()->unique()->after('url');
            }
            if (!Schema::hasColumn('products', 'manage_inventory')) {
                $table->boolean('manage_inventory')->default(false)->after('slug');
            }
            if (!Schema::hasColumn('products', 'qty')) {
                $table->integer('qty')->default(0)->after('manage_inventory');
            }
            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('qty');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_products');
        Schema::dropIfExists('product_variation_images');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_images');
    }
};