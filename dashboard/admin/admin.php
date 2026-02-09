<?php
$pageTitle = 'Admin Dashboard';
require_once './includes/auth.php';
requireAuth(['admin']);

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

// Get statistics
$userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_OBJ)->count;
$projectCount = $db->query("SELECT COUNT(*) as count FROM projects")->fetch(PDO::FETCH_OBJ)->count;
$feedbackCount = $db->query("SELECT COUNT(*) as count FROM feedback")->fetch(PDO::FETCH_OBJ)->count;

// Count users by role
$stmt = $db->query("
    SELECT r.role_name, COUNT(u.user_id) as count
    FROM roles r
    LEFT JOIN users u ON r.role_id = u.role_id
    GROUP BY r.role_id, r.role_name
");
$roleCounts = $stmt->fetchAll(PDO::FETCH_OBJ);

// Get recent users
$stmt = $db->query("
    SELECT users.*, roles.role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.role_id
    ORDER BY users.user_id DESC
    LIMIT 10
");
$recentUsers = $stmt->fetchAll(PDO::FETCH_OBJ);

// Get recent projects
$stmt = $db->query("
    SELECT projects.*,
           coders.name as coder_name,
           clients.name as client_name
    FROM projects
    LEFT JOIN users as coders ON projects.coder_id = coders.user_id
    LEFT JOIN users as clients ON projects.client_id = clients.user_id
    ORDER BY projects.project_id DESC
    LIMIT 5
");
$recentProjects = $stmt->fetchAll(PDO::FETCH_OBJ);

require_once './includes/header.php';
?>

<div class="welcome-section">
    <h2>⚡ Admin Control Panel</h2>
    <p>System overview and management • Logged in as <?= htmlspecialchars($user['email']); ?></p>
</div>

<div class="dashboard-grid">
    <div class="stat-card purple">
        <div class="stat-label">Total Users</div>
        <div class="stat-number"><?= $userCount; ?></div>
        <div class="stat-sublabel">Registered accounts</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-label">Projects</div>
        <div class="stat-number"><?= $projectCount; ?></div>
        <div class="stat-sublabel">Active & completed</div>
    </div>

    <div class="stat-card green">
        <div class="stat-label">Feedback</div>
        <div class="stat-number"><?= $feedbackCount; ?></div>
        <div class="stat-sublabel">Client submissions</div>
    </div>
</div>

<div class="card">
    <div class="section-header">
        <h2>Users by Role</h2>
    </div>

    <div class="mini-stats">
        <?php foreach ($roleCounts as $roleCount): ?>
            <div class="mini-stat">
                <div class="mini-stat-number"><?= $roleCount->count; ?></div>
                <div class="mini-stat-label"><?= htmlspecialchars($roleCount->role_name); ?>s</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div class="section-header">
        <h2>Recent Users</h2>
        <a href="<?= BASE_URL; ?>/dashboard/admin/users" class="btn">View All</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentUsers as $u): ?>
                <tr>
                    <td><?= $u->user_id; ?></td>
                    <td><?= htmlspecialchars($u->name); ?></td>
                    <td><?= htmlspecialchars($u->email); ?></td>
                    <td>
                        <span class="role-badge role-<?= $u->role_name; ?>">
                            <?= htmlspecialchars($u->role_name); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL; ?>/dashboard/admin/edit-user?id=<?= $u->user_id; ?>" class="btn">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div class="section-header">
        <h2>Recent Projects</h2>
        <a href="<?= BASE_URL; ?>/projects" class="btn">View All</a>
    </div>

    <?php if (count($recentProjects) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project</th>
                    <th>Coder</th>
                    <th>Client</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentProjects as $project): ?>
                    <tr>
                        <td><?= $project->project_id; ?></td>
                        <td>
                            <strong><?= htmlspecialchars($project->title); ?></strong>
                        </td>
                        <td><?= htmlspecialchars($project->coder_name ?? 'Unassigned'); ?></td>
                        <td><?= htmlspecialchars($project->client_name ?? 'Unassigned'); ?></td>
                        <td>
                            <span class="project-status" style="background-color: <?= $statusColors[$project->status]; ?>">
                                <?= $statusLabels[$project->status]; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #888; padding: 40px;">No projects created yet</p>
    <?php endif; ?>
</div>

<?php require_once './includes/footer.php'; ?>