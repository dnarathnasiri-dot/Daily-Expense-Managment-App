<?php
// handlers/reports/daily.php
// GET /api/reports/daily?days=7
// Returns: [{ day: "YYYY-MM-DD", total: 0.00 }, ...]
// Fills in zeros for days with no expenses.

$uid  = requireAuth($conn);
$days = max(1, min(90, (int) ($_GET['days'] ?? 7)));

// Generate date range
$dateRange = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $d             = date('Y-m-d', strtotime("-$i days"));
    $dateRange[$d] = 0.0;
}

// Query actual data
$start = array_key_first($dateRange);
$end   = array_key_last($dateRange);

$stmt = $conn->prepare(
    "SELECT date, SUM(amount) AS total
     FROM expenses
     WHERE user_id = ? AND date BETWEEN ? AND ?
     GROUP BY date"
);
$stmt->bind_param('iss', $uid, $start, $end);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $dateRange[$row['date']] = (float) $row['total'];
}

$result = [];
foreach ($dateRange as $day => $total) {
    $result[] = ['day' => $day, 'total' => $total];
}

jsonOk($result);
