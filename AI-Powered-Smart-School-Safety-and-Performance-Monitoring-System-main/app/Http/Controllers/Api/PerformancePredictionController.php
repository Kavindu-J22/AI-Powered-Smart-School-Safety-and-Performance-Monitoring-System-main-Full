<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerformancePredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformancePredictionController extends Controller
{
    protected PerformancePredictionService $predictionService;

    public function __construct(PerformancePredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Get performance prediction for a student
     * GET /api/students/{studentId}/prediction
     */
    public function getPrediction($studentId): JsonResponse
    {
        try {
            // Get prediction from service
            $prediction = $this->predictionService->getPrediction($studentId);

            // Propagate no_data status with 200 so the frontend can show a friendly message
            if (isset($prediction['status']) && $prediction['status'] === 'no_data') {
                return response()->json($prediction);
            }

            // Format for display
            $formatted = $this->predictionService->formatPredictionForDisplay($prediction);

            if (isset($formatted['error'])) {
                return response()->json($formatted, 400);
            }

            return response()->json([
                'status' => 'success',
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if prediction API is available
     * GET /api/prediction/health
     */
    public function health(): JsonResponse
    {
        try {
            $service = new PerformancePredictionService();
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('http://127.0.0.1:5002/health');

            if ($response->successful()) {
                return response()->json([
                    'status' => 'healthy',
                    'api_status' => 'connected',
                    'service' => 'Performance Prediction API'
                ]);
            }

            return response()->json([
                'status' => 'unhealthy',
                'api_status' => 'disconnected'
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'api_status' => 'unreachable',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get batch predictions for multiple students
     * POST /api/students/predictions
     */
    public function batchPredictions(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer'
        ]);

        $predictions = [];
        foreach ($request->student_ids as $studentId) {
            $prediction = $this->predictionService->getPrediction($studentId);
            $formatted = $this->predictionService->formatPredictionForDisplay($prediction);

            if (!isset($formatted['error'])) {
                $predictions[] = $formatted;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $predictions,
            'count' => count($predictions)
        ]);
    }
}
