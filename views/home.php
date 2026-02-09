<?php
$pageTitle = "Home";
require_once './includes/auth.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    redirectToDashboard();
    exit;
}

require_once './config/db.php';

require_once './includes/header.php';
?>

<div class="hero" style="text-align: center;">
    <h1 style="font-size: 3rem; line-height: 1; margin-bottom: 30px;">Coder-Client Communication Portal</h1>
    <p>A workspace to streamline communication between coders and clients.
    </p>

    <div class="action-buttons">
        <a href="<?= BASE_URL; ?>/login" class="btn btn-success">Login</a>
        <a href="<?= BASE_URL; ?>/signup" class="btn">Sign Up</a>
    </div>
</div>

<?php require_once './includes/footer.php';