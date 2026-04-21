<?php

namespace Backend\Services;

class AiScanService
{
    private const DAILY_LIMIT = 20;
    private const GEMINI_URL  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private const GROQ_URL    = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL  = 'meta-llama/llama-4-scout-17b-16e-instruct';

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
     * Call Gemini Vision API and return raw JSON response text.
     * @throws \RuntimeException on HTTP/cURL failure
     */
    public function callGemini(string $rawB64, string $mediaType, string $apiKey): string
    {
        $payload = [
            'contents' => [[
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => $mediaType,
                            'data'      => $rawB64,
                        ],
                    ],
                    [
                        'text' => 'You are a food nutrition expert. Identify all visible food items in this photo and estimate their nutritional content. Return ONLY a JSON object with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0–1.0.',
                    ],
                ],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        $url = self::GEMINI_URL . '?key=' . urlencode($apiKey);
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ];
        $context  = \stream_context_create($options);
        $response = @\file_get_contents($url, false, $context);
        
        $httpCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            if (preg_match('#HTTP/\\d+\\.\\d+ (\\d+)#i', $http_response_header[0], $match)) {
                $httpCode = (int)$match[1];
            }
        }

        if ($response === false || $httpCode !== 200) {
            $errDetails = $response ?: 'No response body';
            throw new \RuntimeException('AI service error (code ' . $httpCode . '): ' . $errDetails);
        }

        $geminiData = json_decode($response, true);
        if (isset($geminiData['error'])) {
            throw new \RuntimeException('AI service error: ' . ($geminiData['error']['message'] ?? 'Unknown error'));
        }

        return $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    /**
     * Call Groq Vision API (OpenAI-compatible) and return raw JSON response text.
     * @throws \RuntimeException on HTTP/cURL failure
     */
    public function callGroq(string $rawB64, string $mediaType, string $apiKey): string
    {
        $prompt = 'Analyze this food photo. Return ONLY a JSON object with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0–1.0. No prose, no markdown.';

        $payload = [
            'model'    => self::GROQ_MODEL,
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'      => 'image_url',
                        'image_url' => ['url' => 'data:' . $mediaType . ';base64,' . $rawB64],
                    ],
                    ['type' => 'text', 'text' => $prompt],
                ],
            ]],
            'max_tokens'      => 1024,
            'temperature'     => 0.1,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = \curl_init(self::GROQ_URL);
        \curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => \json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);
        $response = \curl_exec($ch);
        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            $errDetails = $response ?: 'No response body';
            throw new \RuntimeException('Groq API error (code ' . $httpCode . '): ' . $errDetails);
        }

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \RuntimeException('Groq API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Anthropic Claude Vision API and return raw JSON response text.
     * @throws \RuntimeException on HTTP/cURL failure
     */
    public function callAnthropic(string $rawB64, string $mediaType, string $apiKey): string
    {
        $prompt = 'Analyze this food photo and return JSON with this exact schema: {"items":[{"name":"string","estimated_grams":number,"calories":number,"protein_g":number,"carbs_g":number,"fats_g":number,"confidence":number}],"notes":"string"}. confidence is 0.0–1.0. Return ONLY valid JSON.';

        $payload = [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'system'     => 'You are a food nutrition expert. When given a food photo, identify all visible food items and estimate their nutritional content. Always respond with valid JSON only — no prose, no markdown code fences.',
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'   => 'image',
                        'source' => [
                            'type'       => 'base64',
                            'media_type' => $mediaType,
                            'data'       => $rawB64,
                        ],
                    ],
                    ['type' => 'text', 'text' => $prompt],
                ],
            ]],
        ];

        $ch = \curl_init('https://api.anthropic.com/v1/messages');
        \curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => \json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);
        $response = \curl_exec($ch);
        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            $errDetails = $response ?: 'No response body';
            throw new \RuntimeException('Anthropic API error (code ' . $httpCode . '): ' . $errDetails);
        }

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \RuntimeException('Anthropic API error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['content'][0]['text'] ?? '';
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
