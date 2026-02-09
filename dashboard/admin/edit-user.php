<?php
$pageTitle = 'Edit User';
require_once './includes/auth.php';
requireAuth(['admin']);

$user = getCurrentUser();

require_once './config/db.php';

$errors = [];
$success = '';

// Get user ID from URL
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: ' . BASE_URL . '/admin/users');
    exit;
}

// Get user details
$stmt = $db->prepare("
    SELECT users.*, roles.role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.role_id
    WHERE users.user_id = :user_id
");
$stmt->execute(['user_id' => $userId]);
$editUser = $stmt->fetch(PDO::FETCH_OBJ);

if (!$editUser) {
    header('Location: ' . BASE_URL . '/dashboard/admin/users');
    exit;
}

// Get all roles
$stmt = $db->query("SELECT * FROM roles ORDER BY role_name");
$roles = $stmt->fetchAll(PDO::FETCH_OBJ);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRoleId = $_POST['role_id'];

    if (empty($newRoleId)) {
        $errors[] = 'Please select a role';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE users SET role_id = :role_id WHERE user_id = :user_id");
        $stmt->execute([
            'role_id' => $newRoleId,
            'user_id' => $userId
        ]);

        $success = 'User role updated successfully!';

        // Refresh user data
        $stmt = $db->prepare("
            SELECT users.*, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.role_id
            WHERE users.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $editUser = $stmt->fetch(PDO::FETCH_OBJ);
    }
}

require_once './includes/header.php';
?>

<style>
    .form-container {
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #fff;
    }

    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #333;
        border-radius: 4px;
        background-color: #1a1a1a;
        color: white;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group select:focus {
        outline: none;
        border-color: #2196F3;
    }

    .user-info {
        background: #1a1a1a;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .user-info p {
        margin: 8px 0;
        color: #aaa;
    }

    .user-info strong {
        color: white;
    }
</style>

<a href="<?= BASE_URL; ?>/dashboard/admin/users" class="back-link">← Back to Users</a>

<div class="card form-container">
    <h2>Edit User Role</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?= htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="user-info">
        <p><strong>User ID:</strong> <?= $editUser->user_id; ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($editUser->name); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($editUser->email); ?></p>
        <p><strong>Current Role:</strong> <?= htmlspecialchars(ucfirst($editUser->role_name)); ?></p>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label for="role_id">Change Role To:</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role->role_id; ?>" <?= ($role->role_id == $editUser->role_id) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars(ucfirst($role->role_name)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Update Role</button>
            <a href="<?= BASE_URL; ?>/dashboard/admin/users" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require_once './includes/footer.php'; ?>