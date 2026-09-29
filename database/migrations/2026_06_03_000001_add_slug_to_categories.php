<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug', 200)->nullable()->unique()->after('name');
            }
        });

        // Auto-generate slugs for existing categories
        \Illuminate\Support\Facades\DB::table('categories')
            ->whereNull('slug')
            ->where('status', 0)
            ->orderBy('id')
            ->each(function ($cat) {
                $base = \Illuminate\Support\Str::slug($cat->name);
                $slug = $base;
                $i = 1;
                while (\Illuminate\Support\Facades\DB::table('categories')->where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                \Illuminate\Support\Facades\DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};