<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PerformancePredictionController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\FaceRecognitionController;

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

    // UID-based RFID scan from serial bridge (UNO R3 + RC522)
    Route::post('/rfid-uid-scan', [AttendanceApiController::class, 'rfidUidScan'])
        ->name('rfid-uid-scan');

    // Poll for last RFID scan result (browser polls this in the attendance UI)
    Route::get('/rfid-last-scan', [AttendanceApiController::class, 'getLastRfidScan'])
        ->name('rfid-last-scan');

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

// Face Recognition proxy endpoints (no auth — same local-network trust model as RFID)
Route::prefix('face')->name('api.face.')->group(function () {
    Route::get('/health', [FaceRecognitionController::class, 'health'])->name('health');
    Route::post('/registration/start', [FaceRecognitionController::class, 'startRegistration'])->name('registration.start');
    Route::post('/registration/capture', [FaceRecognitionController::class, 'captureFrame'])->name('registration.capture');
    Route::post('/training/train/{student_id}', [FaceRecognitionController::class, 'trainStudent'])->name('training.train');
    Route::post('/attendance/recognize', [FaceRecognitionController::class, 'recognize'])->name('attendance.recognize');
    Route::get('/attendance/last-scan', [FaceRecognitionController::class, 'getLastScan'])->name('attendance.last-scan');
    Route::get('/mode', [FaceRecognitionController::class, 'currentMode'])->name('mode');
});

// RFID Enrollment endpoints (public — serial bridge + browser use these)
Route::prefix('rfid')->name('api.rfid.')->group(function () {
    // Serial bridge posts detected UID here (unified — server decides if enrollment or attendance)
    Route::post('/scan', [RfidController::class, 'bridgeScan'])->name('bridge-scan');
    // Browser polls this for pending enrollment UID
    Route::get('/enrollment-poll/{token}', [RfidController::class, 'pollEnrollment'])->name('enrollment-poll');
});
