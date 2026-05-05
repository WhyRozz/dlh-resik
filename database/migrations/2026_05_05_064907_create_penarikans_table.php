<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penarikans', function (Blueprint $table) {
            $table->id();

            // User relation (polymorphic)
            $table->unsignedBigInteger('user_id');
            $table->enum('tipe_user', ['masyarakat', 'pns']);

            // Data penarikan
            $table->string('nama');
            $table->string('e_wallet'); // Dana, OVO, GoPay, ShopeePay
            $table->string('nomor_e_wallet');
            $table->decimal('nominal', 15, 2);

            // Status & tracking
            $table->enum('status', ['pending', 'proses', 'selesai', 'ditolak'])->default('pending');
            $table->string('id_transaksi')->nullable(); // ID dari payment gateway (opsional)
            $table->text('catatan_admin')->nullable();

            // Timestamps
            $table->timestamp('diproses_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();

            // Index
            $table->index(['user_id', 'tipe_user']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penarikans');
    }
};
