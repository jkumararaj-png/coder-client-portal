<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle; ?> - Coder-Client Communication Portal</title>

    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/style.css">

    <script>
        function toggleProfileMenu() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }

        function toggleMobileMenu() {
            document.getElementById('mobileNav').classList.toggle('show');
        }

        window.onclick = function (event) {
            // Close profile dropdown
            if (!event.target.matches('.profile-button') && !event.target.closest('.profile-button')) {
                var dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }

            // Close mobile menu when clicking outside
            if (!event.target.matches('.hamburger') && !event.target.closest('.nav')) {
                var mobileNav = document.getElementById('mobileNav');
                if (mobileNav && mobileNav.classList.contains('show')) {
                    mobileNav.classList.remove('show');
                }
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="<?= BASE_URL ?>">
                <h1>Coder-Client Communication Portal</h1>
            </a>

            <!-- Hamburger button (only shows on mobile) -->
            <button class="hamburger" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Desktop + Mobile Navigation -->
            <nav class="nav" id="mobileNav">
                <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
                    <a href="<?= BASE_URL; ?>/dashboard">Dashboard</a>
                    <?php if ($user['role'] === 'coder'): ?>
                        <a href="<?= BASE_URL; ?>/projects">My Projects</a>
                        <a href="<?= BASE_URL; ?>/projects/create">Create Project</a>
                    <?php elseif ($user['role'] === 'client'): ?>
                        <a href="<?= BASE_URL; ?>/projects">My Projects</a>
                    <?php endif; ?>

                    <div class="profile-menu">
                        <button class="profile-button" onclick="toggleProfileMenu()">
                            <span class="profile-icon">👤</span>
                            <span class="profile-email"><?= htmlspecialchars($user['name']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="profile-info">
                                <strong><?= htmlspecialchars($user['email']); ?></strong>
                                <span class="role-badge role-<?= $user['role']; ?>">
                                    <?= ucfirst($user['role']); ?>
                                </span>
                            </div>
                            <a href="<?= BASE_URL; ?>/logout" class="logout-link">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="action-buttons" style="margin: 0;">
                        <a href="<?= BASE_URL; ?>/login" class="btn btn-success">Login</a>
                        <a href="<?= BASE_URL; ?>/signup" class="btn">Sign Up</a>
                    </div>

                    <style>
                        .header {
                            padding: 0;
                        }

                        .header h1 {
                            font-size: 1.2rem;
                            color: transparent;
                        }

                        .content-wrapper {
                            min-height: calc(100vh - 115px);
                            display: flex;
                            width: 100%;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                        }
                    </style>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <div class="content-wrapper">