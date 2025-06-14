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
            $table->string('foto');
            $table->timestamp('tanggal')->useCurrentOnUpdate()->useCurrent();
            $table->string('status', 50);
            $table->integer('id_kategori');
            $table->integer('id_user');
            $table->timestamps();
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
