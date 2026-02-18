<?php
// handlers/dashboard.php
// GET /api/dashboard
// Returns: { today_expenses, month_expenses, total_transactions,
//            avg_expense, by_category, recent }

$uid   = requireAuth($conn);
$today = date('Y-m-d');
$month = date('Y-m');

// ── Today's expenses ──
$r = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM expenses WHERE user_id = ? AND date = ?"
);
$r->bind_param('is', $uid, $today);
$r->execute();
$todayExp = (float) $r->get_result()->fetch_assoc()['total'];

// ── This month ──
$r = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM expenses WHERE user_id = ? AND DATE_FORMAT(date,'%Y-%m') = ?"
);
$r->bind_param('is', $uid, $month);
$r->execute();
$monthExp = (float) $r->get_result()->fetch_assoc()['total'];

// ── Totals ──
$r = $conn->prepare(
    "SELECT COUNT(*) AS cnt, COALESCE(AVG(amount), 0) AS avg
     FROM expenses WHERE user_id = ?"
);
$r->bind_param('i', $uid);
$r->execute();
$row   = $r->get_result()->fetch_assoc();
$txCnt = (int)   $row['cnt'];
$avgEx = (float) $row['avg'];

// ── Expenses by category (this month) ──
$r = $conn->prepare(
    "SELECT c.name AS category, SUM(e.amount) AS total
     FROM expenses e
     JOIN categories c ON e.category_id = c.id
     WHERE e.user_id = ? AND DATE_FORMAT(e.date,'%Y-%m') = ?
     GROUP BY c.id
     ORDER BY total DESC"
);
$r->bind_param('is', $uid, $month);
$r->execute();
$byCat = [];
while ($row = $r->get_result()->fetch_assoc()) {
    $byCat[] = [
        'category' => $row['category'],
        'total'    => (float) $row['total'],
    ];
}

// ── Recent transactions (last 5) ──
$r = $conn->prepare(
    "SELECT e.id, e.amount, e.date, e.description, c.name AS category
     FROM expenses e
     JOIN categories c ON e.category_id = c.id
     WHERE e.user_id = ?
     ORDER BY e.date DESC, e.id DESC
     LIMIT 5"
);
$r->bind_param('i', $uid);
$r->execute();
$recent = [];
while ($row = $r->get_result()->fetch_assoc()) {
    $recent[] = [
        'id'          => (int)   $row['id'],
        'amount'      => (float) $row['amount'],
        'date'        => $row['date'],
        'description' => $row['description'],
        'category'    => $row['category'],
    ];
}

jsonOk([
    'today_expenses'     => $todayExp,
    'month_expenses'     => $monthExp,
    'total_transactions' => $txCnt,
    'avg_expense'        => $avgEx,
    'by_category'        => $byCat,
    'recent'             => $recent,
]);
