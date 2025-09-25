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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('file_name');
            $table->string('mime')->nullable();
            $table->bigInteger('size')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('upload_id')->references('id')->on('uploads')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
