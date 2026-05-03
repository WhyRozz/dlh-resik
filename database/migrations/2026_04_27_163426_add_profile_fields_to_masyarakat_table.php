<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('masyarakat', function (Blueprint $table) {
            // Jenis kelamin
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('nama');

            // Nomor telepon
            $table->string('no_telp', 15)->nullable()->after('jenis_kelamin');

            // Pekerjaan (untuk ASN: Dinas Kominfo, Dinas Lingkungan, dll)
            $table->string('pekerjaan', 100)->nullable()->after('no_telp');

            // Alamat lengkap
            $table->text('alamat')->nullable()->after('pekerjaan');

            // QR Code path
            $table->string('qr_code_path')->nullable()->after('alamat');

            // Saldo bank sampah (default 0)
            $table->decimal('saldo_bank_sampah', 15, 2)->default(0)->after('qr_code_path');
        });
    }

    public function down()
    {
        Schema::table('masyarakat', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'no_telp', 'pekerjaan', 'alamat', 'qr_code_path', 'saldo_bank_sampah']);
        });
    }
};
