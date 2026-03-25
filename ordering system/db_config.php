<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ordering_system');

// Create connection without selecting database first
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists, if not create it
$dbCheckResult = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");

if ($dbCheckResult->num_rows == 0) {
    // Database doesn't exist, create it
    $createDbSql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!$conn->query($createDbSql)) {
        die("Error creating database: " . $conn->error);
    }
    
    // Initialize tables
    $_SESSION['db_needs_init'] = true;
}

// Select the database
$conn->select_db(DB_NAME);

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Check if all required tables exist
$requiredTables = array('products', 'orders', 'order_items', 'users', 'producers', 'admins', 'loyalty_transactions');
$tablesCheckResult = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME IN ('" . implode("','", $requiredTables) . "')");

if ($tablesCheckResult->num_rows < count($requiredTables)) {
    // Tables don't exist, redirect to initialization
    header('Location: db_init.php');
    exit;
}
