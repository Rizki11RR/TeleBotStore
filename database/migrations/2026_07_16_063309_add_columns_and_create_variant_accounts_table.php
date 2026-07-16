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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['ebook', 'account'])->default('ebook')->after('category_id');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('original_price', 12, 2)->nullable()->after('price');
        });

        Schema::create('product_variant_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('username_email');
            $table->string('password');
            $table->boolean('is_sold')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('is_sold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_accounts');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('original_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
