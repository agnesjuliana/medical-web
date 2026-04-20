<?php

namespace Backend\Services;

class AiScanService
{
    private const DAILY_LIMIT = 20;
    private const CLAUDE_URL = 'https://api.anthropic.com/v1/messages';

    public function getDailyLimit(): int
    {
        return self::DAILY_LIMIT;
    }

    /**
     * Parse and validate the raw base64 data URI.
     * Returns ['raw_b64' => string, 'media_type' => string] or throws.
     * Enforces max 2MB size and JPEG signature validation.
     */
    public function parseImageUri(string $imageB64): array
    {
        if (strpos($imageB64, 'data:image/') !== 0) {
            throw new \InvalidArgumentException('image_b64 must be a data:image/ URI');
        }

        $rawB64 = preg_replace('/^data:image\/[a-z]+(?:;[^,]+)*;base64,/', '', $imageB64);
        $binary = base64_decode($rawB64, true);
        if ($binary === false) {
            throw new \InvalidArgumentException('Invalid image encoding');
        }

        // Enforce max 2MB size limit
        if (strlen($binary) > 2 * 1024 * 1024) {
            throw new \InvalidArgumentException('Image exceeds 2MB limit');
        }

        // Enforce JPEG signature (magic bytes)
        if (strncmp($binary, "\xFF\xD8\xFF", 3) !== 0) {
            throw new \InvalidArgumentException('Only JPEG images are allowed');
        }

        if (!preg_match('/^data:image\/(jpeg|jpg)(?:;[^,]+)*;base64,/', $imageB64)) {
            throw new \InvalidArgumentException('Only JPEG images are allowed');
        }
        $mediaType = 'image/jpeg';

        return ['raw_b64' => $rawB64, 'media_type' => $mediaType];
    }

    /**
     * Call Anthropic Claude API with vision capabilities and return raw response text.
     * @throws \RuntimeException on HTTP/cURL failure
     */
    public function callClaude(string $rawB64, string $mediaType, string $apiKey): string
    {
        $payload = [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mediaType,
                                'data' => $rawB64,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => 'You are a food nutrition expert. When given a food photo, identify all visible food items and estimate their nutritional content. Always respond with valid JSON only — no prose, no markdown code fences. Respond with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0-1.0. Return ONLY valid JSON.',
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init(self::CLAUDE_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new \RuntimeException('AI service error, please try again or log manually');
        }

        $claudeData = json_decode($response, true);
        if (isset($claudeData['error'])) {
            throw new \RuntimeException('AI service error: ' . ($claudeData['error']['message'] ?? 'Unknown error'));
        }

        $text = $claudeData['content'][0]['text'] ?? '';

        // Strip markdown code fences as a safety net
        return preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $text);
    }

    /**
     * Validate and sanitize the parsed Gemini items array.
     */
    public function sanitizePrediction(array $prediction): array
    {
        if (!isset($prediction['items']) || !is_array($prediction['items'])) {
            throw new \UnexpectedValueException('AI could not parse the food image. Please log manually.');
        }

        $valid = [];
        foreach ($prediction['items'] as $item) {
            if (
                !isset($item['name'])       || !is_string($item['name'])  ||
                !isset($item['calories'])   || !is_numeric($item['calories']) ||
                !isset($item['protein_g'])  || !is_numeric($item['protein_g']) ||
                !isset($item['carbs_g'])    || !is_numeric($item['carbs_g']) ||
                !isset($item['fats_g'])     || !is_numeric($item['fats_g']) ||
                !isset($item['confidence']) || !is_numeric($item['confidence'])
            ) {
                continue;
            }
            $valid[] = [
                'name'            => $item['name'],
                'estimated_grams' => isset($item['estimated_grams']) && is_numeric($item['estimated_grams'])
                                     ? (float) $item['estimated_grams'] : null,
                'calories'        => (int)   $item['calories'],
                'protein_g'       => round((float) $item['protein_g'], 1),
                'carbs_g'         => round((float) $item['carbs_g'], 1),
                'fats_g'          => round((float) $item['fats_g'], 1),
                'confidence'      => round((float) $item['confidence'], 2),
            ];
        }

        $prediction['items'] = $valid;
        return $prediction;
    }
}
