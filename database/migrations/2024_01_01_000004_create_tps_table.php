<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tps', function (Blueprint $table) {
            $table->id('id_tps');
            $table->string('nama_tps', 150);
            $table->string('lokasi', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kapasitas', 20)->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tps');
    }
};
