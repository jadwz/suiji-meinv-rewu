<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>APP下载 - 樱舞次元</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-app">
    <!-- 顶部导航栏 -->
    <?php include 'includes/nav.php'; ?>

    <div class="glass-box app-card">
        <div class="app-icon">📱</div>
        <h2>在线热舞源码</h2>
        <p class="desc">随时随地，极速播放，体验更流畅</p>

        <a href="./mv.zip" class="btn-download">⬇️ 下载</a>

        <div class="version-info">
            <p>最新版本：v1.0.0 | 更新日期：<?php echo date('Y-m-d'); ?></p>
        </div>
    </div>
</body>
</html>
