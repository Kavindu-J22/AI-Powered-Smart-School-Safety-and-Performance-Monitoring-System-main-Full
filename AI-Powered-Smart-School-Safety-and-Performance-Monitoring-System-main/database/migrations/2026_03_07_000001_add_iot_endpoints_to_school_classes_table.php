<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add IoT sensor endpoint columns to school_classes.
     *
     * camera_ip  – streaming IP/URL of the ESP32-CAM for this classroom
     *              (e.g. "192.168.1.101" or "http://192.168.1.101/stream")
     * audio_ip   – IP/URL of the separate ESP32 audio module for this classroom
     *              (e.g. "192.168.1.102" or "http://192.168.1.102/audio")
     */
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('camera_ip')->nullable()->after('room_number')
                ->comment('ESP32-CAM streaming IP or full URL for this classroom');
            $table->string('audio_ip')->nullable()->after('camera_ip')
                ->comment('ESP32 audio module IP or full URL for this classroom');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn(['camera_ip', 'audio_ip']);
        });
    }
};

