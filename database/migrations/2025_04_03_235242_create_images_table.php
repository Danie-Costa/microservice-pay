<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('path');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('gallery_id')->nullable();
            $table->string('external_reference')->nullable();
            $table->timestamps();

            // Se quiser criar uma foreign key com a tabela projects:
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
