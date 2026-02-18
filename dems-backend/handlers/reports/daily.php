<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId = requireAuth();
$days   = max(1, min(365, (int)($_GET['days'] ?? 7)));
$db     = getDB();

$stmt = $db->prepare("SELECT date, COALESCE(SUM(amount),0) AS total, COUNT(*) AS transactions FROM expenses WHERE user_id=? AND date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY date ORDER BY date ASC");
$stmt->bind_param('ii', $userId, $days);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = ['date' => $row['date'], 'total' => (float)$row['total'], 'transactions' => (int)$row['transactions']];
}
$stmt->close(); $db->close();

jsonOk(['days' => $days, 'data' => $data]);