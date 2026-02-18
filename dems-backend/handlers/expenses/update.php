<?php
// handlers/expenses/update.php
// PUT /api/expenses/{id}
// Body: { amount, category, date, description }
// Returns: updated expense object

$uid    = requireAuth($conn);
$expId  = (int) ($routeParams['id'] ?? 0);
$body   = getBody();

// Verify ownership
$chk = $conn->prepare(
    "SELECT id FROM expenses WHERE id = ? AND user_id = ?"
);
$chk->bind_param('ii', $expId, $uid);
$chk->execute();
if (!$chk->get_result()->fetch_assoc()) {
    jsonError('Expense not found.', 404);
}

$amount   = isset($body['amount']) ? (float) $body['amount'] : 0;
$category = trim($body['category']    ?? '');
$date     = trim($body['date']        ?? '');
$desc     = trim($body['description'] ?? '');

$errors = [];
if ($amount <= 0) $errors['amount'][]   = 'Amount must be greater than 0.';
if (!$category)   $errors['category'][] = 'Category is required.';
if (!$date)       $errors['date'][]     = 'Date is required.';
if ($errors) jsonError('Validation failed.', 422, $errors);

// Resolve category
$cat = $conn->prepare("SELECT id FROM categories WHERE name = ?");
$cat->bind_param('s', $category);
$cat->execute();
$catRow = $cat->get_result()->fetch_assoc();
if (!$catRow) jsonError("Unknown category: $category", 422);
$catId = $catRow['id'];

// Update
$upd = $conn->prepare(
    "UPDATE expenses SET amount = ?, category_id = ?, date = ?, description = ?
     WHERE id = ? AND user_id = ?"
);
$upd->bind_param('dissii', $amount, $catId, $date, $desc, $expId, $uid);
$upd->execute();

jsonOk([
    'id'          => $expId,
    'amount'      => $amount,
    'category'    => $category,
    'date'        => $date,
    'description' => $desc,
]);
