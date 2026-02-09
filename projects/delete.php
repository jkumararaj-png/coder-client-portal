<?php
require_once './includes/auth.php';
requireAuth(['coder']);

$user = getCurrentUser();

require_once './config/db.php';

$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Verify project belongs to this coder
$stmt = $db->prepare("
    SELECT project_id FROM projects 
    WHERE project_id = :project_id AND coder_id = :coder_id
");
$stmt->execute([
    'project_id' => $projectId,
    'coder_id' => $user['user_id']
]);

if ($stmt->rowCount() > 0) {
    // Delete associated feedback first (foreign key constraint)
    $stmt = $db->prepare("DELETE FROM feedback WHERE project_id = :project_id");
    $stmt->execute(['project_id' => $projectId]);

    // Delete the project
    $stmt = $db->prepare("DELETE FROM projects WHERE project_id = :project_id");
    $stmt->execute(['project_id' => $projectId]);
}

// Redirect to projects list
header('Location: ' . BASE_URL . '/projects');
exit;
?>