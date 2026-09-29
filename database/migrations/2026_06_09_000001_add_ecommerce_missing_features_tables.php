<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Reviews table
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_name', 120);
                $table->string('customer_email', 150)->nullable();
                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('title', 200)->nullable();
                $table->text('comment');
                $table->boolean('is_approved')->default(false);
                $table->boolean('is_verified_purchase')->default(false);
                $table->timestamps();

                $table->index(['product_id', 'is_approved']);
                $table->index(['customer_id', 'product_id']);
            });
        }

        // 2. Product Options table
        if (!Schema::hasTable('product_options')) {
            Schema::create('product_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('type', 30)->default('checkbox'); // checkbox, select, text
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'sort_order']);
            });
        }

        // 3. Product Colors table
        if (!Schema::hasTable('product_colors')) {
            Schema::create('product_colors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('color_code', 30)->default('#000000');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'sort_order']);
            });
        }

        // 4. Wishlists table
        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['customer_id', 'product_id']);
            });
        }

        // 5. Update categories table (SEO + Active status)
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            if (!Schema::hasColumn('categories', 'meta_title')) {
                $table->string('meta_title', 255)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('categories', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('categories', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            }
        });

        // 6. Update products table (Open Graph and extra SEO)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('products', 'og_title')) {
                $table->string('og_title', 255)->nullable()->after('meta_keywords');
            }
            if (!Schema::hasColumn('products', 'og_description')) {
                $table->text('og_description')->nullable()->after('og_title');
            }
            if (!Schema::hasColumn('products', 'og_image')) {
                $table->string('og_image', 255)->nullable()->after('og_description');
            }
        });

        // 7. Update banners table (Flash sale + date scheduling)
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('banners', 'end_date')) {
                $table->dateTime('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('banners', 'is_flash_sale')) {
                $table->boolean('is_flash_sale')->default(false)->after('end_date');
            }
        });

        // 8. Update order_items table (Variations, colors, options)
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'variation_id')) {
                $table->foreignId('variation_id')->nullable()->after('product_id')->constrained('product_variations')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'variation_name')) {
                $table->string('variation_name', 150)->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('order_items', 'color')) {
                $table->string('color', 80)->nullable()->after('variation_name');
            }
            if (!Schema::hasColumn('order_items', 'options')) {
                $table->json('options')->nullable()->after('color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variation_id')) {
                $table->dropForeign(['variation_id']);
                $table->dropColumn(['variation_id', 'variation_name', 'color', 'options']);
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'is_flash_sale']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'og_title', 'og_description', 'og_image']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('reviews');
    }
};
