<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AudioThreatController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.audio-threat.';
    protected string $apiBaseUrl;
    protected int $timeout = 30;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.audio_threat.url', 'http://127.0.0.1:5002');
    }

    /**
     * Display the audio threat detection dashboard
     */
    public function dashboard(): View
    {
        $stats = $this->getDetectorStatus();
        
        return view($this->viewDirectory . 'dashboard', [
            'stats' => $stats,
            'apiUrl' => $this->apiBaseUrl
        ]);
    }

    /**
     * Get detector status from API
     */
    public function status(): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiBaseUrl}/api/audio/status");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get detector status'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Audio threat API error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'API service unavailable',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Analyze audio data
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'audio_data' => 'required|string'
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/audio/analyze", [
                    'audio_data' => $request->audio_data
                ]);
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Log threat detections
                if ($result['success'] && $result['result']['is_threat'] ?? false) {
                    Log::warning('Audio threat detected', [
                        'threat_type' => $result['result']['threat_type'] ?? 'unknown',
                        'threat_level' => $result['result']['threat_level'] ?? 'unknown',
                        'confidence' => $result['result']['confidence'] ?? 0,
                        'timestamp' => now()->toIso8601String()
                    ]);
                }
                
                return response()->json($result);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Analysis failed'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Audio analysis error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Calibrate noise profile
     */
    public function calibrate(Request $request): JsonResponse
    {
        $request->validate([
            'audio_data' => 'required|string'
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/audio/calibrate", [
                    'audio_data' => $request->audio_data
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Calibration failed'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Start detection session
     */
    public function startSession(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/detection/start", [
                    'session_id' => $request->session_id ?? uniqid('session_')
                ]);
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Stop detection session
     */
    public function stopSession(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/detection/stop", [
                    'session_id' => $request->session_id
                ]);
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get private detector status
     */
    private function getDetectorStatus(): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->apiBaseUrl}/api/audio/status");
            
            if ($response->successful()) {
                return $response->json()['detector'] ?? [];
            }
        } catch (\Exception $e) {
            Log::debug('Could not fetch detector status: ' . $e->getMessage());
        }
        
        return [
            'non_speech_model_loaded' => false,
            'noise_profiler' => ['is_calibrated' => false]
        ];
    }
}

