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
        Schema::table('interests', function (Blueprint $table) {
            $table->dropForeign(['interest_id']);
            $table->dropColumn('interest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
        });
    }
};
