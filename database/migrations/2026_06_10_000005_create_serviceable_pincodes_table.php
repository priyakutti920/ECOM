<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('serviceable_pincodes')) {
            Schema::create('serviceable_pincodes', function (Blueprint $table) {
                $table->id();
                $table->string('pincode', 10)->unique();
                $table->string('city', 120)->nullable();
                $table->string('state', 120)->nullable();
                $table->boolean('is_serviceable')->default(true);
                $table->boolean('is_cod_available')->default(true);
                $table->integer('estimated_days')->default(4);
                $table->string('courier_name', 100)->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();

                $table->index('pincode');
                $table->index(['is_serviceable', 'is_cod_available']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('serviceable_pincodes');
    }
};
