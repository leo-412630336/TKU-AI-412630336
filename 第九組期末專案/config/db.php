<?php

$host = '127.0.0.1';
$port = '27017';
$dbname = 'secure_app';

try {
    // Connection String
    $uri = "mongodb://$host:$port/$dbname";

    // Create Manager
    $manager = new MongoDB\Driver\Manager($uri);

    // Basic ping to ensure connection works (optional)
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $manager->executeCommand($dbname, $command);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log($e->getMessage());
    die("Database connection failed (MongoDB). Ensure the MongoDB service is running and the PHP extension is enabled.");
}

// Global helper to get the database name easily
$dbName = $dbname;
?>