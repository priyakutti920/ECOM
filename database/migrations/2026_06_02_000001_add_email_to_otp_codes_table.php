<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Switch otp_codes to be keyed by email instead of mobile.
     */
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('id')->index();
            $table->string('mobile', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
            $table->string('mobile', 10)->nullable(false)->change();
        });
    }
};
