<?php
// handlers/reports/category.php
// GET /api/reports/category
// Returns: [{ category: "Food", total: 0.00 }, ...]

$uid = requireAuth($conn);

$stmt = $conn->prepare(
    "SELECT c.name AS category, SUM(e.amount) AS total
     FROM expenses e
     JOIN categories c ON e.category_id = c.id
     WHERE e.user_id = ?
     GROUP BY c.id
     ORDER BY total DESC"
);
$stmt->bind_param('i', $uid);
$stmt->execute();

$result = [];
while ($row = $stmt->get_result()->fetch_assoc()) {
    $result[] = [
        'category' => $row['category'],
        'total'    => (float) $row['total'],
    ];
}

jsonOk($result);
