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
        Schema::table('rating_berita', function (Blueprint $table) {
            $table->foreign(['id_berita'], 'berita_rating_berita')->references(['id'])->on('berita')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_user'], 'user_rating_berita')->references(['id'])->on('user')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rating_berita', function (Blueprint $table) {
            $table->dropForeign('berita_rating_berita');
            $table->dropForeign('user_rating_berita');
        });
    }
};
