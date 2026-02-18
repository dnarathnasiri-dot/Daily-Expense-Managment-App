<?php
// handlers/expenses/list.php
// GET /api/expenses?search=
// Returns: array of expense objects

$uid    = requireAuth($conn);
$search = trim($_GET['search'] ?? '');

if ($search) {
    $like = "%$search%";
    $stmt = $conn->prepare(
        "SELECT e.id, e.amount, e.date, e.description, c.name AS category
         FROM expenses e
         JOIN categories c ON e.category_id = c.id
         WHERE e.user_id = ?
           AND (e.description LIKE ? OR c.name LIKE ?)
         ORDER BY e.date DESC, e.id DESC"
    );
    $stmt->bind_param('iss', $uid, $like, $like);
} else {
    $stmt = $conn->prepare(
        "SELECT e.id, e.amount, e.date, e.description, c.name AS category
         FROM expenses e
         JOIN categories c ON e.category_id = c.id
         WHERE e.user_id = ?
         ORDER BY e.date DESC, e.id DESC"
    );
    $stmt->bind_param('i', $uid);
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$expenses = array_map(fn($r) => [
    'id'          => (int)   $r['id'],
    'amount'      => (float) $r['amount'],
    'date'        => $r['date'],
    'description' => $r['description'],
    'category'    => $r['category'],
], $rows);

jsonOk($expenses);
