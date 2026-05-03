<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin');
            $table->string('email', 255)->unique();
            $table->string('password', 100);
            $table->text('password_encrypted')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('otp', 6)->nullable();
            $table->dateTime('otp_expires')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin');
    }
};
