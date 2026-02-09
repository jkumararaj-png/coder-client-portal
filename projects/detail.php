<?php
$pageTitle = 'Project Details';
require_once './includes/auth.php';
requireAuth();

$user = getCurrentUser();

require_once './config/db.php';
require_once './includes/constants.php';


// Get project ID from URL
$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    header('Location: ' . BASE_URL . '/projects');
    exit;
}

// Fetch project details with coder and client info
$stmt = $db->prepare("
    SELECT projects.*, 
           coders.name as coder_name,
           coders.email as coder_email,
           clients.name as client_name,
           clients.email as client_email
    FROM projects
    LEFT JOIN users as coders ON projects.coder_id = coders.user_id
    LEFT JOIN users as clients ON projects.client_id = clients.user_id
    WHERE projects.project_id = :project_id
");
$stmt->execute(['project_id' => $projectId]);
$project = $stmt->fetch(PDO::FETCH_OBJ);

// Check if project exists
if (!$project) {
    echo "<div class='card'><p>Project not found.</p></div>";
    require_once './includes/footer.php';
    exit;
}

if ($user['role'] === 'admin') {
    echo "<div class='card'><p>Admins do not have access to project details for privacy reasons.</p></div>";
    require_once './includes/footer.php';
    exit;
}

// Check access permissions
if ($user['role'] === 'coder' && $project->coder_id != $user['user_id']) {
    echo "<div class='card'><p>You don't have permission to view this project.</p></div>";
    require_once './includes/footer.php';
    exit;
}

if ($user['role'] === 'client' && $project->client_id != $user['user_id']) {
    echo "<div class='card'><p>You don't have permission to view this project.</p></div>";
    require_once './includes/footer.php';
    exit;
}

// Fetch feedback for this project
$stmt = $db->prepare("
    SELECT feedback.*, users.name as client_name
    FROM feedback
    JOIN users ON feedback.client_id = users.user_id
    WHERE feedback.project_id = :project_id
    ORDER BY feedback.created_at DESC
");
$stmt->execute(['project_id' => $projectId]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_OBJ);

// GitHub embed card link generation
$ogImage = null;

if (!empty($project->github_link)) {
    $githubUrl = trim($project->github_link);
    $hash = bin2hex(random_bytes(10));

    $parsedUrl = parse_url($githubUrl);

    if (
        isset($parsedUrl['host'], $parsedUrl['path']) &&
        $parsedUrl['host'] === 'github.com'
    ) {
        $pathParts = array_values(array_filter(
            explode('/', $parsedUrl['path'])
        ));

        if (count($pathParts) >= 2) {
            $username = $pathParts[0];
            $repo = $pathParts[1];
            $ogImage = "https://opengraph.githubassets.com/$hash/$username/$repo";
        }
    }
}

require_once './includes/header.php';
?>

<script>
    function toggleFeedback() {
        const feedbackSection = document.getElementById('feedbackSection');
        feedbackSection.classList.toggle('active');
    }
</script>

<a href="<?= BASE_URL; ?>/projects" class="back-link">← Back to Projects</a>

<div class="project-details-container">
    <div class="card project-info-section">
        <div class="project-header">
            <div>
                <h1 class="project-title">
                    <?= htmlspecialchars($project->title); ?>
                </h1>
                <div class="project-id">Project #<?= $project->project_id; ?></div>
            </div>
            <span class="project-status" style="background-color: <?= $statusColors[$project->status]; ?>">
                <?= $statusLabels[$project->status]; ?>
            </span>
        </div>

        <div class="detail-section">
            <h3>Project Information</h3>

            <div class="detail-grid">
                <div class="detail-label">Description:</div>
                <div class="detail-value">
                    <?= htmlspecialchars($project->description); ?>
                </div>

                <?php if ($project->image): ?>
                    <div class="detail-label">Project Image:</div>
                    <div class="detail-value">
                        <img src="<?= BASE_URL; ?>/uploads/projects/<?= htmlspecialchars($project->image); ?>"
                            alt="<?= htmlspecialchars($project->title); ?>"
                            style="max-width: 100%; border-radius: 8px; border: 1px solid var(--color-border); margin-top: 10px;">
                    </div>
                <?php endif; ?>

                <?php if ($project->github_link): ?>
                    <div class="detail-label">GitHub Repository:</div>
                    <div class="detail-value">
                        <a href="<?= htmlspecialchars($project->github_link); ?>" target="_blank" class="github-link-btn">
                            View on GitHub ↗
                        </a>

                        <!-- GitHub Card Preview using opengraph.io -->
                        <div class="github-card">
                            <?php if ($ogImage): ?>
                                <img src="<?= htmlspecialchars($ogImage); ?>" alt="GitHub Repository Preview"
                                    style="width: 100%; border-radius: 8px; margin-top: 10px; border: 1px solid var(--color-border);">
                            <?php else: ?>
                                <p>Your GitHub link may be invalid.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <?= $statusLabels[$project->status]; ?>
                </div>

                <?php if ($user['role'] !== 'coder'): ?>
                    <div class="detail-label">Coder:</div>
                    <div class="detail-value">
                        <?php if ($project->coder_name): ?>
                            <?= htmlspecialchars($project->coder_name); ?>
                            <br>
                            <small style="color: #888;">
                                <?= htmlspecialchars($project->coder_email); ?>
                            </small>
                        <?php else: ?>
                            <span style="color: #888;">Not assigned</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($user['role'] !== 'client'): ?>
                    <div class="detail-label">Client:</div>
                    <div class="detail-value">
                        <?php if ($project->client_name): ?>
                            <?= htmlspecialchars($project->client_name); ?>
                            <br>
                            <small style="color: #888;">
                                <?= htmlspecialchars($project->client_email); ?>
                            </small>
                        <?php else: ?>
                            <span style="color: #888;">Not assigned</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <?php if ($user['role'] === 'coder'): ?>
                <a href="<?= BASE_URL; ?>/projects/edit?id=<?= $project->project_id; ?>" class="btn">Edit Project</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card feedback-section" id="feedbackSection">
        <h3 style="margin-bottom: 20px;">Feedback (<?= count($feedbacks); ?>)</h3>

        <div class="feedback-items">
            <?php if (count($feedbacks) > 0): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <strong>
                                <?= htmlspecialchars($feedback->client_name); ?>
                            </strong>
                            <span>
                                <?= date('M d, Y - g:i A', strtotime($feedback->created_at)); ?>
                                <?php if ($user['role'] === 'client' && $feedback->client_id === $user['user_id']): ?>
                                    <button
                                        onclick="if(confirm('Are you sure you want to delete this feedback?')) { window.location.href='<?= BASE_URL; ?>/feedback/delete?id=<?= $feedback->feedback_id; ?>&project_id=<?= $projectId; ?>'; }"
                                        class="btn btn-danger">
                                        🗑️
                                    </button>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="feedback-message">
                            <?= nl2br(htmlspecialchars($feedback->message)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-feedback">
                    No feedback yet for this project
                </div>
            <?php endif; ?>
        </div>

        <?php if ($user['role'] === 'client'): ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--color-border);">
                <a href="<?= BASE_URL; ?>/feedback/create?project_id=<?= $project->project_id; ?>" class="btn btn-success"
                    style="width: 100%; text-align: center;">
                    ✍️ Give Feedback
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Floating toggle button -->
<button class="toggle-feedback-btn" onclick="toggleFeedback()" title="Toggle Feedback">
    💬
    <?php if (count($feedbacks) > 0): ?>
        <span class="feedback-num"><?= count($feedbacks); ?></span>
    <?php endif; ?>
</button>

<?php require_once './includes/footer.php'; ?>