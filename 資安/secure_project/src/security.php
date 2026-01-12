<?php
// src/security.php

/**
 * Log security events to MongoDB
 */
function log_event($manager, $dbName, $event_type, $description, $user_id = null)
{
    if (!isset($_SERVER['REMOTE_ADDR'])) {
        $ip = '127.0.0.1';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'user_id' => $user_id,
        'event_type' => $event_type,
        'description' => $description,
        'ip_address' => $ip,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    // Fire and forget (optional error handling)
    try {
        $manager->executeBulkWrite("$dbName.security_logs", $bulk);
    } catch (Exception $e) {
        // Silently fail logging to avoid breaking app flow, or log to file
        error_log("Failed to write to security log: " . $e->getMessage());
    }
}

/**
 * Basic input sanitization (Use primarily for non-HTML storage)
 * For HTML output, always use htmlspecialchars() at the point of output.
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); // Converts special chars to HTML entities
    return $data;
}

/**
 * Generate a CSRF token
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}
?>