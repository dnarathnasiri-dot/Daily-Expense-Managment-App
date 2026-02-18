<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId     = requireAuth();
$expenseId  = EXPENSE_ID;
$body       = getBody();
$amount     = $body['amount']      ?? null;
$description= trim($body['description'] ?? '');
$category   = trim($body['category']    ?? 'Other');
$date       = trim($body['date']        ?? '');

if (!$amount || !is_numeric($amount) || (float)$amount <= 0) jsonError('Valid amount required.');
if (!$description) jsonError('Description is required.');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) jsonError('Date must be YYYY-MM-DD.');

$amount = round((float)$amount, 2);
$db     = getDB();

$check = $db->prepare('SELECT id FROM expenses WHERE id=? AND user_id=?');
$check->bind_param('ii', $expenseId, $userId);
$check->execute();
if ($check->get_result()->num_rows === 0) jsonError('Expense not found.', 404);
$check->close();

$stmt = $db->prepare('UPDATE expenses SET amount=?,description=?,category=?,date=? WHERE id=? AND user_id=?');
$stmt->bind_param('dsssii', $amount, $description, $category, $date, $expenseId, $userId);
$stmt->execute();
$stmt->close(); $db->close();

jsonOk(['id' => $expenseId, 'amount' => $amount, 'description' => $description, 'category' => $category, 'date' => $date]);