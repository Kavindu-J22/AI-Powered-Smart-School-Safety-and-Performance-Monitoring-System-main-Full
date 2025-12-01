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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->string('profile_image')->nullable()->after('date_of_birth');
            $table->integer('login_count')->default(0)->after('profile_image');
            $table->timestamp('last_login_at')->nullable()->after('login_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'bio',
                'date_of_birth',
                'profile_image',
                'login_count',
                'last_login_at',
            ]);
        });
    }
};
