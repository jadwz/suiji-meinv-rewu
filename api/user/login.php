<?php
// api/user/login.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

$u = trim($_POST['username'] ?? '');
$p = $_POST['password'] ?? '';

if (empty($u) || empty($p)) {
    echo json_encode(['code' => 400, 'msg' => '账号密码不能为空']);
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT `id`, `password`, `role` FROM `users` WHERE `username` = ?");
$stmt->bind_param("s", $u);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($p, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $u;
        $_SESSION['role'] = $row['role'];
        echo json_encode(['code' => 200, 'msg' => '登录成功', 'role' => $row['role']]);
    } else {
        echo json_encode(['code' => 401, 'msg' => '密码错误']);
    }
} else {
    echo json_encode(['code' => 404, 'msg' => '用户不存在']);
}

$stmt->close();
$conn->close();
?>
