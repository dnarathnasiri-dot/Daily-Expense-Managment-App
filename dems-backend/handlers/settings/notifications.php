<?php
// handlers/settings/notifications.php
// PUT /api/settings/notifications
// Body: { email_notifications, daily_summary, budget_alerts }

$uid  = requireAuth($conn);
$body = getBody();

$emailNotif  = isset($body['email_notifications']) ? (int)(bool)$body['email_notifications'] : 0;
$dailySumm   = isset($body['daily_summary'])       ? (int)(bool)$body['daily_summary']       : 0;
$budgetAlert = isset($body['budget_alerts'])       ? (int)(bool)$body['budget_alerts']       : 0;

$upd = $conn->prepare(
    "UPDATE users
     SET email_notifications = ?, daily_summary = ?, budget_alerts = ?
     WHERE id = ?"
);
$upd->bind_param('iiii', $emailNotif, $dailySumm, $budgetAlert, $uid);
$upd->execute();

jsonOk([
    'success'             => true,
    'email_notifications' => (bool) $emailNotif,
    'daily_summary'       => (bool) $dailySumm,
    'budget_alerts'       => (bool) $budgetAlert,
]);
