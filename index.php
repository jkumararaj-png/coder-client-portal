<?php
require_once './includes/auth.php';

// Auto-detect base URL from the script path
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $scriptName === '/' ? '' : $scriptName);

// Get the requested path
$path = $_SERVER['REQUEST_URI'];

$path = parse_url($path, PHP_URL_PATH);

// Remove base URL from path
if (BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
    $path = substr($path, strlen(BASE_URL));
}

$path = trim($path, '/');

// Route to the correct file
switch ($path) {
    // Home/Landing
    case '':
        require 'views/home.php';
        break;

    // Auth routes
    case 'login':
        require 'auth/login.php';
        break;

    case 'signup':
        require 'auth/signup.php';
        break;

    case 'logout':
        require 'auth/logout.php';
        break;

    // Dashboard routes
    case 'dashboard':
        redirectToDashboard();
        break;

    case 'dashboard/admin':
        require 'dashboard/admin/admin.php';
        break;

    case 'dashboard/admin/users':
        require 'dashboard/admin/users.php';
        break;

    case 'dashboard/admin/edit-user':
        require 'dashboard/admin/edit-user.php';
        break;

    case 'dashboard/admin/delete-user':
        require 'dashboard/admin/delete-user.php';
        break;

    case 'dashboard/coder':
        require 'dashboard/coder.php';
        break;

    case 'dashboard/client':
        require 'dashboard/client.php';
        break;

    // Project routes
    case 'projects':
        require 'projects/view.php';
        break;

    case 'projects/detail':
        require 'projects/detail.php';
        break;

    case 'projects/create':
        require 'projects/create.php';
        break;

    case 'projects/edit':
        require 'projects/edit.php';
        break;

    case 'projects/delete':
        require 'projects/delete.php';
        break;

    // Feedback routes
    case 'feedback/create':
        require 'feedback/create.php';
        break;

    case 'feedback/delete':
        require 'feedback/delete.php';
        break;

    // 404 - Page not found
    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page '{$path}' does not exist.</p>";
        echo "<a href='" . BASE_URL . "'>Go Home</a>";
        break;
}
?>