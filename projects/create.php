<?php
$pageTitle = 'Create Project';
require_once './includes/auth.php';
requireAuth(['coder']);  // Only coders can create projects

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

$errors = [];
$success = '';

// Get all clients for the dropdown
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
    $imagePath = null;
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

    // Handle image upload (the design is very human)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // Basically means 5mb

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image type. Only JPG, PNG, and GIF are allowed.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Image too large. Maximum size is 5MB';
        } else {
            // Generate filename
            $extension = pathInfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'project_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadDir = 'uploads/projects/';
        }

        // Create director if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = $filename;
        } else {
            $errors[] = 'Failed to upload image';
        }
    }

    // Create project if no errors
    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO projects (title, description, image, status, coder_id, client_id)
            VALUES (:title, :description, :image, :status, :coder_id, :client_id)
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'image' => $imagePath,
            'status' => $status,
            'coder_id' => $user['user_id'],
            'client_id' => $client_id ?: null  // null if no client selected
        ]);

        $success = 'Project created successfully!';
        $newProjectId = $db->lastInsertId();

        // Redirect to the new project detail page after 2 seconds
        header("Refresh: 2; url=" . BASE_URL . "/projects/detail?id=" . $newProjectId);
    }
}

require_once './includes/header.php';
?>

<a href="<?= BASE_URL; ?>/projects" class="back-link">← Back to Projects</a>

<div class="card form-container">
    <h2 style="margin-bottom: 10px;">Create New Project</h2>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?= htmlspecialchars($success); ?>
            <br>Redirecting to project...
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Project Title *</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>"
                placeholder="e.g., Website Redesign, Mobile App Development">
            <small>A clear, descriptive title for the project</small>
        </div>

        <div class="form-group">
            <label for="description">Project Description *</label>
            <textarea id="description" name="description"
                placeholder="Describe the project scope, goals, and deliverables..."><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            <small>Provide detailed information about what needs to be done</small>
        </div>

        <div class="form-group">
            <label for="image">Upload an image (optional)</label>
            <input type="file" name="image" id="image" accept="image/*">
            <small>Supported file types: JPG, PNG, GIF (Max 5MB)</small>
        </div>

        <div class="form-group">
            <label>Project Status *</label>
            <div class="status-options">
                <div class="status-option">
                    <input type="radio" id="status_pending" name="status" value="<?= STATUS_PENDING; ?>"
                        <?= (isset($_POST['status']) && $_POST['status'] == STATUS_PENDING) ? 'checked' : ''; ?>>
                    <label for="status_pending">
                        <strong>Pending</strong>
                        <br><small>Not started</small>
                    </label>
                </div>

                <div class="status-option">
                    <input type="radio" id="status_progress" name="status" value="<?= STATUS_IN_PROGRESS; ?>"
                        <?= (isset($_POST['status']) && $_POST['status'] == STATUS_IN_PROGRESS) ? 'checked' : ''; ?>>
                    <label for="status_progress">
                        <strong>In Progress</strong>
                        <br><small>Currently working</small>
                    </label>
                </div>

                <div class="status-option">
                    <input type="radio" id="status_completed" name="status" value="<?= STATUS_COMPLETED; ?>"
                        <?= (isset($_POST['status']) && $_POST['status'] == STATUS_COMPLETED) ? 'checked' : ''; ?>>
                    <label for="status_completed">
                        <strong>Completed</strong>
                        <br><small>Finished</small>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="client_id">Assign to Client (Optional)</label>
            <select id="client_id" name="client_id">
                <option value="">-- No client assigned --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client->user_id; ?>" <?= (isset($_POST['client_id']) && $_POST['client_id'] == $client->user_id) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($client->name); ?>
                        (<?= htmlspecialchars($client->email); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <small>You can assign a client now or later</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Create Project</button>
            <a href="<?= BASE_URL; ?>/projects" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require_once './includes/footer.php'; ?>