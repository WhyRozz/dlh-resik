<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('penarikans', function (Blueprint $table) {
        $table->id();
        
        // ✅ PERBAIKAN: Gunakan integer() (signed) agar cocok dengan int(11) di masyarakat
        $table->integer('user_id');  // ← Bukan unsignedInteger!
        
        // Kolom lainnya
        $table->string('nama');
        $table->timestamp('waktu_penarikan');
        $table->string('jenis')->default('Dana');
        $table->string('nomor_ewallet')->nullable();
        $table->decimal('jumlah', 15, 2);
        $table->enum('status', ['Diproses', 'Diterima', 'Ditolak'])->default('Diproses');
        $table->text('catatan')->nullable();
        $table->timestamps();
        
        // ✅ Sesuaikan charset/collation PERSIS dengan tabel masyarakat
        $table->engine = 'InnoDB';
        $table->charset = 'utf8mb4';
        $table->collation = 'utf8mb4_general_ci';  // ← Harus sama: general_ci
        
        // ✅ Foreign key dalam satu Schema::create (jangan dipisah!)
        $table->foreign('user_id', 'fk_penarikans_user_id')
              ->references('id_masyarakat')
              ->on('masyarakat')
              ->onDelete('cascade')
              ->onUpdate('cascade');
    });
}
    public function down(): void
    {
        Schema::dropIfExists('penarikans');
    }
};