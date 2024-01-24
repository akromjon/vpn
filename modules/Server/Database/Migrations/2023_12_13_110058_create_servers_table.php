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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->nullable();
            $table->string("name");
            $table->string("status",50)->default("new");
            $table->string("provider",20)->default("digitalocean");
            $table->ipAddress("ip")->unique()->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("country_code",3)->nullable();
            $table->string("flag")->nullable();
            $table->json("config")->nullable();
            $table->float("price")->nullable();
            $table->json("localization")->nullable();
            $table->index('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
