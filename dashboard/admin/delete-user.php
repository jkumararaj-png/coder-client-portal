<?php
require_once './includes/auth.php';
requireAuth(['admin']);

require_once './config/db.php';

$user = getCurrentUser();
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: ' . BASE_URL . '/dashboard/admin/users');
    exit;
}

// Prevent admin from deleting themselves
if ($userId == $user['user_id']) {
    header('Location: ' . BASE_URL . '/dashboard/admin/users');
    exit;
}

// Delete user
$stmt = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);

header('Location: ' . BASE_URL . '/dashboard/admin/users');
exit;
?>