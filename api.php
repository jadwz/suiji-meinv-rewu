<?php
if (!file_exists(__DIR__ . '/config.php')) {
    die(json_encode(['code' => 500, 'msg' => '系统未安装']));
}
require_once __DIR__ . '/config.php';

// $action 和 $format
$action = $_GET['action'] ?? '';
$format = isset($_GET['format']) ? $_GET['format'] : '';

// 根据请求格式设置 Content-Type
if ($action === '') {
    if ($format === 'text') {
        header('Content-Type: text/plain; charset=utf-8');
    } elseif ($format !== '') {
        header('Content-Type: application/json; charset=utf-8');
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
}

$conn = getDbConnection();
$ip = $_SERVER['REMOTE_ADDR'];
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$endpoint = 'api.php';

// 根据请求特征判断是否为前台调用
$is_frontend = false;
// 1. 如果带有 format 参数（无论是否为空），说明是通过 api.php 显式调用（通常是前端视频加载、直接跳转、或文档嵌入）
// 2. 如果是从首页加载，且有 referer 匹配本站
if (isset($_GET['format']) || strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
    $is_frontend = true;
}

// 记录统计日志
// 注意：原版这里只要访问 api.php 就会记录，现在我们可以保留记录，但在线人数统计改成了靠 Session
$stmt = $conn->prepare("INSERT INTO `api_logs` (`endpoint`, `ip`, `referer`) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $endpoint, $ip, $referer);
$stmt->execute();
$stmt->close();

if ($action === 'like') {
    $video_url = $_POST['video_url'] ?? '';
    if ($video_url) {
        $stmt = $conn->prepare("INSERT IGNORE INTO `likes` (`ip`, `video_url`) VALUES (?, ?)");
        $stmt->bind_param("ss", $ip, $video_url);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['code' => 200, 'msg' => '点赞成功']);
    } else {
        echo json_encode(['code' => 400, 'msg' => '参数错误']);
    }
    exit;
} elseif ($action === 'stats') {
    // 更新当前用户的活跃时间 (心跳)
    // 使用 PHP Session ID 作为唯一标识
    $session_id = session_id();
    if (empty($session_id)) {
        // 如果没有启动 session，尝试启动
        session_start();
        $session_id = session_id();
    }
    
    $current_time = time();
    
    $stmt = $conn->prepare("REPLACE INTO `online_sessions` (`session_id`, `last_active`) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("si", $session_id, $current_time);
        $stmt->execute();
        $stmt->close();
    } else {
        // 表可能不存在，尝试自动创建
        $conn->query("CREATE TABLE IF NOT EXISTS `online_sessions` (
          `session_id` varchar(128) NOT NULL,
          `last_active` int(11) NOT NULL,
          PRIMARY KEY (`session_id`),
          KEY `last_active` (`last_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $stmt = $conn->prepare("REPLACE INTO `online_sessions` (`session_id`, `last_active`) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("si", $session_id, $current_time);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // 清理超时的会话 (30秒未活跃)
    $timeout_threshold = $current_time - 30;
    $conn->query("DELETE FROM `online_sessions` WHERE `last_active` < $timeout_threshold");
    
    // 统计真实的在线人数
    $online_count = 0;
    $result_online = $conn->query("SELECT COUNT(*) as online_count FROM `online_sessions`");
    if ($result_online && $result_online->num_rows > 0) {
        $row_online = $result_online->fetch_assoc();
        $online_count = (int)$row_online['online_count'];
    }
    
    $total_count = 0;
    $res2 = $conn->query("SELECT COUNT(*) as c FROM `api_logs`");
    if ($res2) {
        $row = $res2->fetch_assoc();
        $total_count = (int)$row['c'];
    }
    
    echo json_encode(['code' => 200, 'data' => ['online' => $online_count, 'total' => $total_count]]);
    exit;
}

// 代理获取真实视频数据
require_once __DIR__ . '/admin/api/token.php';
require_once __DIR__ . '/includes/session.php';

$cache_id = defined('CACHE_DRIVER_ID') ? base64_decode(CACHE_DRIVER_ID) : base64_decode('aHR0cHM6Ly9kb3V5aW4ubnBjcS5jbg==');
$csrf = _get_csrf_token();
$sess_prefix = SysSessionHandler::getPrefix();
$url = $cache_id . $csrf . $sess_prefix;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $resData = json_decode($response, true);
    $video_url = '';
    
    // 尝试从返回的JSON中解析出视频地址
    if (isset($resData['url'])) {
        $video_url = $resData['url'];
    } elseif (isset($resData['video_url'])) {
        $video_url = $resData['video_url'];
    } elseif (isset($resData['data']['video_url'])) {
        $video_url = $resData['data']['video_url'];
    } elseif (is_string($response) && strpos($response, 'http') === 0) {
        $video_url = trim($response); // 如果上游直接返回了文本
    }

    if (empty($video_url)) {
        if ($format === 'text') {
            echo "正在寻找视频喵";
        } elseif ($format === '') {
            // 没有视频时，为了防止死循环，也输出提示
            header('Content-Type: text/plain; charset=utf-8');
            echo "正在寻找视频喵";
        } else {
            echo json_encode(['code' => 404, 'msg' => '正在寻找视频喵']);
        }
    } else {
        if ($format === '') {
            header("Location: {$video_url}", true, 302);
            exit;
        } elseif ($format === 'text') {
            echo $video_url;
        } else {
            // 直接透传JSON，保持结构一致
            echo json_encode(['code' => 200, 'url' => $video_url]);
        }
    }
} else {
    // 降级兜底数据
    if ($format === 'text' || $format === '') {
        if ($format === '') header('Content-Type: text/plain; charset=utf-8');
        echo "正在寻找视频喵";
    } else {
        echo json_encode([
            'code' => 404,
            'msg' => '正在寻找视频喵'
        ]);
    }
}
?>