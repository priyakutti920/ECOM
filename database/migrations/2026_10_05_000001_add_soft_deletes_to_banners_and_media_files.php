<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (!Schema::hasColumn('banners', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if (Schema::hasTable('media_files')) {
            Schema::table('media_files', function (Blueprint $table) {
                if (!Schema::hasColumn('media_files', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
                if (!Schema::hasColumn('media_files', 'alt_text')) {
                    $table->string('alt_text', 255)->nullable()->after('name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (Schema::hasColumn('banners', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        if (Schema::hasTable('media_files')) {
            Schema::table('media_files', function (Blueprint $table) {
                if (Schema::hasColumn('media_files', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                if (Schema::hasColumn('media_files', 'alt_text')) {
                    $table->dropColumn('alt_text');
                }
            });
        }
    }
};
