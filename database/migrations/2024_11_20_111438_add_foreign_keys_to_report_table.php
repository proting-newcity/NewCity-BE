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
        Schema::table('report', function (Blueprint $table) {
            $table->integer('id_pemerintah')->nullable()->change();
            $table->integer('id_kategori')->nullable()->change();

            $table->foreign(['id_kategori'], 'kategori_report')->references(['id'])->on('kategori_report')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_masyarakat'], 'masyarakat_report')->references(['id'])->on('masyarakat')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_pemerintah'], 'pemerintah_report')->references(['id'])->on('pemerintah')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report', function (Blueprint $table) {
            $table->dropForeign('kategori_report');
            $table->dropForeign('masyarakat_report');
            $table->dropForeign('pemerintah_report');
        });
    }
};
