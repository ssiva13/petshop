<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->uuid('category_uuid');
            $table->uuid('brand_uuid');
            $table->string('title');
            $table->double('price', 12, 2);
            $table->text('description');
            $table->json('metadata');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('category_uuid')->references('uuid')->on('categories');
            $table->foreign('brand_uuid')->references('uuid')->on('brands');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
