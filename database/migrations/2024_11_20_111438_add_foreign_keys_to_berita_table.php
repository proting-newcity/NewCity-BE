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
        Schema::table('berita', function (Blueprint $table) {
            $table->foreign(['id_kategori'], 'kategori_berita')->references(['id'])->on('kategori_berita')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['id_user'], 'editor')->references(['id'])->on('user')->onUpdate('cascade')->onDelete('restrict');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berita', function (Blueprint $table) {
            $table->dropForeign('kategori_berita');
        });
    }
};
