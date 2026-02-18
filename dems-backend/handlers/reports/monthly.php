<?php
// handlers/reports/monthly.php
// GET /api/reports/monthly
// Returns: [{ month: "YYYY-MM", total: 0.00 }, ...]  (most recent first)

$uid = requireAuth($conn);

$stmt = $conn->prepare(
    "SELECT DATE_FORMAT(date, '%Y-%m') AS month,
            SUM(amount) AS total
     FROM expenses
     WHERE user_id = ?
     GROUP BY month
     ORDER BY month DESC
     LIMIT 12"
);
$stmt->bind_param('i', $uid);
$stmt->execute();

$result = [];
while ($row = $stmt->get_result()->fetch_assoc()) {
    $result[] = [
        'month' => $row['month'],
        'total' => (float) $row['total'],
    ];
}

jsonOk($result);
