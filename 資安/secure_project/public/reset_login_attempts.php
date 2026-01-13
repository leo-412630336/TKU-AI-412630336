<?php
require_once __DIR__ . '/../config/db.php';

try {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete([]); 

    $manager->executeBulkWrite("$dbName.login_attempts", $bulk);

    echo "<h1>Brute Force Protection Reset</h1>";
    echo "<p style='color:green;'>Success! Login attempts history has been cleared.</p>";
    echo "<p>You can now test the login blocking mechanism again.</p>";
    echo "<a href='login.php'>Go back to Login</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>