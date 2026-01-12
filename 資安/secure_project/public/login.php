<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/security.php';

// Generate Token if not exists
generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF Validation Failed.");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Pass MongoDB manager and dbName
    $result = login_user($manager, $dbName, $username, $password);

    if ($result['success']) {
        header("Location: home.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login - Secure App';
require_once __DIR__ . '/../templates/layout_header.php';
require_once __DIR__ . '/../templates/login.php';
require_once __DIR__ . '/../templates/layout_footer.php';
?>