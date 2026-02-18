<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$body      = getBody();
$firstName = trim($body['first_name'] ?? '');
$lastName  = trim($body['last_name']  ?? '');
$email     = trim($body['email']      ?? '');
$password  = $body['password']        ?? '';

if (!$firstName || !$lastName || !$email || !$password) jsonError('All fields are required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonError('Invalid email address.');
if (strlen($password) < 6) jsonError('Password must be at least 6 characters.');

$db   = getDB();
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) jsonError('Email already in use.', 409);
$stmt->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $db->prepare('INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $firstName, $lastName, $email, $hashed);
$stmt->execute();
$userId = $stmt->insert_id;
$stmt->close();

$token = generateToken();
$stmt  = $db->prepare('INSERT INTO personal_access_tokens (user_id, token) VALUES (?, ?)');
$stmt->bind_param('is', $userId, $token);
$stmt->execute();
$stmt->close();
$db->close();

jsonOk(['token' => $token, 'user' => [
    'id' => $userId, 'first_name' => $firstName, 'last_name' => $lastName,
    'email' => $email, 'email_notifications' => true, 'daily_summary' => false, 'budget_alerts' => true,
]], 201);