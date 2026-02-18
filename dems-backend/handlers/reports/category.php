<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId = requireAuth();
$month  = $_GET['month'] ?? date('Y-m');
$db     = getDB();

$stmt = $db->prepare("SELECT category, COALESCE(SUM(amount),0) AS total, COUNT(*) AS transactions FROM expenses WHERE user_id=? AND DATE_FORMAT(date,'%Y-%m')=? GROUP BY category ORDER BY total DESC");
$stmt->bind_param('is', $userId, $month);
$stmt->execute();
$result = $stmt->get_result();
$data = []; $grandTotal = 0;
while ($row = $result->fetch_assoc()) {
    $grandTotal += (float)$row['total'];
    $data[] = ['category' => $row['category'], 'total' => (float)$row['total'], 'transactions' => (int)$row['transactions']];
}
$stmt->close(); $db->close();

foreach ($data as &$row) {
    $row['percentage'] = $grandTotal > 0 ? round(($row['total'] / $grandTotal) * 100, 1) : 0;
}

jsonOk(['month' => $month, 'grand_total' => $grandTotal, 'data' => $data]);
