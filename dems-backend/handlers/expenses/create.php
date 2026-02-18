<?php
// handlers/expenses/create.php
// POST /api/expenses
// Body: { amount, category, date, description }
// Returns: the created expense object

$uid  = requireAuth($conn);
$body = getBody();

$amount   = isset($body['amount']) ? (float) $body['amount'] : 0;
$category = trim($body['category']    ?? '');
$date     = trim($body['date']        ?? '');
$desc     = trim($body['description'] ?? '');

// Validate
$errors = [];
if ($amount <= 0)  $errors['amount'][]   = 'Amount must be greater than 0.';
if (!$category)    $errors['category'][] = 'Category is required.';
if (!$date)        $errors['date'][]     = 'Date is required.';

if ($errors) jsonError('Validation failed.', 422, $errors);

// Resolve category name → category_id
$cat = $conn->prepare("SELECT id FROM categories WHERE name = ?");
$cat->bind_param('s', $category);
$cat->execute();
$catRow = $cat->get_result()->fetch_assoc();

if (!$catRow) jsonError("Unknown category: $category", 422);
$catId = $catRow['id'];

// Insert
$ins = $conn->prepare(
    "INSERT INTO expenses (user_id, amount, category_id, date, description)
     VALUES (?, ?, ?, ?, ?)"
);
$ins->bind_param('idiss', $uid, $amount, $catId, $date, $desc);
$ins->execute();

$newId = $conn->insert_id;

jsonOk([
    'id'          => $newId,
    'amount'      => $amount,
    'category'    => $category,
    'date'        => $date,
    'description' => $desc,
], 201);
