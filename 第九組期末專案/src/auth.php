<?php

require_once __DIR__ . '/security.php';

function check_brute_force($manager, $dbName, $ip)
{
 
    $filter = ['ip_address' => $ip];
    $options = ['sort' => ['attempt_time' => -1], 'limit' => 5];
    $query = new MongoDB\Driver\Query($filter, $options);

    $cursor = $manager->executeQuery("$dbName.login_attempts", $query);
    $attempts = $cursor->toArray();

    if (count($attempts) >= 5) {
        $oldest = end($attempts);
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
    $username = (string) $username;

    $filter = ['username' => $username];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery("$dbName.users", $query);

    if (!empty($cursor->toArray())) {
        return ['success' => false, 'message' => '使用者名稱已被註冊。'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => '密碼長度必須至少為 8 個字元。'];
    }

    $hash = password_hash($password, PASSWORD_ARGON2ID);

    $role = ($username === 'admin') ? 'admin' : 'user';

    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $id = new MongoDB\BSON\ObjectId();
        $bulk->insert([
            '_id' => $id,
            'username' => $username,
            'password_hash' => $hash,
            'role' => $role,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $manager->executeBulkWrite("$dbName.users", $bulk);

        log_event($manager, $dbName, 'REGISTER_SUCCESS', "New user registered: $username ($role)", (string) $id);

        return ['success' => true, 'message' => '註冊成功！'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => '註冊期間發生資料庫錯誤。'];
    }
}

function login_user($manager, $dbName, $username, $password)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $username = (string) $username;

    if (check_brute_force($manager, $dbName, $ip)) {
        log_event($manager, $dbName, 'LOGIN_BLOCKED', "IP blocked due to too many acts: $username");
        return ['success' => false, 'message' => '登入失敗次數過多，請稍後再試。'];
    }

 
    $filter = ['username' => $username];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery("$dbName.users", $query);
    $users = $cursor->toArray();
    $user = $users[0] ?? null;

    if ($user && password_verify($password, $user->password_hash)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (string) $user->_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role ?? 'user'; 
        log_event($manager, $dbName, 'LOGIN_SUCCESS', "User logged in", (string) $user->_id);
        return ['success' => true];
    } else {
        record_failed_login($manager, $dbName, $ip);
        log_event($manager, $dbName, 'LOGIN_FAIL', "Failed login attempt for username: $username");
        return ['success' => false, 'message' => '使用者名稱或密碼錯誤。'];
    }
}

function get_all_users($manager, $dbName)
{
    try {
        $query = new MongoDB\Driver\Query([], ['sort' => ['created_at' => -1]]);
        $cursor = $manager->executeQuery("$dbName.users", $query);
        $users = [];
        foreach ($cursor as $doc) {
            $doc->created_at_fmt = $doc->created_at->toDateTime()->format('Y-m-d H:i');
            $users[] = $doc;
        }
        return $users;
    } catch (Exception $e) {
        return [];
    }
}
?>