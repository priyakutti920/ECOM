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
 Schema::create('otp_codes', function (Blueprint $table) {
 $table->id();
 $table->string('mobile', 10)->index();
 $table->string('code', 4);
 $table->string('purpose', 20)->default('login');
 $table->timestamp('expires_at');
 $table->timestamp('verified_at')->nullable();
 $table->timestamps();
 });
 }

 /**
 * Reverse the migrations.
 */
 public function down(): void
 {
 Schema::dropIfExists('otp_codes');
 }
};