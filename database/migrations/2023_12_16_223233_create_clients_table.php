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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->string("os_type",20);
            $table->string("os_version",20);
            $table->string("model",50);
            $table->string("status",50)->default("active");
            $table->string("email")->nullable()->unique();
            $table->string("name")->nullable();
            $table->dateTime("last_used_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
