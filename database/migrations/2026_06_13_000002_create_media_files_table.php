<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('media_files')) {
            Schema::create('media_files', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('filename', 255);
                $table->string('path', 500);
                $table->string('disk', 50)->default('public');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('folder', 100)->default('general');
                $table->boolean('is_favorite')->default(false);
                $table->timestamps();

                $table->index('folder');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
