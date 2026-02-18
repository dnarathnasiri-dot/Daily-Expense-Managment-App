<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$headers    = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$token      = str_starts_with($authHeader, 'Bearer ') ? trim(substr($authHeader, 7)) : null;

if ($token) {
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM personal_access_tokens WHERE token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();
    $db->close();
}
