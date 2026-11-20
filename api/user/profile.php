<?php
// api/user/profile.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['code' => 401, 'msg' => '请先登录']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDbConnection();

// POST 请求表示更新信息
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $avatar = '';

    // 处理头像文件上传
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
        // 简单限制扩展名
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $upload_dir . $filename)) {
                $avatar = 'uploads/' . $filename;
            }
        }
    }

    // 构建 SQL 语句 (动态更新)
    $sql = "UPDATE `users` SET `nickname` = ?, `bio` = ?";
    $params = [$nickname, $bio];
    $types = "ss";

    if ($avatar !== '') {
        $sql .= ", `avatar` = ?";
        $params[] = $avatar;
        $types .= "s";
    }
    
    if ($new_password !== '') {
        $sql .= ", `password` = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        $types .= "s";
    }
    
    $sql .= " WHERE `id` = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['code' => 200, 'msg' => '个人信息保存成功', 'new_avatar' => $avatar]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '保存失败']);
    }
    $stmt->close();
} 
// GET 请求表示获取信息
else {
    $stmt = $conn->prepare("SELECT `username`, `nickname`, `avatar`, `bio`, `role`, `created_at` FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // 如果没有头像，给个默认头像
    if (empty($result['avatar'])) {
        $result['avatar'] = 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($result['username']);
    }
    
    // 获取我的点赞数 (free版本用IP统计)
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt_likes = $conn->prepare("SELECT COUNT(*) as c FROM `likes` WHERE `ip` = ?");
    $stmt_likes->bind_param("s", $ip);
    $stmt_likes->execute();
    $result['likes_count'] = $stmt_likes->get_result()->fetch_assoc()['c'];
    
    echo json_encode(['code' => 200, 'data' => $result]);
    $stmt->close();
}

$conn->close();
?>
