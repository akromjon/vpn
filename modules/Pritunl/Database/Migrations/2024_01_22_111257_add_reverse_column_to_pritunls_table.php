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
        Schema::table('pritunls', function (Blueprint $table) {
            $table->boolean('reverse_action_enabled')->default(false);
            $table->string('reverse_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pritunls', function (Blueprint $table) {
            $table->dropColumn('reverse_action_enabled');
            $table->dropColumn('reverse_value');
        });
    }
};
