<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/security.php';

generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF 驗證失敗。");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';

    if (trim($captcha) !== '12') {
        $error = "安全驗證回答錯誤。";
    } else {
        $result = register_user($manager, $dbName, $username, $password);
        if ($result['success']) {
            $success = "註冊成功！您現在可以登入了。";
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