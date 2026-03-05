<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AudioVideoThreatController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.audio-video-threat.';
    protected string $audioApiUrl;
    protected string $videoApiUrl;
    protected int $timeout = 30;

    // Alert email recipient
    protected string $alertEmail = 'cn3581743@gmail.com';

    public function __construct()
    {
        $this->audioApiUrl = config('services.audio_threat.url', 'http://127.0.0.1:5002');
        $this->videoApiUrl = config('services.video_threat.url', 'http://127.0.0.1:5003');
    }

    /**
     * Display the combined Audio & Video threat detection dashboard
     */
    public function dashboard(): View
    {
        $audioStats = $this->getAudioStatus();
        $videoStats = $this->getVideoStatus();

        return view($this->viewDirectory . 'dashboard', [
            'audioStats' => $audioStats,
            'videoStats' => $videoStats,
            'audioApiUrl' => $this->audioApiUrl,
            'videoApiUrl' => $this->videoApiUrl,
        ]);
    }

    /**
     * Get audio detector status
     */
    public function audioStatus(): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->audioApiUrl}/api/audio/status");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['status' => 'error', 'message' => 'Audio API unavailable'], 503);
        } catch (\Exception $e) {
            Log::error('Audio-Video: Audio status error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 503);
        }
    }

    /**
     * Get video detector status
     */
    public function videoStatus(): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->videoApiUrl}/api/video/status");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['status' => 'error', 'message' => 'Video API unavailable'], 503);
        } catch (\Exception $e) {
            Log::error('Audio-Video: Video status error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 503);
        }
    }

    /**
     * Analyze audio data (proxy to audio API)
     */
    public function analyzeAudio(Request $request): JsonResponse
    {
        $request->validate(['audio_data' => 'required|string']);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->audioApiUrl}/api/audio/analyze", [
                    'audio_data'  => $request->audio_data,
                    'format'      => $request->input('format', 'auto'),
                    'sample_rate' => $request->input('sample_rate', 16000),
                    'session_id'  => $request->input('session_id'),
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if (!empty($result['success']) && !empty($result['result']['is_threat'])) {
                    Log::warning('AudioVideo: Audio threat detected', [
                        'threat_type'  => $result['result']['threat_type'] ?? 'unknown',
                        'threat_level' => $result['result']['threat_level'] ?? 'unknown',
                    ]);
                }
                return response()->json($result);
            }

            return response()->json(['success' => false, 'error' => 'Audio analysis failed'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Process video frame (proxy to video API)
     */
    public function processFrame(Request $request): JsonResponse
    {
        $request->validate(['frame' => 'required|string']);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->videoApiUrl}/api/video/process-frame", [
                    'frame' => $request->frame,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if (!empty($result['success'])) {
                    $isThreat = !empty($result['threats']['is_threat']);
                    if ($isThreat) {
                        Log::warning('AudioVideo: Video threat detected', [
                            'threat_type' => $result['threats']['threat_type'] ?? 'unknown',
                        ]);
                    }
                }
                return response()->json($result);
            }

            return response()->json(['success' => false, 'error' => 'Frame processing failed'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Calibrate audio noise profile
     */
    public function calibrateAudio(Request $request): JsonResponse
    {
        $request->validate(['audio_data' => 'required|string']);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->audioApiUrl}/api/audio/calibrate", [
                    'audio_data' => $request->audio_data,
                ]);

            return response()->json($response->successful()
                ? $response->json()
                : ['success' => false, 'error' => 'Calibration failed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Start audio detection session
     */
    public function startAudioSession(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->audioApiUrl}/api/detection/start", [
                    'session_id' => $request->input('session_id', uniqid('av_session_')),
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Stop audio detection session
     */
    public function stopAudioSession(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->audioApiUrl}/api/detection/stop", [
                    'session_id' => $request->input('session_id'),
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Send combined critical threat alert email.
     * Called when BOTH audio and video threats are simultaneously detected.
     */
    public function sendCombinedAlert(Request $request): JsonResponse
    {
        try {
            $audioThreat = $request->input('audio_threat', []);
            $videoThreat = $request->input('video_threat', []);
            $timestamp   = now()->format('Y-m-d H:i:s');

            $audioType  = $audioThreat['threat_type']  ?? 'Unknown';
            $audioLevel = $audioThreat['threat_level'] ?? 'High';
            $audioConf  = round(($audioThreat['confidence'] ?? 0) * 100, 1);
            $videoType  = $videoThreat['threat_type']  ?? 'Unknown';
            $videoConf  = round(($videoThreat['confidence'] ?? 0) * 100, 1);

            $subject = 'CRITICAL COMBINED THREAT ALERT - School Safety System';

            $body  = "CRITICAL PRIORITY ALERT - SIMULTANEOUS AUDIO & VIDEO THREAT DETECTED\n";
            $body .= str_repeat('=', 65) . "\n\n";
            $body .= "Timestamp : {$timestamp}\n\n";
            $body .= "AUDIO THREAT\n";
            $body .= "  Type       : {$audioType}\n";
            $body .= "  Level      : {$audioLevel}\n";
            $body .= "  Confidence : {$audioConf}%\n\n";
            $body .= "VIDEO THREAT\n";
            $body .= "  Type       : {$videoType}\n";
            $body .= "  Confidence : {$videoConf}%\n\n";
            $body .= "ACTION REQUIRED: Simultaneous audio and video threats indicate a high-risk\n";
            $body .= "incident. Please review surveillance footage and dispatch security immediately.\n\n";
            $body .= str_repeat('-', 65) . "\n";
            $body .= "This alert was generated automatically by the School Safety Monitoring System.\n";

            Mail::raw($body, function ($message) use ($subject) {
                $message->to($this->alertEmail)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address', 'no-reply@school.edu'),
                        config('mail.from.name', 'School Safety System')
                    );
            });

            Log::critical('AudioVideo: COMBINED CRITICAL ALERT sent', [
                'audio_threat' => $audioType,
                'video_threat' => $videoType,
                'email'        => $this->alertEmail,
                'timestamp'    => $timestamp,
            ]);

            return response()->json(['success' => true, 'message' => 'Critical alert sent']);
        } catch (\Exception $e) {
            Log::error('AudioVideo: Failed to send combined alert: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Private helper: fetch audio API status for dashboard view
     */
    private function getAudioStatus(): array
    {
        try {
            $resp = Http::timeout(5)->get("{$this->audioApiUrl}/api/audio/status");
            if ($resp->successful()) {
                return $resp->json()['detector'] ?? [];
            }
        } catch (\Exception $e) {
            Log::debug('AudioVideo: Could not fetch audio status: ' . $e->getMessage());
        }
        return ['non_speech_model_loaded' => false];
    }

    /**
     * Private helper: fetch video API status for dashboard view
     */
    private function getVideoStatus(): array
    {
        try {
            $resp = Http::timeout(5)->get("{$this->videoApiUrl}/api/video/status");
            if ($resp->successful()) {
                return $resp->json();
            }
        } catch (\Exception $e) {
            Log::debug('AudioVideo: Could not fetch video status: ' . $e->getMessage());
        }
        return ['object_detector_loaded' => false, 'threat_detector_loaded' => false];
    }
}
