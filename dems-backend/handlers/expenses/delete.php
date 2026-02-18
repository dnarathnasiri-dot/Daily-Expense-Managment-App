<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId    = requireAuth();
$expenseId = EXPENSE_ID;
$db        = getDB();

$stmt = $db->prepare('DELETE FROM expenses WHERE id=? AND user_id=?');
$stmt->bind_param('ii', $expenseId, $userId);
$stmt->execute();

if ($stmt->affected_rows === 0) jsonError('Expense not found.', 404);
$stmt->close(); $db->close();

jsonOk(['message' => 'Expense deleted successfully.']);