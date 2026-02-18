<?php
// handlers/reports/summary.php
// GET /api/reports/summary
// Returns: { total_expenses, avg_per_transaction, total_categories }

$uid = requireAuth($conn);

$r = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total,
            COALESCE(AVG(amount), 0) AS avg,
            COUNT(*) AS cnt
     FROM expenses WHERE user_id = ?"
);
$r->bind_param('i', $uid);
$r->execute();
$row = $r->get_result()->fetch_assoc();

$r2 = $conn->prepare(
    "SELECT COUNT(DISTINCT category_id) AS cats FROM expenses WHERE user_id = ?"
);
$r2->bind_param('i', $uid);
$r2->execute();
$cats = (int) $r2->get_result()->fetch_assoc()['cats'];

jsonOk([
    'total_expenses'      => (float) $row['total'],
    'avg_per_transaction' => (float) $row['avg'],
    'total_categories'    => $cats,
]);
