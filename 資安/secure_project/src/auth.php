<?php
// src/auth.php

require_once __DIR__ . '/security.php';

function check_brute_force($manager, $dbName, $ip)
{
    // MongoDB doesn't have SQL intervals easily without Date objects, we compare timestamps.
    // For simplicity in this driver usage, we check count. 
    // In real app, use: 'attempt_time' => ['$gt' => new MongoDB\BSON\UTCDateTime(...)]

    // Simplification: Check last 5 attempts from this IP.
    // If they are recent, block. 
    // For this demo, we just count total attempts. Real brute force logic needs time window.

    $filter = ['ip_address' => $ip];
    $options = ['sort' => ['attempt_time' => -1], 'limit' => 5];
    $query = new MongoDB\Driver\Query($filter, $options);

    $cursor = $manager->executeQuery("$dbName.login_attempts", $query);
    $attempts = $cursor->toArray();

    if (count($attempts) >= 5) {
        // Retrieve the 5th most recent attempt
        $oldest = end($attempts);
        // If it was less than 15 mins ago (900 seconds)
        // Note: $oldest->attempt_time is BSON UTCDateTime (milliseconds)
        $timeDiff = (time() * 1000) - (string) $oldest->attempt_time;
        if ($timeDiff < 900000) {
            return true;
        }
    }
    return false;
}

function record_failed_login($manager, $dbName, $ip)
{
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'ip_address' => $ip,
        'attempt_time' => new MongoDB\BSON\UTCDateTime()
    ]);
    $manager->executeBulkWrite("$dbName.login_attempts", $bulk);
}

function register_user($manager, $dbName, $username, $password)
{
    // NoSQL Injection Protection: Cast to string
    $username = (string) $username;

    // 1. Check if username exists
    // $eq is safer, but casting is the primary defense.
    $filter = ['username' => $username];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery("$dbName.users", $query);

    if (!empty($cursor->toArray())) {
        return ['success' => false, 'message' => 'Username already taken.'];
    }

    // 2. Validate password
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    // 3. Hash password
    $hash = password_hash($password, PASSWORD_ARGON2ID);

    // 4. Insert user
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $id = new MongoDB\BSON\ObjectId();
        $bulk->insert([
            '_id' => $id,
            'username' => $username,
            'password_hash' => $hash,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $manager->executeBulkWrite("$dbName.users", $bulk);

        log_event($manager, $dbName, 'REGISTER_SUCCESS', "New user registered: $username", (string) $id);

        return ['success' => true, 'message' => 'Registration successful!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error during registration.'];
    }
}

function login_user($manager, $dbName, $username, $password)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // NoSQL Injection Protection
    $username = (string) $username;

    // 1. Check Brute Force
    if (check_brute_force($manager, $dbName, $ip)) {
        log_event($manager, $dbName, 'LOGIN_BLOCKED', "IP blocked due to too many acts: $username");
        return ['success' => false, 'message' => 'Too many failed attempts. Please try again later.'];
    }

    // 2. Fetch User
    // Explicitly casting $username prevents query injection like {$ne: null}
    $filter = ['username' => $username];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery("$dbName.users", $query);
    $users = $cursor->toArray();
    $user = $users[0] ?? null;

    // 3. Verify Password
    if ($user && password_verify($password, $user->password_hash)) {
        // Login Success
        session_regenerate_id(true);
        $_SESSION['user_id'] = (string) $user->_id;
        $_SESSION['username'] = $user->username;

        log_event($manager, $dbName, 'LOGIN_SUCCESS', "User logged in", (string) $user->_id);
        return ['success' => true];
    } else {
        // Login Fail
        record_failed_login($manager, $dbName, $ip);
        log_event($manager, $dbName, 'LOGIN_FAIL', "Failed login attempt for username: $username");
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
}
?>