<?php

use App\Enums\DateFormat;
use App\Enums\TimeFormat;
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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('logo')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_address')->nullable();
            $table->string('mail_signature')->nullable();
            $table->enum('date_format', DateFormat::values())->default(DateFormat::DMY());
            $table->enum('time_format', TimeFormat::values())->default(TimeFormat::HIS());
            $table->enum('timezone', array_keys(config('app.timezones')))->default('Asia/Colombo');
            $table->enum('country', array_keys(config('app.countries')))->default('LK');
            $table->longText('copyright_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
