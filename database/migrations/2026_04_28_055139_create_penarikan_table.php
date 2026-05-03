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
        Schema::create('penarikan', function (Blueprint $table) {
            $table->id('id_penarikan');
            $table->unsignedBigInteger('id_masyarakat')->nullable();
            $table->unsignedBigInteger('id_pns')->nullable();
            $table->decimal('jumlah_uang', 10, 2)->nullable();
            $table->string('jenis_ewallet', 50)->nullable();
            $table->string('nomor_ewallet', 50)->nullable();
            $table->enum('status', ['diproses', 'berhasil', 'ditolak'])->default('diproses');
            $table->timestamp('tanggal_penarikan')->useCurrent();
            
            // Jika ada tabel masyarakat dan pns, aktifkan foreign key ini:
            // $table->foreign('id_masyarakat')->references('id')->on('masyarakats')->onDelete('cascade');
            // $table->foreign('id_pns')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penarikan');
    }
};