<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->decimal('bonus_percent', 5, 2)->comment('percentage off on entire purchase amount');
            $table->decimal('min_amount', 10, 2)->default(0)->comment('minimum purchase amount to qualify');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
