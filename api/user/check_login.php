<?php
// api/user/check_login.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'code' => 200, 
        'msg' => '已登录',
        'data' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ]
    ]);
} else {
    echo json_encode(['code' => 401, 'msg' => '未登录']);
}
?>
