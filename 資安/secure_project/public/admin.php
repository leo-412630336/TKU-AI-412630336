<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/forms.php';
require_once __DIR__ . '/../src/security.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: home.php");
}

generate_csrf_token();

if (isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF Validation Failed.");
    }
    if (delete_application($manager, $dbName, $_POST['delete_id'])) {
        $success = "Application deleted.";
    } else {
        $error = "Failed to delete application.";
    }
}

$applications = get_all_applications($manager, $dbName);

require_once __DIR__ . '/../src/auth.php'; 
$users = get_all_users($manager, $dbName);

$pageTitle = 'Admin Panel - Secure App';
require_once __DIR__ . '/../templates/layout_header.php';
?>

<h1>Admin Dashboard</h1>

<div class="alert alert-success" style="border-color: #f1c40f; background-color: #fcf8e3; color: #8a6d3b;">
    <strong>Beta Feature:</strong> Role-Based Access Control (RBAC) System Active.<br>
    You are viewing this page because your role is: <strong>
        <?= htmlspecialchars($_SESSION['role']) ?>
    </strong>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<h2>Received Applications</h2>

<?php if (empty($applications)): ?>
    <p>No applications found.</p>
<?php else: ?>
    <table border="1" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
        <tr style="background: #eee;">
            <th>Event</th>
            <th>Name</th>
            <th>Email</th>
            <th>Submitted At</th>
            <th>Action</th>
        </tr>
        <?php foreach ($applications as $app): ?>
            <tr>
                <td style="padding:8px;">
                    <?= htmlspecialchars($app->event_name) ?>
                </td>
                <td style="padding:8px;">
                    <?= htmlspecialchars($app->participant_name) ?>
                </td>
                <td style="padding:8px;">
                    <?= htmlspecialchars($app->email) ?>
                </td>
                <td style="padding:8px;">
                    <?= htmlspecialchars($app->created_at_fmt) ?>
                </td>
                <td style="padding:8px; text-align:center;">
                    <form method="POST" action="admin.php" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($app->id_str) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" style="background:#e74c3c; padding: 4px 8px; font-size: 0.9rem;">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/layout_footer.php'; ?>