<?php
$pageTitle = 'Edit Project';
require_once './includes/auth.php';
requireAuth(['coder']);  // Only coders can edit

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

$errors = [];
$success = '';

// Get project ID from URL
$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Fetch the project
$stmt = $db->prepare("
    SELECT * FROM projects 
    WHERE project_id = :project_id AND coder_id = :coder_id
");
$stmt->execute([
    'project_id' => $projectId,
    'coder_id' => $user['user_id']
]);
$project = $stmt->fetch(PDO::FETCH_OBJ);

// Check if project exists and belongs to this coder
if (!$project) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Get all clients for dropdown
$stmt = $db->prepare("
    SELECT user_id, name, email 
    FROM users 
    WHERE role_id = (SELECT role_id FROM roles WHERE role_name = 'client')
    ORDER BY name ASC
");
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_OBJ);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $client_id = $_POST['client_id'] ?? null;

    // Validation
    if (empty($title)) {
        $errors[] = 'Project title is required';
    }

    if (empty($description)) {
        $errors[] = 'Project description is required';
    }

    if (!isset($status) || $status === '') {
        $errors[] = 'Project status is required';
    }

    // Update project if no errors
    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE projects 
            SET title = :title, 
                description = :description, 
                status = :status, 
                client_id = :client_id
            WHERE project_id = :project_id 
            AND coder_id = :coder_id
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'client_id' => $client_id ?: null,
            'project_id' => $projectId,
            'coder_id' => $user['user_id']
        ]);

        $success = 'Project updated successfully!';

        // Refresh project data
        $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = :project_id");
        $stmt->execute(['project_id' => $projectId]);
        $project = $stmt->fetch(PDO::FETCH_OBJ);
    }
}

require_once './includes/header.php';
?>

<a href="<?= BASE_URL; ?>/projects/detail?id=<?= $projectId; ?>" class="back-link">← Back to Project</a>

<div class="card form-container">
    <h2>Edit Project</h2>
    <p style="color: #888; margin-bottom: 20px;">Project #
        <?= $projectId; ?>
    </p>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= htmlspecialchars($error); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?= htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Project Title *</label>
            <input type="text" id="title" name="title"
                value="<?= htmlspecialchars($_POST['title'] ?? $project->title); ?>">
        </div>

        <div class="form-group">
            <label for="description">Project Description *</label>
            <textarea id="description"
                name="description"><?= htmlspecialchars($_POST['description'] ?? $project->description); ?></textarea>
        </div>

        <div class="form-group">
            <label>Project Status *</label>
            <div class="status-options">
                <div class="status-option">
                    <input type="radio" id="status_pending" name="status" value="<?= STATUS_PENDING; ?>" <?php
                      $currentStatus = $_POST['status'] ?? $project->status;
                      echo ($currentStatus == STATUS_PENDING) ? 'checked' : ''; ?>>
                    <label for="status_pending">
                        <strong>Pending</strong>
                        <br><small>Not started</small>
                    </label>
                </div>

                <div class="status-option">
                    <input type="radio" id="status_progress" name="status" value="<?= STATUS_IN_PROGRESS; ?>"
                        <?= ($currentStatus == STATUS_IN_PROGRESS) ? 'checked' : ''; ?>>
                    <label for="status_progress">
                        <strong>In Progress</strong>
                        <br><small>Currently working</small>
                    </label>
                </div>

                <div class="status-option">
                    <input type="radio" id="status_completed" name="status" value="<?= STATUS_COMPLETED; ?>"
                        <?= ($currentStatus == STATUS_COMPLETED) ? 'checked' : ''; ?>>
                    <label for="status_completed">
                        <strong>Completed</strong>
                        <br><small>Finished</small>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="client_id">Assign to Client</label>
            <select id="client_id" name="client_id">
                <option value="">-- No client assigned --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client->user_id; ?>" <?php
                      $selectedClient = $_POST['client_id'] ?? $project->client_id;
                      echo ($selectedClient == $client->user_id) ? 'selected' : '';
                      ?>>
                        <?= htmlspecialchars($client->name); ?>
                        (<?= htmlspecialchars($client->email); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="<?= BASE_URL; ?>/projects/detail?id=<?= $projectId; ?>" class="btn">Cancel</a>
        </div>
    </form>

    <!-- Optional: Delete functionality -->
    <div class="danger-zone">
        <h3>⚠️ Danger Zone</h3>
        <p style="color: #888; margin-bottom: 15px;">
            Deleting a project is permanent and cannot be undone. All associated feedback will also be deleted.
        </p>
        <button
            onclick="if(confirm('Are you sure you want to delete this project? This cannot be undone!')) { window.location.href='<?= BASE_URL; ?>/projects/delete?id=<?= $projectId; ?>'; }"
            class="btn btn-danger">
            Delete Project
        </button>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>