<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

$userId  = requireAuth();
$db      = getDB();
$search  = trim($_GET['search']   ?? '');
$category= trim($_GET['category'] ?? '');
$from    = trim($_GET['from']     ?? '');
$to      = trim($_GET['to']       ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where = ['user_id = ?']; $types = 'i'; $params = [$userId];

if ($search)   { $where[] = '(description LIKE ? OR category LIKE ?)'; $types .= 'ss'; $like = "%$search%"; $params[] = $like; $params[] = $like; }
if ($category) { $where[] = 'category = ?'; $types .= 's'; $params[] = $category; }
if ($from)     { $where[] = 'date >= ?';    $types .= 's'; $params[] = $from; }
if ($to)       { $where[] = 'date <= ?';    $types .= 's'; $params[] = $to; }

$w = 'WHERE ' . implode(' AND ', $where);

$cs = $db->prepare("SELECT COUNT(*) AS cnt FROM expenses $w");
$cs->bind_param($types, ...$params);
$cs->execute();
$total = (int)$cs->get_result()->fetch_assoc()['cnt'];
$cs->close();

$types .= 'ii'; $params[] = $perPage; $params[] = $offset;
$stmt = $db->prepare("SELECT id,amount,date,description,category FROM expenses $w ORDER BY date DESC,id DESC LIMIT ? OFFSET ?");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$expenses = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int)$row['id']; $row['amount'] = (float)$row['amount'];
    $expenses[] = $row;
}
$stmt->close(); $db->close();

jsonOk(['data' => $expenses, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => (int)ceil($total / $perPage)]);