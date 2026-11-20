<?php
require_once __DIR__ . '/config.php';
// 如果未登录，直接在 PHP 端拦截跳转，防止暴露 UI
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>个人中心 - 樱舞次元</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-profile">
    <!-- 顶部导航栏 -->
    <?php include 'includes/nav.php'; ?>

    <div class="profile-shell">
        <div class="glass-box">
            <h2>✨ 个人资料 ✨</h2>

            <div class="avatar-wrap">
                <img id="previewAvatar" src="" alt="头像">
                <div class="role-badge" id="roleBadge">普通用户</div>
                <br>
                <input type="file" id="avatarFile" accept="image/*" class="avatar-file">
            </div>

            <p class="reg-time" id="regTime">注册时间：-</p>

            <div class="stats-panel">
                <div class="stats-item">
                    <h4 id="likesCount">0</h4>
                    <p>我赞过的</p>
                </div>
            </div>

            <div class="form-group">
                <label>登录账号 (不可改)</label>
                <input type="text" id="username" class="is-readonly" readonly>
            </div>

            <div class="form-group">
                <label>昵称 </label>
                <input type="text" id="nickname" placeholder="起个可爱的昵称吧~">
            </div>

            <div class="form-group">
                <label>个人简介</label>
                <textarea id="bio" placeholder="这个人很懒，什么都没写~"></textarea>
            </div>

            <div class="form-group">
                <label>修改密码 (不修改请留空)</label>
                <input type="password" id="new_password" placeholder="输入新密码">
            </div>

            <div class="btn-wrap">
                <button class="btn-back" onclick="location.href='index.php'">返回首页</button>
                <button class="btn-save" onclick="saveProfile()">保存资料</button>
            </div>
            <button class="btn-logout" onclick="logout()">退出登录</button>
        </div>
    </div>

    <script>
        // 页面加载时获取信息
        window.onload = async function() {
            try {
                const res = await fetch('api/user/profile.php');
                const data = await res.json();
                if (data.code === 401) {
                    showToast("请先登录！");
                    setTimeout(() => location.href = 'login.php', 1500);
                    return;
                }
                if (data.code === 200) {
                    document.getElementById('username').value = data.data.username;
                    document.getElementById('nickname').value = data.data.nickname || '';
                    document.getElementById('bio').value = data.data.bio || '';
                    document.getElementById('previewAvatar').src = data.data.avatar;
                    document.getElementById('roleBadge').innerText = data.data.role === 'admin' ? '管理员' : '普通用户';
                    document.getElementById('regTime').innerText = '注册时间：' + data.data.created_at;
                    document.getElementById('likesCount').innerText = data.data.likes_count;
                }
            } catch (e) {
                console.error("加载资料失败", e);
            }
        };

        // 保存资料
        async function saveProfile() {
            const formData = new FormData();
            formData.append('nickname', document.getElementById('nickname').value);
            formData.append('bio', document.getElementById('bio').value);
            
            const newPwd = document.getElementById('new_password').value;
            if (newPwd) {
                formData.append('new_password', newPwd);
            }
            
            const fileInput = document.getElementById('avatarFile');
            if (fileInput.files.length > 0) {
                formData.append('avatar_file', fileInput.files[0]);
            }

            try {
                const res = await fetch('api/user/profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                showToast(data.msg);
                if (data.code === 200) {
                    setTimeout(() => location.reload(), 1000); // 刷新显示最新头像
                }
            } catch (e) {
                showToast("保存失败");
                console.error(e);
            }
        }

        // 退出登录
        async function logout() {
            if(confirm("确定要退出吗？")) {
                await fetch('api/user/logout.php');
                location.href = 'index.php';
            }
        }
    </script>
</body>
</html>
