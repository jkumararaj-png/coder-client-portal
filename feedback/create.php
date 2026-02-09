<?php
$pageTitle = 'Submit Feedback';
require_once './includes/auth.php';

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';

$error = [];
$success = '';

// Get project ID from URL
$projectId = $_GET['project_id'] ?? null;

if (!$projectId) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Make sure the project is assigned to the respective client
$stmt = $db->prepare("
    SELECT project_id, title
    FROM projects
    WHERE project_id = :project_id AND client_id = :client_id
");

$stmt->execute([
    'project_id' => $projectId,
    'client_id' => $user['user_id']
]);

$project = $stmt->fetch(PDO::FETCH_OBJ);

// If project doesn't exist, head back to project view
if (!$project) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);

    // Validation
    if (empty($message)) {
        $errors[] = 'Feedback message is required';
    }

    // Save feedback if no errors
    if (empty($errors)) {
        $stmt = $db->prepare("
        INSERT INTO feedback (project_id, client_id, message)
        VALUES (:project_id, :client_id, :message)
        ");

        $stmt->execute([
            'project_id' => $projectId,
            'client_id' => $user['user_id'],
            'message' => $message
        ]);

        $success = 'Feedback submitted successfully!';

        // Redirect to project detail page after short load
        header("Refresh: 2; url=" . BASE_URL . "/projects/detail?id=" . $projectId);
    }
}

require_once './includes/header.php';
?>

<style>
    .form-container {
        max-width: 700px;
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

    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #333;
        border-radius: 4px;
        background-color: #1a1a1a;
        color: white;
        font-size: 14px;
        box-sizing: border-box;
        min-height: 150px;
        resize: vertical;
        font-family: Arial, sans-serif;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #2196F3;
    }
</style>

<a href="<?= BASE_URL; ?>/projects/detail?id=<?= $projectId; ?>" class="back-link">
    ← Back to Project
</a>

<div class="card form-container">
    <h2>Submit Feedback</h2>
    <p style="color: #888; margin-bottom: 30px;">
        Project: <strong>
            <?= htmlspecialchars($project->title); ?>
        </strong>
    </p>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul style="margin: 0; padding-left: 20px;">
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
            <br>Redirecting back to project...
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="message">Your Feedback</label>
            <textarea id="message" name="message"
                placeholder="Share your thoughts, suggestions, or concerns about this project..."
                required><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Submit Feedback</button>
            <a href="<?= BASE_URL; ?>/projects/detail?id=<?php $projectId; ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require_once './includes/footer.php'; ?>