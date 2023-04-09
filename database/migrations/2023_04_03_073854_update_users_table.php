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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->unique()->after('id');
            $table->string('first_name')->after('uuid');
            $table->string('last_name')->after('first_name');
            $table->boolean('is_admin')->default('0')->after('last_name');
            $table->uuid('avatar')->after('password')->nullable();
            $table->string('address')->after('avatar');
            $table->string('phone_number')->after('address');
            $table->boolean('is_marketing')->default('0')->after('phone_number');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
