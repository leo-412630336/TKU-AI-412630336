<?php

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

    try {
        $manager->executeBulkWrite("$dbName.security_logs", $bulk);
    } catch (Exception $e) {
        error_log("Failed to write to security log: " . $e->getMessage());
    }
}


function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); // Converts special chars to HTML entities
    return $data;
}


function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verify_csrf_token($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}


function detect_attack($input)
{
    $patterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is' => 'XSS Attack (Script Tag)',
        '/javascript:/i' => 'XSS Attack (JavaScript Protocol)',
        '/onerror=/i' => 'XSS Attack (Event Handler)',
        '/onload=/i' => 'XSS Attack (Event Handler)',
        '/alert\s*\(/i' => 'XSS Attack (Alert Function)',
        '/UNION\s+SELECT/i' => 'SQL Injection (UNION)',
        '/BENCHMARK\s*\(/i' => 'SQL Injection (Benchmark)',
        '/SLEEP\s*\(/i' => 'SQL Injection (Sleep)'
    ];

    foreach ($patterns as $pattern => $type) {
        if (preg_match($pattern, $input)) {
            return $type;
        }
    }
    return false;
}


function scan_inputs($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_string($key) && strpos($key, '$') === 0) {
                return "NoSQL Injection (MongoDB Operator: $key)";
            }
            $result = scan_inputs($value);
            if ($result) {
                return $result;
            }
        }
    } elseif (is_string($data)) {
        return detect_attack($data);
    }
    return false;
}

function run_security_check()
{
    $result = scan_inputs($_GET);
    if ($result) {
        trigger_block($result, 'DEBUG_INFO_HIDDEN'); // Payload hidden for nested arrays simplicity
    }

    $result = scan_inputs($_POST);
    if ($result) {
        trigger_block($result, 'DEBUG_INFO_HIDDEN');
    }
}

function trigger_block($type, $payload)
{
 
    global $manager, $dbName;

    if (isset($manager) && isset($dbName)) {
        $userId = $_SESSION['user_id'] ?? 'guest';
        try {
            log_event($manager, $dbName, 'ATTACK_DETECTED', "Type: $type, Payload: $payload", $userId);
        } catch (Exception $e) {
        }
    }

    $safeType = htmlspecialchars($type);

    echo "<!DOCTYPE html><html><head><script>";
    echo "alert('安全警告：檢測到惡意行為 ($safeType)。為了您的安全，系統將執行強制登出。');";
    echo "window.location.href = 'logout.php';";
    echo "</script></head><body></body></html>";
    exit;
}

run_security_check();
?>