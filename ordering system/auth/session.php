<?php
// Session management

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['producer_id']) || isset($_SESSION['admin_id']);
}

/**
 * Check if current user is a customer
 * @return bool
 */
function isCustomer() {
    return isset($_SESSION['user_id']) && !isset($_SESSION['producer_id']) && !isset($_SESSION['admin_id']);
}

/**
 * Check if current user is a producer
 * @return bool
 */
function isProducer() {
    return isset($_SESSION['producer_id']) && !isset($_SESSION['admin_id']);
}

/**
 * Check if current user is an admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require customer login
 */
function requireCustomer() {
    if (!isCustomer()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require producer login
 */
function requireProducer() {
    if (!isProducer()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Login as customer
 */
function loginCustomer($userId, $email) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_type'] = 'customer';
}

/**
 * Login as producer
 */
function loginProducer($producerId, $email, $companyName) {
    $_SESSION['producer_id'] = $producerId;
    $_SESSION['producer_email'] = $email;
    $_SESSION['company_name'] = $companyName;
    $_SESSION['user_type'] = 'producer';
}

/**
 * Login as admin
 */
function loginAdmin($adminId, $email) {
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_email'] = $email;
    $_SESSION['user_type'] = 'admin';
}

/**
 * Get current user type
 */
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Get current user/producer/admin ID
 */
function getCurrentId() {
    if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
    if (isset($_SESSION['producer_id'])) return $_SESSION['producer_id'];
    if (isset($_SESSION['admin_id'])) return $_SESSION['admin_id'];
    return null;
}
