<?php
// Project status definitions
define('STATUS_PENDING', 0);
define('STATUS_IN_PROGRESS', 1);
define('STATUS_COMPLETED', 2);

// Status labels for display
$statusLabels = [
    STATUS_PENDING => 'Pending',
    STATUS_IN_PROGRESS => 'In Progress',
    STATUS_COMPLETED => 'Completed'
];

// Status colors for badges
$statusColors = [
    STATUS_PENDING => '#FFA500',
    STATUS_IN_PROGRESS => '#2196F3',
    STATUS_COMPLETED => '#4CAF50'
];

// Role badge classes (for styling)
$roleClasses = [
    'admin' => 'role-admin',
    'coder' => 'role-coder',
    'client' => 'role-client'
];
?>