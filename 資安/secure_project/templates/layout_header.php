<!-- templates/layout_header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Secure App' ?>
    </title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <nav>
                <a href="home.php">Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        <?php endif; ?>