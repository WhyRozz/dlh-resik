<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('masyarakat', function (Blueprint $table) {
            $table->string('reset_token')->nullable()->after('otp_expires');
            $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
        });

        Schema::table('pns', function (Blueprint $table) {
            $table->string('reset_token')->nullable()->after('otp_expires');
            $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
        });
    }

    public function down()
    {
        Schema::table('masyarakat', function (Blueprint $table) {
            $table->dropColumn(['reset_token', 'reset_token_expires']);
        });

        Schema::table('pns', function (Blueprint $table) {
            $table->dropColumn(['reset_token', 'reset_token_expires']);
        });
    }
};
