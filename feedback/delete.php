<?php
require_once './includes/auth.php';
requireAuth(['client']);

$user = getCurrentUser();

require_once './config/db.php';

$feedbackId = $_GET['id'] ?? null;
$projectId = $_GET['project_id'] ?? null;

if (!$feedbackId || !$projectId) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Make sure the feedback belongs to the respective client
$stmt = $db->prepare("
    SELECT feedback_id
    FROM feedback
    WHERE feedback_id = :feedback_id
    AND client_id = :client_id
");

$stmt->execute([
    'feedback_id' => $feedbackId,
    'client_id' => $user['user_id']
]);

if ($stmt->rowCount() > 0) {
    // If it belongs to the client, then delete it
    $stmt = $db->prepare("DELETE FROM feedback WHERE feedback_id = :feedback_id");
    $stmt->execute(['feedback_id' => $feedbackId]);
}

// Redirect back to project details
header('Location: ' . BASE_URL . '/projects/detail?id=' . $projectId);
exit;
?>