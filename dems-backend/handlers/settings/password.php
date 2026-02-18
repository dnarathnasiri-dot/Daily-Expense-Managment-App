<?php
// handlers/settings/password.php
// PUT /api/settings/password
// Body: { current_password, new_password, new_password_confirmation }

$uid  = requireAuth($conn);
$body = getBody();

$current = $body['current_password']          ?? '';
$new     = $body['new_password']              ?? '';
$confirm = $body['new_password_confirmation'] ?? '';

// Fetch current hash
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param('i', $uid);
$stmt->execute();
$hash = $stmt->get_result()->fetch_assoc()['password'];

if (!password_verify($current, $hash)) {
    jsonError('Current password is incorrect.', 422,
        ['current_password' => ['Current password is incorrect.']]);
}
if (strlen($new) < 6) {
    jsonError('New password must be at least 6 characters.', 422,
        ['new_password' => ['Must be at least 6 characters.']]);
}
if ($new !== $confirm) {
    jsonError('Passwords do not match.', 422,
        ['new_password_confirmation' => ['Passwords do not match.']]);
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$upd     = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$upd->bind_param('si', $newHash, $uid);
$upd->execute();

jsonOk(['success' => true, 'message' => 'Password updated successfully.']);
