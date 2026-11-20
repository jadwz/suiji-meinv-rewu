<?php
require_once __DIR__ . '/config.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录 / 注册 - 樱舞次元</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 全站统一二次元皮肤（本分发包自带，不引用上级目录） -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-auth">
    <div class="auth-wrap">
        <!-- 品牌标题区 -->
        <div class="auth-brand">
            <span class="brand-mark" aria-hidden="true">🌸</span>
            <span class="auth-name">樱舞次元</span>
            <span class="auth-sub">SAKURA DANCE</span>
        </div>

        <div class="glass-box auth-card">
            <h2>✨ 欢迎回来 ✨</h2>
            <input type="text" id="username" placeholder="用户名">
            <input type="password" id="password" placeholder="密码">

            <div class="auth-actions">
                <button class="btn-login" onclick="doAuth('login')">登 录</button>
                <button class="btn-register" onclick="doAuth('register')">注 册</button>
            </div>
            <p class="tips">建议用QQ号注册</p>
            <p class="back-row"><a href="index.php">← 返回首页</a></p>
        </div>
    </div>
    <script>
        async function doAuth(action) {
            // 检查是否是直接双击打开的 HTML 文件
            if (window.location.protocol === 'file:') {
                alert("⚠️ 错误：请通过本地服务器环境（如 localhost）访问该页面，直接双击打开 HTML 无法运行 PHP 后端哦！");
                return;
            }

            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            if(!u || !p) return alert('请输入账号密码！');
            
            const formData = new FormData();
            formData.append('username', u);
            formData.append('password', p);

            const url = action === 'login' ? 'api/user/login.php' : 'api/user/register.php';
            try {
                const res = await fetch(url, { method: 'POST', body: formData });
                const text = await res.text(); // 先拿到纯文本
                
                try {
                    const data = JSON.parse(text); // 尝试解析 JSON
                    alert(data.msg);
                    if (data.code === 200) {
                        if (data.role === 'admin') {
                            location.href = 'admin/index.php';
                        } else {
                            location.href = 'index.php';
                        }
                    }
                } catch (jsonErr) {
                    // 如果解析 JSON 失败，说明 PHP 报错了或者数据库没连上
                    alert("服务器返回异常，可能是数据库账号密码未配置正确。\n详细信息: " + text);
                    console.error("解析失败:", text);
                }
            } catch (e) {
                alert("网络请求失败，请检查 PHP 环境是否正常启动！\n报错：" + e.message);
                console.error(e);
            }
        }
    </script>
</body>
</html>
