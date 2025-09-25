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
            $table->unsignedBigInteger('product_id')->nullable(); // link to products
            $table->string('uuid')->index(); // dzuuid
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->string('storage_path');
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'checksum']);
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
