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
   
    $searchString = (string) $search;

    
    $filter = ['username' => new MongoDB\BSON\Regex($searchString, 'i')];
    $query = new MongoDB\Driver\Query($filter);

    try {
        $cursor = $manager->executeQuery("$dbName.users", $query);

        foreach ($cursor as $doc) {
            $searchResults[] = [
                'username' => $doc->username,
                'created_at' => $doc->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        }

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