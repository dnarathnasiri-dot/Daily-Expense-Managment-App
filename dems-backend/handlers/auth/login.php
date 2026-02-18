<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$body  = getBody();
$email = trim($body['email'] ?? '');
$pass  = $body['password'] ?? '';

if (!$email || !$pass) jsonError('Email and password are required.');

$db   = getDB();
$stmt = $db->prepare('SELECT id, first_name, last_name, email, password, email_notifications, daily_summary, budget_alerts FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) jsonError('Invalid email or password.', 401);

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($pass, $user['password'])) jsonError('Invalid email or password.', 401);

$token = generateToken();
$stmt  = $db->prepare('INSERT INTO personal_access_tokens (user_id, token) VALUES (?, ?)');
$stmt->bind_param('is', $user['id'], $token);
$stmt->execute();
$stmt->close();
$db->close();

unset($user['password']);
$user['id']                  = (int)  $user['id'];
$user['email_notifications'] = (bool) $user['email_notifications'];
$user['daily_summary']       = (bool) $user['daily_summary'];
$user['budget_alerts']       = (bool) $user['budget_alerts'];

jsonOk(['token' => $token, 'user' => $user]);