<?php
// includes/auth.php
// Call requireAuth() at the top of any protected handler.
// Sets $currentUserId in the calling scope.

function getBearerToken(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION']
           ?? apache_request_headers()['Authorization']
           ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        return $m[1];
    }
    // Also allow ?token= for CSV export (opened in new tab)
    return $_GET['token'] ?? null;
}

function requireAuth(mysqli $conn): int {
    $token = getBearerToken();

    if (!$token) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized. No token provided.']);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT user_id FROM personal_access_tokens WHERE token = ?"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized. Invalid or expired token.']);
        exit;
    }

    return (int) $row['user_id'];
}
