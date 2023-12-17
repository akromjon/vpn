<?php

use App\Enum\CloudProviderEnum;
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
            $table->string("region",50)->nullable();
            $table->string("country_code",10)->nullable();
            $table->string("size",50)->nullable();
            $table->string("image_id",15)->nullable();
            $table->json("ssh_key_ids")->nullable();
            $table->string("project_id",100)->nullable();
            $table->string("status",50)->nullable();
            $table->string("cloud_provider_type",20)->nullable();
            $table->ipAddress("public_ip_address")->nullable();
            $table->ipAddress("private_ip_address")->nullable();
            $table->dateTime("server_created_at")->nullable();
            $table->float("price")->nullable();
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
