<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId      = requireAuth();
$body        = getBody();
$amount      = $body['amount']      ?? null;
$description = trim($body['description'] ?? '');
$category    = trim($body['category']    ?? 'Other');
$date        = trim($body['date']        ?? date('Y-m-d'));

if (!$amount || !is_numeric($amount) || (float)$amount <= 0) jsonError('Valid amount required.');
if (!$description) jsonError('Description is required.');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) jsonError('Date must be YYYY-MM-DD.');

$amount = round((float)$amount, 2);
$db     = getDB();
$stmt   = $db->prepare('INSERT INTO expenses (user_id,amount,description,category,date) VALUES (?,?,?,?,?)');
$stmt->bind_param('idsss', $userId, $amount, $description, $category, $date);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close(); $db->close();

jsonOk(['id' => $id, 'amount' => $amount, 'description' => $description, 'category' => $category, 'date' => $date], 201);