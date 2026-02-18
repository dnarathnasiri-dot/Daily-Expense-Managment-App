<?php
// handlers/expenses/delete.php
// DELETE /api/expenses/{id}

$uid   = requireAuth($conn);
$expId = (int) ($routeParams['id'] ?? 0);

$stmt = $conn->prepare(
    "DELETE FROM expenses WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $expId, $uid);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    jsonError('Expense not found.', 404);
}

jsonOk(['success' => true, 'message' => 'Expense deleted.']);
