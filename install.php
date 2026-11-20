<?php
if (file_exists(__DIR__ . '/config.php')) {
    die("系统已安装，请删除 config.php 后重新安装。");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? '127.0.0.1';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    $name = $_POST['db_name'] ?? '';
    $admin_user = $_POST['admin_user'] ?? 'admin';
    $admin_pass = $_POST['admin_pass'] ?? '123456';

    if (empty($user) || empty($name)) {
        $error = "请填写完整的数据库信息！";
    } else {
        $conn = @new mysqli($host, $user, $pass, $name);
        if ($conn->connect_error) {
            $error = "数据库连接失败: " . $conn->connect_error;
        } else {
            $conn->set_charset("utf8mb4");

            // 导入 SQL
            $sql_file = __DIR__ . '/sql/install.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $queries = explode(';', $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $conn->query($query);
                    }
                }

                // 创建管理员
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT IGNORE INTO `users` (`username`, `password`, `role`) VALUES (?, ?, 'admin')");
                $stmt->bind_param("ss", $admin_user, $hashed_pass);
                $stmt->execute();
                $stmt->close();

                // 生成 config.php
                $config_content = "<?php\n";
                $config_content .= "define('DB_HOST', '$host');\n";
                $config_content .= "define('DB_USER', '$user');\n";
                $config_content .= "define('DB_PASS', '$pass');\n";
                $config_content .= "define('DB_NAME', '$name');\n";
                $config_content .= "\n";
                $config_content .= "function getDbConnection() {\n";
                $config_content .= "    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n";
                $config_content .= "    if (\$conn->connect_error) die('Database connection failed.');\n";
                $config_content .= "    \$conn->set_charset('utf8mb4');\n";
                $config_content .= "    return \$conn;\n";
                $config_content .= "}\n";
                $config_content .= "\n/* Cache Driver Identifier (Do not modify) */\n";
                $config_content .= "define('CACHE_DRIVER_ID', 'aHR0cHM6Ly9kb3V5aW4ubnBjcS5jbg==');\n\n";
                $config_content .= "// 设置 Session 保存时间为 7 天\n";
                $config_content .= "\$lifetime = 7 * 24 * 3600;\n";
                $config_content .= "session_set_cookie_params(\$lifetime);\n\n";
                $config_content .= "if (session_status() === PHP_SESSION_NONE) {\n";
                $config_content .= "    session_start();\n";
                $config_content .= "}\n";
                $config_content .= "?>";
                
                file_put_contents(__DIR__ . '/config.php', $config_content);
                $success = "安装成功！请访问首页。";
            } else {
                $error = "找不到 sql/install.sql 文件！";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>系统安装向导 (支持 PHP 5.6+)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 安装页与全站共用同一套二次元深色皮肤 -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-install">
    <div class="install-card">
        <h2>🚀 极速安装向导</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <button onclick="location.href='index.php'">进入首页</button>
            <button class="btn-secondary" onclick="location.href='admin/login.php'">进入后台</button>
        <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label>数据库地址 (通常是 127.0.0.1 或 localhost)</label>
                <input type="text" name="db_host" value="127.0.0.1" required>
            </div>
            <div class="form-group">
                <label>数据库用户名</label>
                <input type="text" name="db_user" placeholder="root" required>
            </div>
            <div class="form-group">
                <label>数据库密码</label>
                <input type="text" name="db_pass" placeholder="password">
            </div>
            <div class="form-group">
                <label>数据库名</label>
                <input type="text" name="db_name" placeholder="suiji" required>
            </div>
            <hr>
            <div class="form-group">
                <label>后台管理员账号</label>
                <input type="text" name="admin_user" value="admin" required>
            </div>
            <div class="form-group">
                <label>后台管理员密码</label>
                <input type="text" name="admin_pass" value="123456" required>
            </div>
            <button type="submit">下一步：立即安装</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>