<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAuth($allowedRoles = [])
{
    // Check if user is authenticated
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header('Location:' . BASE_URL . '/login');
        exit();
    }

    if (!empty($allowedRoles)) {
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            redirectToDashboard();
            exit();
        }
    }
}

function redirectToDashboard()
{
    $role = $_SESSION['role'] ?? null;

    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_URL . '/dashboard/admin');
            break;
        case 'coder':
            header('Location: ' . BASE_URL . '/dashboard/coder');
            break;
        case 'client':
            header('Location: ' . BASE_URL . '/dashboard/client');
            break;
        default:
            header('Location: ' . BASE_URL . '/login');
    }
    exit();
}

function getCurrentUser()
{
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}


function hasRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>