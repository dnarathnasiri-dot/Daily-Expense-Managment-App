<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId = requireAuth();
$months = max(1, min(24, (int)($_GET['months'] ?? 6)));
$db     = getDB();

$stmt = $db->prepare("SELECT DATE_FORMAT(date,'%Y-%m') AS month, COALESCE(SUM(amount),0) AS total, COUNT(*) AS transactions FROM expenses WHERE user_id=? AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) GROUP BY DATE_FORMAT(date,'%Y-%m') ORDER BY month ASC");
$stmt->bind_param('ii', $userId, $months);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = ['month' => $row['month'], 'total' => (float)$row['total'], 'transactions' => (int)$row['transactions']];
}
$stmt->close(); $db->close();

jsonOk(['months' => $months, 'data' => $data]);