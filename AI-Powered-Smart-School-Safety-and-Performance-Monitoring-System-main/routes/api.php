<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PerformancePredictionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes for Arduino attendance device
Route::prefix('attendance')->name('api.attendance.')->group(function () {
    // RFID scan endpoint - receives attendance data from Arduino WiFi device
    Route::post('/rfid-scan', [AttendanceApiController::class, 'rfidScan'])
        ->name('rfid-scan');

    // Device registration and health check
    Route::post('/device/register', [AttendanceApiController::class, 'registerDevice'])
        ->name('device.register');

    Route::post('/device/ping', [AttendanceApiController::class, 'devicePing'])
        ->name('device.ping');

    // Sync pending attendance records from SD card
    Route::post('/sync', [AttendanceApiController::class, 'syncPendingRecords'])
        ->name('sync');

    // Get device configuration
    Route::get('/device/config', [AttendanceApiController::class, 'getDeviceConfig'])
        ->name('device.config');
});

// Protected API routes (require authentication)
Route::middleware('auth')->group(function () {
    // Attendance management
    Route::prefix('attendance')->name('api.attendance.')->controller(AttendanceApiController::class)->group(function () {
        Route::get('/today', 'getTodayAttendance')->name('today');
        Route::get('/statistics', 'getStatistics')->name('statistics');
        Route::get('/report', 'getReport')->name('report');
        Route::get('/student/{studentId}', 'getStudentAttendance')->name('student');
    });

    // Performance Prediction (AI Model)
    Route::prefix('prediction')->name('api.prediction.')->controller(PerformancePredictionController::class)->group(function () {
        Route::get('/health', 'health')->name('health');
        Route::post('/batch', 'batchPredictions')->name('batch');
    });

    // Student prediction endpoints with flexible authentication (session + API tokens)
    Route::prefix('students')->name('api.students.')->group(function () {
        Route::get('{studentId}/prediction', [PerformancePredictionController::class, 'getPrediction'])
            ->name('prediction');
    });
});

// Public prediction endpoint for authenticated dashboard users (session-based auth)
Route::get('/students/{studentId}/prediction', [PerformancePredictionController::class, 'getPrediction'])
    ->middleware(['auth', 'web'])
    ->withoutMiddleware('api')
    ->name('api.students.prediction.public');
