<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Repositories\AiScanRepository;
use Backend\Services\AiScanService;

class AiController extends Controller
{
    private AiScanRepository $repo;
    private AiScanService    $service;

    public function __construct(AiScanRepository $repo, AiScanService $service)
    {
        $this->repo    = $repo;
        $this->service = $service;
    }

    public function getQuota(int $userId): void
    {
        $used  = $this->repo->getQuota($userId);
        $limit = $this->service->getDailyLimit();

        $this->jsonSuccess([
            'used'      => $used,
            'limit'     => $limit,
            'remaining' => max(0, $limit - $used),
        ]);
    }

    public function scanFood(int $userId): void
    {
        $body      = $this->getRequestBody();
        $imageB64  = $body['image_b64'] ?? '';

        if (empty($imageB64)) {
            $this->jsonError('image_b64 required', 422);
        }

        // Parse & validate image URI
        try {
            ['raw_b64' => $rawB64, 'media_type' => $mediaType] = $this->service->parseImageUri($imageB64);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), 422);
        }

        // Check quota BEFORE processing (do not consume quota on validation failure)
        $limit = $this->service->getDailyLimit();
        if (!$this->repo->canScanToday($userId, $limit)) {
            $this->jsonError("Daily AI scan limit reached ({$limit}/day)", 429);
        }

        $groqKey      = getenv('GROQ_API_KEY')      ?: '';
        $geminiKey    = getenv('GEMINI_API_KEY')    ?: '';
        $anthropicKey = getenv('ANTHROPIC_API_KEY') ?: '';

        if (empty($groqKey) && empty($geminiKey) && empty($anthropicKey)) {
            $prediction = [
                'items' => [[
                    'name'            => 'Mock AI Food',
                    'estimated_grams' => 150,
                    'calories'        => 250,
                    'protein_g'       => 15,
                    'carbs_g'         => 20,
                    'fats_g'          => 5,
                    'confidence'      => 0.95,
                ]],
                'notes' => 'Mocked response due to missing API key',
            ];
            $text = json_encode($prediction);
        } else {
            $text = null;

            if (!empty($groqKey)) {
                try {
                    $text = $this->service->callGroq($rawB64, $mediaType, $groqKey);
                } catch (\RuntimeException $e) {
                    error_log('[ai_scan] Groq API error: ' . $e->getMessage());
                }
            }

            if ($text === null && !empty($geminiKey)) {
                try {
                    $text = $this->service->callGemini($rawB64, $mediaType, $geminiKey);
                } catch (\RuntimeException $e) {
                    error_log('[ai_scan] Gemini API error: ' . $e->getMessage());
                }
            }

            if ($text === null && !empty($anthropicKey)) {
                try {
                    $text = $this->service->callAnthropic($rawB64, $mediaType, $anthropicKey);
                } catch (\RuntimeException $e) {
                    error_log('[ai_scan] Anthropic API error: ' . $e->getMessage());
                }
            }

            if ($text === null) {
                $this->jsonError('AI service unavailable, please try again or log manually.', 502);
            }
        }

        $prediction = json_decode($text, true);
        if ($prediction === null) {
            $this->jsonError('AI could not parse the food image. Please log manually.', 422);
        }

        // Sanitize
        try {
            $prediction = $this->service->sanitizePrediction($prediction);
        } catch (\UnexpectedValueException $e) {
            $this->jsonError($e->getMessage(), 422);
        }

        // Increment quota atomically only after successful parse/sanitization
        if ($this->repo->incrementIfUnderLimit($userId, $limit) === false) {
            $this->jsonError("Daily AI scan limit reached ({$limit}/day)", 429);
        }

        $this->jsonSuccess($prediction);
    }
}
