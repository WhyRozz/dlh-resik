<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_masyarakat')->nullable();
            $table->string('nama', 100)->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Diproses', 'Diterima', 'Ditolak', 'Ditarik'])->default('Diproses');
            $table->text('balasan')->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->date('tanggal')->nullable();

            $table->foreign('id_masyarakat')->references('id_masyarakat')->on('masyarakat')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('laporan');
    }
};
