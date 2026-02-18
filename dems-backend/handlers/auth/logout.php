<?php
// handlers/auth/logout.php
// POST /api/logout
// Deletes the current Bearer token from DB.

$token = getBearerToken();

if ($token) {
    $stmt = $conn->prepare(
        "DELETE FROM personal_access_tokens WHERE token = ?"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
}

jsonOk(['success' => true, 'message' => 'Logged out successfully.']);
