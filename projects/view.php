<?php
$pageTitle = 'Projects';
require_once './includes/auth.php';
requireAuth();

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

// Build query based on role
if ($user['role'] === 'admin') {
    $stmt = $db->query("
        SELECT projects.project_id,
               projects.title,
               projects.description,
               projects.status,
               projects.coder_id,
               projects.client_id,
               coders.name as coder_name,
               clients.name as client_name,
               COUNT(feedback.feedback_id) as feedback_count
        FROM projects
        LEFT JOIN users as coders ON projects.coder_id = coders.user_id
        LEFT JOIN users as clients ON projects.client_id = clients.user_id
        LEFT JOIN feedback ON feedback.project_id = projects.project_id
        GROUP BY projects.project_id, 
                 projects.title, 
                 projects.description, 
                 projects.status, 
                 projects.coder_id, 
                 projects.client_id,
                 coders.name,
                 clients.name
        ORDER BY projects.project_id DESC
    ");

} elseif ($user['role'] === 'coder') {
    $stmt = $db->prepare("
        SELECT projects.project_id,
               projects.title,
               projects.description,
               projects.status,
               projects.coder_id,
               projects.client_id,
               clients.name as client_name,
               COUNT(feedback.feedback_id) as feedback_count
        FROM projects
        LEFT JOIN users as clients ON projects.client_id = clients.user_id
        LEFT JOIN feedback ON feedback.project_id = projects.project_id
        WHERE projects.coder_id = :coder_id
        GROUP BY projects.project_id, 
                 projects.title, 
                 projects.description, 
                 projects.status, 
                 projects.coder_id, 
                 projects.client_id,
                 clients.name
        ORDER BY projects.project_id DESC
    ");
    $stmt->execute(['coder_id' => $user['user_id']]);

} else {
    $stmt = $db->prepare("
        SELECT projects.project_id,
               projects.title,
               projects.description,
               projects.status,
               projects.coder_id,
               projects.client_id,
               coders.name as coder_name,
               COUNT(feedback.feedback_id) as feedback_count
        FROM projects
        LEFT JOIN users as coders ON projects.coder_id = coders.user_id
        LEFT JOIN feedback ON feedback.project_id = projects.project_id
        WHERE projects.client_id = :client_id
        GROUP BY projects.project_id, 
                 projects.title, 
                 projects.description, 
                 projects.status, 
                 projects.coder_id, 
                 projects.client_id,
                 coders.name
        ORDER BY projects.project_id DESC
    ");
    $stmt->execute(['client_id' => $user['user_id']]);
}

$projects = $stmt->fetchAll(PDO::FETCH_OBJ);

require_once './includes/header.php';
?>

<a href="<?= BASE_URL; ?>/dashboard" class="back-link">← Back to Dashboard</a>

<div class="page-header">
    <h2>
        <?php if ($user['role'] === 'admin'): ?>
            All Projects
        <?php elseif ($user['role'] === 'coder'): ?>
            My Projects
        <?php else: ?>
            My Projects
        <?php endif; ?>
    </h2>

    <?php if ($user['role'] === 'coder'): ?>
        <a href="<?= BASE_URL; ?>/projects/create" class="btn btn-success">+ New Project</a>
    <?php endif; ?>
</div>

<?php if (count($projects) > 0): ?>
    <?php foreach ($projects as $project): ?>
        <div class="project-card">
            <div class="project-header">
                <div>
                    <h3 class="project-title"><?= htmlspecialchars($project->title); ?></h3>
                    <div class="project-id">Project #<?= $project->project_id; ?></div>
                </div>
                <span class="project-status" style="background-color: <?= $statusColors[$project->status]; ?>">
                    <?= $statusLabels[$project->status]; ?>
                </span>
            </div>

            <p class="project-description">
                <?= htmlspecialchars($project->description); ?>
            </p>

            <div class="project-meta">
                <?php if ($user['role'] !== 'coder'): ?>
                    <div class="meta-item">
                        <strong>Coder:</strong>
                        <?= htmlspecialchars($project->coder_name ?? 'Not assigned'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($user['role'] !== 'client'): ?>
                    <div class="meta-item">
                        <strong>Client:</strong>
                        <?= htmlspecialchars($project->client_name ?? 'Not assigned'); ?>
                    </div>
                <?php endif; ?>
                <div class="meta-item">
                    <strong>Feedback:</strong>
                    <?= $project->feedback_count; ?>
                </div>
            </div>

            <div class="project-actions">
                <a href="<?= BASE_URL; ?>/projects/detail?id=<?= $project->project_id; ?>" class="btn">View
                    Details</a>

                <?php if ($user['role'] === 'coder'): ?>
                    <a href="<?= BASE_URL; ?>/projects/edit?id=<?= $project->project_id; ?>" class="btn">Edit</a>
                <?php endif; ?>

                <?php if ($user['role'] === 'client'): ?>
                    <a href="<?= BASE_URL; ?>/feedback/create?project_id=<?= $project->project_id; ?>" class="btn btn-success">Give
                        Feedback</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">📂</div>
        <h3>No projects yet</h3>

        <?php if ($user['role'] === 'admin'): ?>
            <p>No projects have been created in the system</p>
        <?php elseif ($user['role'] === 'coder'): ?>
            <p>You haven't created any projects yet</p>
            <a href="<?= BASE_URL; ?>/projects/create" class="btn btn-success btn-large">Create Your First Project</a>
        <?php else: ?>
            <p>No projects have been assigned to you yet</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once './includes/footer.php'; ?>