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
        Schema::create('pritunl_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pritunl_id')->constrained('pritunls')->onDelete('cascade');
            $table->string("server_ip",20)->nullable();
            $table->string("status",20)->nullable();
            $table->string("name",50)->nullable();
            $table->string("internal_user_id",50)->nullable();
            $table->string("opt_secret",100)->nullable();
            $table->boolean("is_online")->default(false);
            $table->dateTime("last_active")->nullable();
            $table->string("vpn_config_path")->nullable();
            $table->boolean("disabled")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pritunl_users');
    }
};
