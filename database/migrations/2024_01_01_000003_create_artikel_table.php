<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id('id_artikel');
            $table->string('judul', 255);
            $table->text('deskripsi')->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamp('tanggal')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('artikel');
    }
};
