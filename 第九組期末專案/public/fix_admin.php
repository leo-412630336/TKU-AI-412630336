<?php
require_once __DIR__ . '/../config/db.php';

$username = 'admin'; 

try {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['username' => $username],
        ['$set' => ['role' => 'admin']]
    );

    $result = $manager->executeBulkWrite("$dbName.users", $bulk);

    if ($result->getModifiedCount() > 0) {
        echo "Success! User '$username' is now an Admin.<br>";
        echo "Please <a href='logout.php'>Logout</a> and Login again.";
    } else {
        echo "No changes made. Either user '$username' not found, or they are already an admin.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>