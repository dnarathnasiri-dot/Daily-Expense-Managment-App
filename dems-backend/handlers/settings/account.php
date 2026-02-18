<?php
// handlers/settings/account.php
// DELETE /api/settings/account

$uid  = requireAuth($conn);

// Delete user — cascade deletes expenses + tokens via FK
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param('i', $uid);
$stmt->execute();

jsonOk(['success' => true, 'message' => 'Account deleted.']);
