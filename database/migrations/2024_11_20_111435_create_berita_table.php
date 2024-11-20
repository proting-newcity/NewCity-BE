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
        Schema::create('berita', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 50);
            $table->string('content');
            $table->binary('photo');
            $table->timestamp('tanggal')->useCurrentOnUpdate()->useCurrent();
            $table->integer('id_kategori')->index('kategori_fk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
