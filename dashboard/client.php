<?php
$pageTitle = 'Client Dashboard';
require_once './includes/auth.php';
requireAuth(['client']);

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

// Get client's projects with feedback count
$stmt = $db->prepare("
    SELECT projects.*, 
           coders.name as coder_name,
           coders.email as coder_email,
           (SELECT COUNT(*) FROM feedback WHERE feedback.project_id = projects.project_id AND feedback.client_id = :client_id) as feedback_count
    FROM projects
    LEFT JOIN users as coders ON projects.coder_id = coders.user_id
    WHERE projects.client_id = :client_id2
    ORDER BY projects.project_id DESC
");
$stmt->execute(['client_id' => $user['user_id'], 'client_id2' => $user['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_OBJ);

// Count projects by status
$pendingCount = 0;
$inProgressCount = 0;
$completedCount = 0;
$totalFeedback = 0;

foreach ($projects as $p) {
    if ($p->status == 0)
        $pendingCount++;
    if ($p->status == 1)
        $inProgressCount++;
    if ($p->status == 2)
        $completedCount++;
    $totalFeedback += $p->feedback_count;
}

require_once './includes/header.php';
?>

<div class="welcome-section">
    <h2>Welcome, <?= htmlspecialchars($user['email']); ?>! 👋</h2>
    <p>Track your projects and provide feedback to coders</p>
</div>

<div class="dashboard-grid">
    <div class="stat-card orange">
        <div class="stat-label">Pending</div>
        <div class="stat-number"><?= $pendingCount; ?></div>
        <div class="stat-sublabel">Waiting to start</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-label">Active</div>
        <div class="stat-number"><?= $inProgressCount; ?></div>
        <div class="stat-sublabel">In development</div>
    </div>

    <div class="stat-card green">
        <div class="stat-label">Completed</div>
        <div class="stat-number"><?= $completedCount; ?></div>
        <div class="stat-sublabel">Ready to review</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-label">Feedback</div>
        <div class="stat-number"><?= $totalFeedback; ?></div>
        <div class="stat-sublabel">Given</div>
    </div>
</div>

<div class="card">
    <h2>My Projects (<?= count($projects); ?>)</h2>

    <?php if (count($projects) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Coder</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($project->title); ?></strong>
                            <br>
                            <small
                                style="color: #888;"><?= htmlspecialchars(substr($project->description, 0, 50)); ?>...</small>
                        </td>
                        <td>
                            <?php if ($project->coder_name): ?>
                                <?= htmlspecialchars($project->coder_name); ?>
                                <br>
                                <small style="color: #888;"><?= htmlspecialchars($project->coder_email); ?></small>
                            <?php else: ?>
                                <span style="color: #888;">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="project-status" style="background-color: <?= $statusColors[$project->status]; ?>">
                                <?= $statusLabels[$project->status]; ?>
                            </span>
                        </td>
                        <td>
                            <span class="feedback-badge"><?= $project->feedback_count; ?> submitted</span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL; ?>/projects?id=<?= $project->project_id; ?>" class="btn">View</a>
                            <a href="<?= BASE_URL; ?>/feedback/create?project_id=<?= $project->project_id; ?>"
                                class="btn btn-success">Feedback</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3>No projects assigned to you yet</h3>
            <p>When a coder assigns you to a project, it will appear here</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once './includes/footer.php'; ?>