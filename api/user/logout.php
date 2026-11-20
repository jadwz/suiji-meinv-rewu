<?php
// api/user/logout.php
require_once __DIR__ . '/../../config.php';
session_destroy();
echo json_encode(['code' => 200, 'msg' => '已退出']);
?>
