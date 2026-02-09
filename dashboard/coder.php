<?php
$pageTitle = 'Coder Dashboard';
require_once './includes/auth.php';
requireAuth(['coder']);

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

// Get coder's projects
$stmt = $db->prepare("
    SELECT projects.*, 
           clients.name as client_name,
           clients.email as client_email
    FROM projects
    LEFT JOIN users as clients ON projects.client_id = clients.user_id
    WHERE projects.coder_id = :coder_id
    ORDER BY projects.project_id DESC
");
$stmt->execute(['coder_id' => $user['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_OBJ);

// Count projects by status
$pendingCount = 0;
$inProgressCount = 0;
$completedCount = 0;
foreach ($projects as $p) {
    if ($p->status == 0)
        $pendingCount++;
    if ($p->status == 1)
        $inProgressCount++;
    if ($p->status == 2)
        $completedCount++;
}

require_once './includes/header.php';
?>

<div class="welcome-section">
    <h2>Welcome back,
        <?= htmlspecialchars($user['email']); ?>! 👋
    </h2>
    <p>Manage your projects and collaborate with clients</p>
    <div class="action-buttons">
        <a href="<?= BASE_URL; ?>/projects/create" class="btn btn-success btn-large">+ New Project</a>
        <a href="<?= BASE_URL; ?>/projects" class="btn btn-large">View All Projects</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card orange">
        <div class="stat-label">Pending</div>
        <div class="stat-number">
            <?= $pendingCount; ?>
        </div>
        <div>Projects waiting to start</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-label">In Progress</div>
        <div class="stat-number">
            <?= $inProgressCount; ?>
        </div>
        <div>Active projects</div>
    </div>

    <div class="stat-card green">
        <div class="stat-label">Completed</div>
        <div class="stat-number">
            <?= $completedCount; ?>
        </div>
        <div>Finished projects</div>
    </div>
</div>

<div class="card">
    <h2>Recent Projects</h2>

    <?php if (count($projects) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <strong>
                                <?= htmlspecialchars($project->title); ?>
                            </strong>
                            <br>
                            <small style="color: #888;">
                                <?= htmlspecialchars(substr($project->description, 0, 60)); ?>...
                            </small>
                        </td>
                        <td>
                            <?php if ($project->client_name): ?>
                                <?= htmlspecialchars($project->client_name); ?>
                                <br>
                                <small style="color: #888;">
                                    <?= htmlspecialchars($project->client_email); ?>
                                </small>

                            <?php else: ?>
                                <span style="color: #888;">No client assigned</span>
                            <?php endif; ?>

                        </td>
                        <td>
                            <span class="project-status" style="background-color: <?= $statusColors[$project->status]; ?>">
                                <?= $statusLabels[$project->status]; ?>
                            </span>
                        </td>
                        <td>


                            <a href="<?= BASE_URL; ?>/projects?id=<?= $project->project_id; ?>" class="btn">View</a>
                            <a href="<?= BASE_URL; ?>/projects/edit?id=<?= $project->project_id; ?>" class="btn">Edit</a>
                        </td>
                    </tr>


                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>No projects yet</h3>
            <p>Start by creating your first project to collaborate with clients</p>
            <a href="<?= BASE_URL; ?>/projects/create" class="btn btn-success btn-large">Create First Project</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once './includes/footer.php'; ?>