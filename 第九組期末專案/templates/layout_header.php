<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : '安全專案' ?>
    </title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <nav>
                <a href="home.php">首頁</a>
                <a href="apply.php">申請活動</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin.php" style="color:red; font-weight:bold;">管理員面板</a>
                <?php endif; ?>
                <a href="logout.php">登出</a>
            </nav>
        <?php endif; ?>