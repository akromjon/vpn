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
            $table->string("device_info",10);
            $table->string("status",25)->nullable();
            $table->string("email")->unique();
            $table->string("ip_address",50);
            $table->string("country_code",10)->nullable();
            $table->dateTime("last_seen_at")->nullable();
            $table->unsignedBigInteger("data_usage")->nullable();
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
