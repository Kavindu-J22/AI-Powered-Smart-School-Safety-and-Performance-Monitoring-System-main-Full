<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VideoThreatController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.video-threat.';
    protected string $apiBaseUrl;
    protected int $timeout = 30;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.video_threat.url', 'http://127.0.0.1:5003');
    }

    /**
     * Display the video threat detection dashboard
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
                ->get("{$this->apiBaseUrl}/api/video/status");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get detector status'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Video threat API error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'API service unavailable',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Detect objects in frame
     */
    public function detectObjects(Request $request): JsonResponse
    {
        $request->validate([
            'frame' => 'required|string'
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/video/detect-objects", [
                    'frame' => $request->frame
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Log left-behind object detections
                if ($result['success'] && ($result['left_behind_count'] ?? 0) > 0) {
                    Log::warning('Left-behind objects detected', [
                        'count' => $result['left_behind_count'],
                        'timestamp' => now()->toIso8601String()
                    ]);
                }

                return response()->json($result);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to detect objects'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Object detection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Detect threats in frame
     */
    public function detectThreats(Request $request): JsonResponse
    {
        $request->validate([
            'frame' => 'required|string'
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/video/detect-threats", [
                    'frame' => $request->frame
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Log threat detections
                if ($result['success'] && ($result['result']['is_threat'] ?? false)) {
                    Log::warning('Video threat detected', [
                        'threat_type' => $result['result']['threat_type'] ?? 'unknown',
                        'confidence' => $result['result']['confidence'] ?? 0,
                        'timestamp' => now()->toIso8601String()
                    ]);
                }

                return response()->json($result);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to detect threats'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Threat detection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Process complete frame (objects + threats)
     */
    public function processFrame(Request $request): JsonResponse
    {
        $request->validate([
            'frame' => 'required|string'
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiBaseUrl}/api/video/process-frame", [
                    'frame' => $request->frame
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Log combined detections
                if ($result['success']) {
                    $leftBehindCount = $result['objects']['left_behind_count'] ?? 0;
                    $isThreat = $result['threats']['is_threat'] ?? false;

                    if ($leftBehindCount > 0 || $isThreat) {
                        Log::warning('Video detection alert', [
                            'left_behind_objects' => $leftBehindCount,
                            'threat_detected' => $isThreat,
                            'threat_type' => $result['threats']['threat_type'] ?? null,
                            'timestamp' => now()->toIso8601String()
                        ]);
                    }
                }

                return response()->json($result);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to process frame'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Frame processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get detector status (helper method)
     */
    protected function getDetectorStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiBaseUrl}/api/video/status");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get detector status: ' . $e->getMessage());
        }

        return [
            'status' => 'unavailable',
            'object_detector_loaded' => false,
            'threat_detector_loaded' => false,
            'tracker_active' => false
        ];
    }
}
