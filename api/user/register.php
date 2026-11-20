<?php
// api/user/register.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

if (empty($u) || empty($p)) {
    echo json_encode(['code' => 400, 'msg' => '账号密码不能为空']);
    exit;
}

$conn = getDbConnection();

// 检查是否存在
$stmt = $conn->prepare("SELECT `id` FROM `users` WHERE `username` = ?");
$stmt->bind_param("s", $u);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['code' => 400, 'msg' => '用户名已存在']);
    exit;
}
$stmt->close();

$hash = password_hash($p, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO `users` (`username`, `password`, `role`) VALUES (?, ?, 'user')");
$stmt->bind_param("ss", $u, $hash);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['username'] = $u;
    $_SESSION['role'] = 'user';
    echo json_encode(['code' => 200, 'msg' => '注册成功并已登录', 'role' => 'user']);
} else {
    echo json_encode(['code' => 500, 'msg' => '注册失败']);
}

$stmt->close();
$conn->close();
?>
