<?php
// src/forms.php

require_once __DIR__ . '/security.php';

function submit_application($manager, $dbName, $userId, $data)
{
    // 1. Validate Input
    $eventName = sanitize_input($data['event_name'] ?? '');
    $participantName = sanitize_input($data['participant_name'] ?? '');
    $email = sanitize_input($data['email'] ?? '');

    if (empty($eventName) || empty($participantName) || empty($email)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    // 2. Insert into 'forms' collection
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $id = new MongoDB\BSON\ObjectId();
        $bulk->insert([
            '_id' => $id,
            'user_id' => $userId,
            'event_name' => $eventName,
            'participant_name' => $participantName,
            'email' => $email,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $manager->executeBulkWrite("$dbName.forms", $bulk);

        log_event($manager, $dbName, 'FORM_SUBMIT', "User submitted application for $eventName", $userId);

        return ['success' => true, 'message' => 'Application submitted successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error.'];
    }
}

function get_all_applications($manager, $dbName)
{
    try {
        $query = new MongoDB\Driver\Query([], ['sort' => ['created_at' => -1]]);
        $cursor = $manager->executeQuery("$dbName.forms", $query);
        $forms = [];
        foreach ($cursor as $doc) {
            // Add ID string for deletion link
            $doc->id_str = (string) $doc->_id;
            $doc->created_at_fmt = $doc->created_at->toDateTime()->format('Y-m-d H:i');
            $forms[] = $doc;
        }
        return $forms;
    } catch (Exception $e) {
        return [];
    }
}

function delete_application($manager, $dbName, $idStr)
{
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($idStr)]);
        $manager->executeBulkWrite("$dbName.forms", $bulk);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>