<?php
// handlers/settings/export.php
// GET /api/settings/export?token=...
// Downloads CSV of all user expenses.
// Note: token is passed as ?token= because window.open() can't set headers.

$uid = requireAuth($conn);  // requireAuth() also checks $_GET['token']

// Override Content-Type set by cors.php
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="expenses_' . date('Y-m-d') . '.csv"');

$stmt = $conn->prepare(
    "SELECT e.date, e.description, c.name AS category, e.amount
     FROM expenses e
     JOIN categories c ON e.category_id = c.id
     WHERE e.user_id = ?
     ORDER BY e.date DESC"
);
$stmt->bind_param('i', $uid);
$stmt->execute();

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Description', 'Category', 'Amount']);

while ($row = $stmt->get_result()->fetch_assoc()) {
    fputcsv($out, [
        $row['date'],
        $row['description'],
        $row['category'],
        number_format((float)$row['amount'], 2, '.', ''),
    ]);
}

fclose($out);
exit;
