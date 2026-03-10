<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class AudioVideoThreatController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.audio-video-threat.';
    protected string $audioApiUrl;
    protected string $videoApiUrl;
    protected int $timeout = 30;

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
        $classrooms = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get(['id', 'class_name', 'grade_level', 'section', 'room_number', 'camera_ip', 'camera_port', 'camera_off', 'audio_ip', 'audio_port', 'mic_off']);

        return view($this->viewDirectory . 'dashboard', [
            'audioStats'  => $audioStats,
            'videoStats'  => $videoStats,
            'audioApiUrl' => $this->audioApiUrl,
            'videoApiUrl' => $this->videoApiUrl,
            'classrooms'  => $classrooms,
        ]);
    }

    /**
     * Return all active classrooms with their IoT endpoints (JSON)
     */
    public function classrooms(): JsonResponse
    {
        $classrooms = SchoolClass::where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get(['id', 'class_name', 'grade_level', 'section', 'room_number', 'camera_ip', 'camera_port', 'camera_off', 'audio_ip', 'audio_port', 'mic_off']);

        return response()->json(['success' => true, 'classrooms' => $classrooms]);
    }

    /**
     * Save the camera_ip and audio_ip for a specific classroom (JSON API, used by dashboard).
     */
    public function updateClassroomDevices(Request $request): JsonResponse
    {
        $request->validate([
            'classroom_id' => 'required|exists:school_classes,id',
            'camera_ip'    => 'nullable|string|max:255',
            'camera_port'  => 'nullable|string|max:10',
            'audio_ip'     => 'nullable|string|max:255',
            'audio_port'   => 'nullable|string|max:10',
        ]);

        $classroom = SchoolClass::findOrFail($request->classroom_id);
        $classroom->update([
            'camera_ip'   => $request->camera_ip,
            'camera_port' => $request->input('camera_port', '80'),
            'audio_ip'    => $request->audio_ip,
            'audio_port'  => $request->input('audio_port', '5002'),
        ]);

        Log::info('AudioVideo: Classroom IoT devices updated', [
            'classroom_id' => $classroom->id,
            'class_name'   => $classroom->class_name,
            'camera_ip'    => $classroom->camera_ip,
            'camera_port'  => $classroom->camera_port,
            'audio_ip'     => $classroom->audio_ip,
            'audio_port'   => $classroom->audio_port,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "IoT endpoints saved for {$classroom->class_name}",
            'classroom' => $classroom->only(['id', 'class_name', 'grade_level', 'section', 'room_number', 'camera_ip', 'camera_port', 'camera_off', 'audio_ip', 'audio_port', 'mic_off']),
        ]);
    }

    /**
     * Show the Classroom IoT Setup management page.
     */
    public function classroomSetup(): View
    {
        $classrooms = SchoolClass::orderBy('grade_level')->orderBy('class_name')->get([
            'id',
            'class_name',
            'grade_level',
            'section',
            'room_number',
            'camera_ip',
            'camera_port',
            'camera_off',
            'audio_ip',
            'audio_port',
            'mic_off',
            'status',
        ]);

        return view($this->viewDirectory . 'classroom-setup', compact('classrooms'));
    }

    /**
     * Save IoT device settings (camera IP/port, camera_off, audio IP/port, mic_off) for one classroom.
     * Called from the Classroom IoT Setup page.
     */
    public function saveClassroomSetup(Request $request): JsonResponse
    {
        $request->validate([
            'classroom_id' => 'required|exists:school_classes,id',
            'camera_ip'    => 'nullable|string|max:255',
            'camera_port'  => 'nullable|string|max:10',
            'camera_off'   => 'nullable|boolean',
            'audio_ip'     => 'nullable|string|max:255',
            'audio_port'   => 'nullable|string|max:10',
            'mic_off'      => 'nullable|boolean',
        ]);

        $classroom = SchoolClass::findOrFail($request->classroom_id);
        $classroom->update([
            'camera_ip'   => $request->input('camera_ip', $classroom->camera_ip),
            'camera_port' => $request->input('camera_port', $classroom->camera_port ?? '80'),
            'camera_off'  => (bool) $request->input('camera_off', false),
            'audio_ip'    => $request->input('audio_ip', $classroom->audio_ip),
            'audio_port'  => $request->input('audio_port', $classroom->audio_port ?? '5002'),
            'mic_off'     => (bool) $request->input('mic_off', false),
        ]);

        Log::info('AudioVideo: Classroom IoT setup saved', [
            'classroom_id' => $classroom->id,
            'class_name'   => $classroom->class_name,
            'camera_ip'    => $classroom->camera_ip,
            'camera_port'  => $classroom->camera_port,
            'camera_off'   => $classroom->camera_off,
            'audio_ip'     => $classroom->audio_ip,
            'audio_port'   => $classroom->audio_port,
            'mic_off'      => $classroom->mic_off,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "IoT settings saved for {$classroom->class_name}",
            'classroom' => $classroom->only(['id', 'class_name', 'grade_level', 'section', 'room_number', 'camera_ip', 'camera_port', 'camera_off', 'audio_ip', 'audio_port', 'mic_off']),
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
     * Send combined critical threat SMS alert via Twilio.
     * Called when BOTH audio and video threats are simultaneously detected.
     * The recipient number can be provided dynamically by the admin UI (alert_number).
     */
    public function sendCombinedAlert(Request $request): JsonResponse
    {
        try {
            $audioThreat   = $request->input('audio_threat', []);
            $videoThreat   = $request->input('video_threat', []);
            $timestamp     = now()->format('Y-m-d H:i:s');
            $classroomName = $request->input('classroom_name', '');
            $gradeLevel    = $request->input('grade_level', '');
            $chatId = config('services.telegram.alert_chat_id');

            // Resolve human-readable audio type:
            // For non_speech threats the actual class is nested inside non_speech_result.detected_class
            $rawAudioType = $audioThreat['threat_type'] ?? 'Unknown';
            if ($rawAudioType === 'non_speech' && !empty($audioThreat['non_speech_result']['detected_class'])) {
                $audioType = ucwords(str_replace('_', ' ', $audioThreat['non_speech_result']['detected_class']));
            } elseif ($rawAudioType === 'speech' && !empty($audioThreat['speech_result']['detected_keywords'])) {
                $keywords  = collect($audioThreat['speech_result']['detected_keywords'])
                    ->map(fn($k) => is_array($k) ? ($k['keyword'] ?? '') : $k)
                    ->filter()->implode(', ');
                $audioType = $keywords ? "Speech ({$keywords})" : 'Speech Threat';
            } elseif ($rawAudioType === 'combined') {
                $nsClass   = $audioThreat['non_speech_result']['detected_class'] ?? '';
                $audioType = $nsClass ? ucwords(str_replace('_', ' ', $nsClass)) . ' + Speech' : 'Combined Threat';
            } else {
                $audioType = ucwords(str_replace('_', ' ', $rawAudioType));
            }

            $audioConf = round(($audioThreat['confidence'] ?? 0) * 100, 1);

            // Resolve human-readable video type
            $rawVideoType = $videoThreat['threat_type'] ?? 'Unknown';
            $videoType    = ucwords(str_replace('_', ' ', $rawVideoType));
            $videoConf    = round(($videoThreat['confidence'] ?? 0) * 100, 1);

            // Speech transcript (if available)
            $speechText = $audioThreat['speech_result']['text'] ?? null;

            // Build the alert message body
            $smsBody  = "⚠ CRITICAL SCHOOL THREAT ALERT ⚠\n";
            $smsBody .= "Time: {$timestamp}\n";
            if ($classroomName) {
                $locationLine = "Classroom: {$classroomName}";
                if ($gradeLevel) {
                    $locationLine .= " (Grade {$gradeLevel})";
                }
                $smsBody .= "{$locationLine}\n";
            }
            $smsBody .= "\n";
            $smsBody .= "AUDIO: {$audioType} ({$audioConf}%)\n";
            if ($speechText) {
                $smsBody .= "Transcript: \"{$speechText}\"\n";
            }
            $smsBody .= "VIDEO: {$videoType} ({$videoConf}%)\n\n";
            $smsBody .= "ACTION: Dispatch security immediately and review live footage.\n";
            $smsBody .= "— School Safety Monitoring System";

            // Send alert via Telegram Bot API using raw cURL (more reliable than Guzzle on Windows)
            $botToken = config('services.telegram.bot_token');
            $tgResult = $this->sendViaTelegram($botToken, $chatId, $smsBody);

            if (!$tgResult['ok']) {
                Log::error('AudioVideo: Telegram API rejected combined alert', [
                    'error' => $tgResult['error'],
                ]);
                return response()->json(['success' => false, 'error' => 'Telegram error: ' . $tgResult['error']], 500);
            }

            Log::critical('AudioVideo: COMBINED CRITICAL TELEGRAM ALERT sent', [
                'audio_threat' => $audioType,
                'video_threat' => $videoType,
                'classroom'    => $classroomName ?: 'N/A',
                'grade'        => $gradeLevel ?: 'N/A',
                'telegram_to'  => $chatId,
                'timestamp'    => $timestamp,
            ]);

            return response()->json(['success' => true, 'message' => 'Critical Telegram alert sent successfully.']);
        } catch (\Exception $e) {
            Log::error('AudioVideo: Failed to send combined Telegram alert: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a Telegram alert for a single object/threat that was detected
     * continuously for 10+ seconds on the frontend.
     * Each unique object key triggers exactly one message per session (enforced on frontend).
     */
    public function sendObjectAlert(Request $request): JsonResponse
    {
        try {
            $objectKey    = $request->input('object_key', 'unknown');
            $objectLabel  = $request->input('object_label', 'Unknown Object');
            $confidence   = $request->input('confidence');
            $classroomName = $request->input('classroom_name', '');
            $gradeLevel   = $request->input('grade_level', '');
            $timestamp    = now()->format('Y-m-d H:i:s');

            // Build the Telegram message
            $msg  = "⚠ PERSISTENT OBJECT ALERT ⚠\n";
            $msg .= "Time: {$timestamp}\n";
            if ($classroomName) {
                $locationLine = "Classroom: {$classroomName}";
                if ($gradeLevel) $locationLine .= " (Grade {$gradeLevel})";
                $msg .= "{$locationLine}\n";
            }
            $msg .= "\n";
            $msg .= "Object: {$objectLabel}\n";
            if ($confidence !== null) {
                $msg .= "Confidence: {$confidence}%\n";
            }
            $msg .= "Duration: Detected continuously for 10+ seconds\n\n";
            $msg .= "ACTION: Investigate this object immediately.\n";
            $msg .= "— School Safety Monitoring System";

            $botToken = config('services.telegram.bot_token');
            $chatId   = config('services.telegram.alert_chat_id');

            // Send via raw cURL (more reliable than Guzzle on Windows dev environments)
            $tgResult = $this->sendViaTelegram($botToken, $chatId, $msg);

            if (!$tgResult['ok']) {
                Log::error('AudioVideo: Telegram API rejected object alert', [
                    'error'  => $tgResult['error'],
                    'object' => $objectLabel,
                ]);
                return response()->json(['success' => false, 'error' => 'Telegram error: ' . $tgResult['error']], 500);
            }

            Log::warning('AudioVideo: Persistent object Telegram alert sent', [
                'object_key'   => $objectKey,
                'object_label' => $objectLabel,
                'classroom'    => $classroomName ?: 'N/A',
                'grade'        => $gradeLevel ?: 'N/A',
                'timestamp'    => $timestamp,
            ]);

            return response()->json(['success' => true, 'message' => "Persistent object alert sent for: {$objectLabel}"]);
        } catch (\Exception $e) {
            Log::error('AudioVideo: Failed to send persistent object alert: ' . $e->getMessage());
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

    /**
     * Send a Telegram message via raw cURL.
     * Uses cURL directly instead of Guzzle/Http because cURL is more reliable
     * on Windows dev environments (SSL verification disabled, explicit timeout).
     *
     * @return array{ok: bool, error: string}
     */
    private function sendViaTelegram(string $botToken, string $chatId, string $text): array
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['chat_id' => $chatId, 'text' => $text]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,   // required on Windows dev (no system CA bundle)
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        unset($ch);

        if ($curlError) {
            Log::error('AudioVideo: cURL Telegram send error', ['curl_error' => $curlError]);
            return ['ok' => false, 'error' => "cURL error: {$curlError}"];
        }

        $result = json_decode($response, true);

        if (empty($result['ok'])) {
            $desc = $result['description'] ?? $response;
            Log::error('AudioVideo: Telegram API error', ['response' => $result]);
            return ['ok' => false, 'error' => $desc];
        }

        return ['ok' => true, 'error' => ''];
    }
}
