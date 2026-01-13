<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/forms.php';
require_once __DIR__ . '/../src/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF Validation Failed.");
    }

    $result = submit_application($manager, $dbName, $_SESSION['user_id'], $_POST);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Apply Event - Secure App';
require_once __DIR__ . '/../templates/layout_header.php';
?>

<h1>Event Application</h1>

<div class="alert alert-success">
    Logged in as: <strong>
        <?= htmlspecialchars($_SESSION['username']) ?>
    </strong>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="apply.php">
    <div class="form-group">
        <label for="event_name">Event Name</label>
        <select id="event_name" name="event_name" required style="width:100%; padding:0.75rem;">
            <option value="Security Workshop">Security Workshop 2024</option>
            <option value="Web Hacking Contest">Web Hacking Contest</option>
            <option value="Defense Seminar">Defense Seminar</option>
        </select>
    </div>

    <div class="form-group">
        <label for="participant_name">Your Full Name</label>
        <input type="text" id="participant_name" name="participant_name" required>
    </div>

    <div class="form-group">
        <label for="email">Contact Email</label>
        <input type="text" id="email" name="email" required>
    </div>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit">Submit Application</button>
</form>

<?php require_once __DIR__ . '/../templates/layout_footer.php'; ?>