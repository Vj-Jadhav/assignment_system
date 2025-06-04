<?php
// logout.php
declare(strict_types=1);

// Start session if not active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Debug output (remove after testing)
error_log('Logout initiated - Session data: ' . print_r($_SESSION, true));

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        '/',  // Root path for entire domain
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy session
session_destroy();

// Security headers
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login
header('Location: /assignment_system/login.php');
exit();