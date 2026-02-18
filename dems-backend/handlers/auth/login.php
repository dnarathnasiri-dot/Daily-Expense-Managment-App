<?php
// handlers/auth/login.php
// POST /api/login
// Body: { email, password }
// Returns: { token, user }

$body  = getBody();
$email = trim($body['email'] ?? '');
$pass  = $body['password'] ?? '';

if (!$email || !$pass) {
    jsonError('Email and password are required.', 422);
}

// Fetch user
$stmt = $conn->prepare(
    "SELECT id, first_name, last_name, email, password,
            email_notifications, daily_summary, budget_alerts
     FROM users WHERE email = ?"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($pass, $user['password'])) {
    jsonError('Invalid email or password.', 401);
}

// Generate token
$token = bin2hex(random_bytes(40));
$stmt2 = $conn->prepare(
    "INSERT INTO personal_access_tokens (user_id, token) VALUES (?, ?)"
);
$stmt2->bind_param('is', $user['id'], $token);
$stmt2->execute();

// Return token + user (never return password)
unset($user['password']);
$user['id']                  = (int)$user['id'];
$user['email_notifications'] = (bool)$user['email_notifications'];
$user['daily_summary']       = (bool)$user['daily_summary'];
$user['budget_alerts']       = (bool)$user['budget_alerts'];

jsonOk(['token' => $token, 'user' => $user]);
