<?php
// bootstrap.php
define('ROOT_PATH', realpath(dirname(__FILE__)));

// Set up error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Auto-load classes
spl_autoload_register(function ($class_name) {
    $file = ROOT_PATH . '/includes/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load core files
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/auth.php';

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}