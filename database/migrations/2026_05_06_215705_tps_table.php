<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tps', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tps', 100);
            $table->text('alamat');
            $table->string('kecamatan', 100);
            $table->string('kelurahan', 100)->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('kapasitas', 50); // e.g., "5-6 m3"
            $table->text('jenis_sampah')->nullable();
            $table->string('jam_operasional', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'non-aktif'])->default('aktif');
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index(['nama_tps', 'kecamatan']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tps');
    }
};
