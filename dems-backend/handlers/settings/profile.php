<?php
// handlers/settings/profile.php
// PUT /api/settings/profile
// Body: { first_name, last_name, email }
// Returns: { user }

$uid  = requireAuth($conn);
$body = getBody();

$first = trim($body['first_name'] ?? '');
$last  = trim($body['last_name']  ?? '');
$email = trim($body['email']      ?? '');

$errors = [];
if (!$first)                                    $errors['first_name'][] = 'First name is required.';
if (!$last)                                     $errors['last_name'][]  = 'Last name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'][]      = 'A valid email is required.';
if ($errors) jsonError('Validation failed.', 422, $errors);

// Check email uniqueness (exclude self)
$chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$chk->bind_param('si', $email, $uid);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    jsonError('That email is already in use.', 409, ['email' => ['Email already taken.']]);
}

$upd = $conn->prepare(
    "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?"
);
$upd->bind_param('sssi', $first, $last, $email, $uid);
$upd->execute();

// Return updated user (with current notification prefs)
$stmt = $conn->prepare(
    "SELECT id, first_name, last_name, email,
            email_notifications, daily_summary, budget_alerts
     FROM users WHERE id = ?"
);
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$user['id']                  = (int)   $user['id'];
$user['email_notifications'] = (bool)  $user['email_notifications'];
$user['daily_summary']       = (bool)  $user['daily_summary'];
$user['budget_alerts']       = (bool)  $user['budget_alerts'];

jsonOk(['success' => true, 'user' => $user]);
