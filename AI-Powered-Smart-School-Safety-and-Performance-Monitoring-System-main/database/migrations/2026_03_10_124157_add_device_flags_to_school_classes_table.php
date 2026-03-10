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
        Schema::table('school_classes', function (Blueprint $table) {
            // Whether the camera device is disabled for this classroom
            $table->boolean('camera_off')->default(false)->after('camera_ip')
                ->comment('Set true to disable camera monitoring for this classroom');
            // Whether the microphone/audio device is disabled for this classroom
            $table->boolean('mic_off')->default(false)->after('audio_ip')
                ->comment('Set true to disable microphone monitoring for this classroom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn(['camera_off', 'mic_off']);
        });
    }
};
