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
        Schema::create('report', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('judul', 100);
            $table->string('deskripsi');
            $table->string('lokasi');
            $table->json('status');
            $table->binary('foto');
            $table->integer('id_masyarakat')->index('masyarakat_fk');
            $table->integer('id_pemerintah')->index('pemerintah_fk');
            $table->integer('id_kategori')->index('kategori_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
