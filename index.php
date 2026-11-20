<?php
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: install.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>首页 - 樱舞次元</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- 顶部导航栏 -->
    <?php include 'includes/nav.php'; ?>

    <!-- 在线统计固定在屏幕左上角，独立外层 -->
    <div class="top-bar">
        <span class="stats-item">👁️ <span id="onlineCount">0</span> 在线</span>
        <span class="stats-item">▶️ <span id="playCount">0</span> 播放</span>
    </div>

    <div id="app">
        <!-- 视频容器：玻璃卡片 + 渐变描边 -->
        <div class="video-container">
            <div class="player-card">
                <div class="player-frame">
                    <video id="player" autoplay preload="auto" playsinline webkit-playsinline x5-video-player-type="h5" x5-video-player-fullscreen="false" x5-playsinline controlsList="nodownload"></video>
                </div>
            </div>
        </div>

        <!-- 加载中动画（樱花渐变环） -->
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="spinner"></div>
            <p>正在寻找好看的视频喵~ (●'◡'●)</p>
        </div>

        <!-- 右侧按钮组 -->
        <div class="right-actions">
            <button class="action-btn" id="nextBtn">⏭️ <span class="action-text">下一个</span></button>
            <label class="auto-play-toggle">
                <div class="switch">
                    <input type="checkbox" id="autoPlaySwitch" checked>
                    <span class="slider"></span>
                </div>
                <span class="toggle-text">自动播放</span>
            </label>
            <button class="action-btn" id="likeBtn">❤️ <span class="action-text">点赞</span></button>
            <button class="action-btn" id="loginBtn" onclick="location.href='profile.php'">👤 <span class="action-text">我的</span></button>
        </div>
    </div>
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
