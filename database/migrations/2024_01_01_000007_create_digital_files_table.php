<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->enum('delivery_type', ['file', 'text', 'manual']);
            $table->text('content')->nullable()->comment('For type=text: the content to send');
            $table->string('file_path', 255)->nullable()->comment('For type=file: path to the file');
            $table->string('file_name', 255)->nullable()->comment('Original file name');
            $table->text('notes')->nullable()->comment('For type=manual: manual delivery instructions');
            $table->timestamps();

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_files');
    }
};
