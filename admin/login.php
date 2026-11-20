<?php
if (!file_exists(__DIR__ . '/../config.php')) {
    header('Location: ../install.php');
    exit;
}
require_once __DIR__ . '/../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = '密码错误';
        }
    } else {
        $error = '用户不存在';
    }
    $stmt->close();
}

// 退出登录逻辑
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员登录 - 樱舞次元</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 全站统一二次元皮肤（本分发包自带，不引用上级目录） -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-admin-login">
    <div class="admin-login-card">
        <span class="brand-mark" aria-hidden="true">🌸</span>
        <h2>后台登录</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="管理员账号" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="管理员密码" required>
            </div>
            <button type="submit">登录</button>
        </form>
        <div class="back-row">
            <a href="../index.php">← 返回首页</a>
        </div>
    </div>
</body>
</html>
