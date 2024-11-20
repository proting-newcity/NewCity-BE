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
        Schema::table('bookmark', function (Blueprint $table) {
            $table->foreign(['id_report'], 'report_bookmark')->references(['id'])->on('report')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_user'], 'user_bookmark')->references(['id'])->on('user')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookmark', function (Blueprint $table) {
            $table->dropForeign('report_bookmark');
            $table->dropForeign('user_bookmark');
        });
    }
};
