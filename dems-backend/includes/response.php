<?php
// includes/response.php

function jsonOk(mixed $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $code = 400, array $errors = []): void {
    http_response_code($code);
    $body = ['success' => false, 'message' => $message];
    if ($errors) $body['errors'] = $errors;
    echo json_encode($body);
    exit;
}

function getBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}
