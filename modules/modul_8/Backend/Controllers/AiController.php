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

        // Read Anthropic Claude API key
        $apiKey = getenv('ANTHROPIC_API_KEY');
        if (empty($apiKey)) {
            $this->jsonError('AI service not configured', 503);
        }

        // Call Claude API
        try {
            $text = $this->service->callClaude($rawB64, $mediaType, $apiKey);
        } catch (\RuntimeException $e) {
            // Log detailed error server-side, return generic message to client
            error_log('[ai_scan] Claude API error: ' . $e->getMessage());
            $this->jsonError('AI service temporarily unavailable', 502);
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

        // Increment quota only after successful parse/sanitization
        $this->repo->incrementScanCount($userId);

        $this->jsonSuccess($prediction);
    }
}
