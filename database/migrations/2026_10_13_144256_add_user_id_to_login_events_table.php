<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On a fresh database, create_login_events_table already adds user_id
        if (Schema::hasColumn('login_events', 'user_id')) {
            return;
        }

        Schema::table('login_events', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // The column belongs to create_login_events_table
    }
};