<?php
include '../db_config.php';
include '../auth/session.php';

requireLogin();

$userType = getUserType();

// Route to appropriate dashboard
if ($userType === 'customer') {
    header('Location: customer_dashboard.php');
} elseif ($userType === 'producer') {
    header('Location: producer_dashboard.php');
} elseif ($userType === 'admin') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: ../auth/logout.php');
}
$conn->close();
