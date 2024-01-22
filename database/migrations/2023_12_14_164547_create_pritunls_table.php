<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pritunls', function (Blueprint $table) {
            $table->id();
            $table->string("uuid")->unique();
            $table->string("username",100);
            $table->string("password",100);
            $table->string("organization_id",100)->nullable();
            $table->string("internal_server_id",100)->nullable();
            $table->string("internal_server_status",30)->nullable();
            $table->string("status",50)->nullable();
            $table->string("sync_status",20)->default("not_synced");
            $table->unsignedBigInteger("user_count")->nullable();
            $table->unsignedBigInteger("online_user_count")->default(0);
            $table->foreignId('server_id')->unique()->constrained('servers')->onDelete('cascade');
            $table->index('server_id');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pritunls');
    }
};
