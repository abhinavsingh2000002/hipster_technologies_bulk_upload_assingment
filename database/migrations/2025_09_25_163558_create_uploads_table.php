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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('dzuuid')->index()->unique();
            $table->string('filename');
            $table->unsignedBigInteger('size');
            $table->string('checksum', 128)->nullable();
            $table->string('disk')->default('public');
            $table->string('path')->nullable();
            $table->enum('status', ['pending', 'assembled', 'checksum_failed', 'processing', 'done', 'failed'])->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
