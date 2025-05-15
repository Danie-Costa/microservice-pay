<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('external_reference')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('fee')->default(97);
            $table->string('mp_user_id')->nullable();
            $table->text('mp_access_token')->nullable();
            $table->text('mp_refresh_token')->nullable();
            $table->text('mp_public_key')->nullable();
            $table->integer('mp_expires_in')->nullable();
            $table->timestamp('mp_token_created_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};
