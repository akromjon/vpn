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
        Schema::create('client_pritunl_user_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('pritunl_user_id')->constrained('pritunl_users')->onDelete('cascade');
            $table->enum("status",['connected','disconnected','idle'])->default('connected');
            $table->dateTime("connected_at")->nullable();
            $table->dateTime("disconnected_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_pritunl_user_connections');
    }
};
