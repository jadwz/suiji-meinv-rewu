<?php
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['code' => 403, 'msg' => '权限不足']));
}

header('Content-Type: application/json; charset=utf-8');
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_settings') {
        $api_url = $_POST['api_url'] ?? '';
        $api_format = $_POST['api_format'] ?? 'json';
        $api_key = $_POST['api_key'] ?? '';
        
        $settings = [
            'api_url' => $api_url,
            'api_format' => $api_format,
            'api_key' => $api_key
        ];
        
        foreach ($settings as $k => $v) {
            $stmt = $conn->prepare("INSERT INTO `settings` (`key_name`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
            $stmt->bind_param("sss", $k, $v, $v);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['code' => 200, 'msg' => '保存成功']);
    } elseif ($action === 'add_user') {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        if (empty($user) || empty($pass)) {
            echo json_encode(['code' => 400, 'msg' => '用户名或密码不能为空']);
            exit;
        }
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO `users` (`username`, `password`, `role`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $hashed_pass, $role);
        if ($stmt->execute()) {
            echo json_encode(['code' => 200, 'msg' => '添加成功']);
        } else {
            echo json_encode(['code' => 500, 'msg' => '添加失败，可能用户名已存在']);
        }
        $stmt->close();
    } elseif ($action === 'delete_user') {
        $id = (int)$_POST['id'];
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['code' => 400, 'msg' => '不能删除自己']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM `users` WHERE `id` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['code' => 200, 'msg' => '删除成功']);
        $stmt->close();
    }
} else {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'stats') {
        $data = [
            'online' => 1,
            'today' => 0,
            'total' => 0,
            'likes' => [],
            'hourly_chart' => [],
            'daily_chart' => []
        ];
        
        // 在线人数统计 (通过 online_sessions 表)
        $online_count = 0;
        // 清理超时的会话 (30秒未活跃)
        $current_time = time();
        $timeout_threshold = $current_time - 30;
        $conn->query("DELETE FROM `online_sessions` WHERE `last_active` < $timeout_threshold");
        
        $result_online = $conn->query("SELECT COUNT(*) as online_count FROM `online_sessions`");
        if ($result_online && $result_online->num_rows > 0) {
            $row_online = $result_online->fetch_assoc();
            $online_count = (int)$row_online['online_count'];
        }
        $data['online'] = $online_count;
        
        // 今日请求
        $res = $conn->query("SELECT COUNT(*) as c FROM `api_logs` WHERE DATE(`created_at`) = CURDATE()");
        if ($res && $row = $res->fetch_assoc()) {
            $data['today'] = (int)$row['c'];
        }
        
        // 总访问
        $res = $conn->query("SELECT COUNT(*) as c FROM `api_logs`");
        if ($res && $row = $res->fetch_assoc()) {
            $data['total'] = (int)$row['c'];
        }
        
        // 点赞排行
        $res = $conn->query("SELECT `video_url`, COUNT(*) as c FROM `likes` GROUP BY `video_url` ORDER BY c DESC LIMIT 10");
        if ($res) {
            $data['likes'] = $res->fetch_all(MYSQLI_ASSOC);
        }
        
        // 今日按小时统计
        $res = $conn->query("SELECT HOUR(`created_at`) as h, COUNT(*) as c FROM `api_logs` WHERE DATE(`created_at`) = CURDATE() GROUP BY HOUR(`created_at`)");
        if ($res) {
            $data['hourly_chart'] = $res->fetch_all(MYSQLI_ASSOC);
        }
        
        // �?0天按天统�?(按月曲线)
        $res = $conn->query("SELECT DATE(`created_at`) as d, COUNT(*) as c FROM `api_logs` WHERE `created_at` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(`created_at`) ORDER BY d ASC");
        if ($res) {
            $data['daily_chart'] = $res->fetch_all(MYSQLI_ASSOC);
        }

        // 近12个月按月统计
        $res = $conn->query("SELECT DATE_FORMAT(`created_at`, '%Y-%m') as m, COUNT(*) as c FROM `api_logs` WHERE `created_at` >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01') GROUP BY DATE_FORMAT(`created_at`, '%Y-%m') ORDER BY m ASC");
        if ($res) {
            $data['monthly_chart'] = $res->fetch_all(MYSQLI_ASSOC);
        }
        
        echo json_encode(['code' => 200, 'data' => $data]);
        
    } elseif ($action === 'users') {
        $res = $conn->query("SELECT `id`, `username`, `role`, `created_at` FROM `users` ORDER BY `id` DESC");
        $users = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['code' => 200, 'data' => $users]);
        
    } elseif ($action === 'get_settings') {
        $res = $conn->query("SELECT * FROM `settings`");
        $settings = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $settings[$row['key_name']] = $row['value'];
            }
        }
        echo json_encode(['code' => 200, 'data' => $settings]);
    }
}
$conn->close();
?>
