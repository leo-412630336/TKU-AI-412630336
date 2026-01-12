<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/security.php';

generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF Validation Failed.");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';

    // Mock CAPTCHA Validation (5 + 7 = 12)
    if (trim($captcha) !== '12') {
        $error = "Incorrect Security Answer.";
    } else {
        $result = register_user($pdo, $username, $password);
        if ($result['success']) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Register - Secure App';
require_once __DIR__ . '/../templates/layout_header.php';
require_once __DIR__ . '/../templates/register.php';
require_once __DIR__ . '/../templates/layout_footer.php';
?>