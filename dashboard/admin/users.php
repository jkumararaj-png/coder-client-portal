<?php
$pageTitle = 'Manage Users';
require_once './includes/auth.php';
requireAuth(['admin']);

$user = getCurrentUser();

require_once './config/db.php';

// Get all users with their roles
$stmt = $db->query("
    SELECT users.*, roles.role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.role_id
    ORDER BY users.user_id DESC
");
$allUsers = $stmt->fetchAll(PDO::FETCH_OBJ);

require_once './includes/header.php';
?>

<a href="<?= BASE_URL; ?>/dashboard" class="back-link">← Back to Dashboard</a>

<div class="card">
    <h2>All Users (<?= count($allUsers); ?>)
    </h2>

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
            <?php foreach ($allUsers as $u): ?>
                <tr>
                    <td>
                        <?= $u->user_id; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($u->name); ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($u->email); ?>
                    </td>
                    <td>
                        <span class="role-badge role-<?= $u->role_name; ?>">
                            <?= htmlspecialchars($u->role_name); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL; ?>/dashboard/admin/edit-user?id=<?= $u->user_id; ?>" class="btn">Edit
                            User</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once './includes/footer.php'; ?>