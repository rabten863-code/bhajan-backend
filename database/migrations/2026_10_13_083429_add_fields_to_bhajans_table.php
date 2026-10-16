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
        Schema::table('bhajans', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->text('lyrics')->nullable();
            $table->string('language')->default('en');
            $table->string('status')->default('draft'); // draft | published | archived
            $table->string('audio_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bhajans', function (Blueprint $table) {
            $table->dropColumn(['title', 'lyrics', 'language', 'status', 'audio_path']);
        });
    }
};
