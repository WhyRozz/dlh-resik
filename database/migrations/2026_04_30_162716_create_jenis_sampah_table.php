<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jenis_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('gambar')->nullable();
            $table->string('jenis');
            $table->string('satuan'); // Kg, Lt, Pcs, dll
            $table->decimal('harga', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jenis_sampah');
    }
};
