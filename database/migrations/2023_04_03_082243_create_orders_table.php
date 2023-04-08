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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->uuid('user_uuid');
            $table->uuid('order_status_uuid');
            $table->uuid('payment_uuid')->nullable();

            $table->json('products');
            $table->json('address');
            $table->double('delivery_fee', 12, 2)->nullable();
            $table->double('amount', 12, 2);
            $table->timestamps();
            $table->timestamp('shipped_at')->nullable();
            $table->softDeletes();


            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('order_status_uuid')->references('uuid')->on('order_statuses');
            $table->foreign('payment_uuid')->references('uuid')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
