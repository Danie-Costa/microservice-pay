<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->enum('status',['approved', 'pending', 'failure'])->default('pending');
            $table->string('external_reference')->nullable();
            $table->string('internal_reference')->nullable();
            $table->string('preference_id');
            $table->string('payment_id')->nullable();
            $table->string('title');
            $table->integer('quantity');
            $table->decimal('price', 19, 4);
            $table->integer('fee');
            $table->decimal('price_fee', 19, 4);
            $table->string('success_url');
            $table->string('failure_url');
            $table->string('pending_url');
            $table->string('email',255);
            $table->string('name',255);
            $table->string('phone',255);
            $table->string('cpf',255);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
