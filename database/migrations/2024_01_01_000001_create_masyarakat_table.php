<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('masyarakat', function (Blueprint $table) {
            $table->id('id_masyarakat');
            $table->string('nama', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 100);
            $table->string('otp', 6)->nullable();
            $table->dateTime('otp_expires')->nullable();
            $table->string('google_id', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('masyarakat');
    }
};
