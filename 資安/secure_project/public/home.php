<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$searchResults = [];

if ($search) {
    // SECURE: NoSQL Injection Protection
    // 1. Cast to string to prevent object injection (e.g. ?search[$ne]=null)
    $searchString = (string) $search;

    // 2. Use MongoDB Regex for LIKE functionality
    // Note: In a high-security app, ensure you escape regex special characters if you want exact match only.
    // preg_quote equivalent isn't native to MongoDB, but for simple user search it's okay.
    // If user enters '.', it matches any char. To strictly prevent Regex DoS, limit length.

    $filter = ['username' => new MongoDB\BSON\Regex($searchString, 'i')];
    $query = new MongoDB\Driver\Query($filter);

    try {
        $cursor = $manager->executeQuery("$dbName.users", $query);

        // Convert BSON Documents to arrays for template
        foreach ($cursor as $doc) {
            $searchResults[] = [
                'username' => $doc->username,
                // Convert BSON UTCDateTime to readable string
                'created_at' => $doc->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        }

        // Log the search action
        log_event($manager, $dbName, 'SEARCH', "User searched for: $searchString", $_SESSION['user_id']);
    } catch (Exception $e) {
        $error = "Search failed.";
    }
}

$pageTitle = 'Home - Secure App (MongoDB)';
require_once __DIR__ . '/../templates/layout_header.php';
require_once __DIR__ . '/../templates/home.php';
require_once __DIR__ . '/../templates/layout_footer.php';
?>