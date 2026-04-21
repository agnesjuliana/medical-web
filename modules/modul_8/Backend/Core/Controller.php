<?php

namespace Backend\Core;

class Controller
{
    /**
     * Send a successful JSON response
     */
    protected function jsonSuccess($data, int $code = 200): never
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['data' => $data]);
        if (defined('UNIT_TESTING')) {
            throw new \Exception('API_EXIT');
        }
        exit;
    }

    /**
     * Send an error JSON response
     */
    protected function jsonError(string $message, int $code = 400): never
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => $message]);
        if (defined('UNIT_TESTING')) {
            throw new \Exception('API_EXIT');
        }
        exit;
    }

    /**
     * Parse and validate JSON request body
     */
    protected function getRequestBody(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
