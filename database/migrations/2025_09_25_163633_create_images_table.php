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
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // assumes products table exists
            $table->foreignId('upload_id')->constrained('uploads')->onDelete('cascade');
            $table->string('original_path');
            $table->string('variant_256')->nullable();
            $table->string('variant_512')->nullable();
            $table->string('variant_1024')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'upload_id']); // ensure re-attach is a no-op
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
