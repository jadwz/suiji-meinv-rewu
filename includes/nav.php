<?php
// includes/nav.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- 顶部全局导航栏（樱舞次元 · 毛玻璃吸顶） -->
<nav class="main-nav">
    <!-- 品牌区：樱花图标 + 中文站名渐变字 + 英文副标 -->
    <a href="index.php" class="nav-brand">
        <span class="brand-mark" aria-hidden="true">🌸</span>
        <span class="brand-lockup">
            <span class="brand-name">樱舞次元</span>
            <span class="brand-sub">SAKURA DANCE</span>
        </span>
    </a>
    <div class="nav-links">
        <!-- 当前页高亮：沿用原有 PHP 判断，只把内联样式换成 active 类 -->
        <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">🏠 首页</a>
        <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">👤 我的</a>
        <a href="api_docs.php" class="<?php echo $current_page == 'api_docs.php' ? 'active' : ''; ?>">📚 接口</a>
        <a href="app.php" class="<?php echo $current_page == 'app.php' ? 'active' : ''; ?>">📱 下载</a>
    </div>
</nav>

<!-- 全局 Anime Toast 容器 -->
<div id="anime-toast-container" class="anime-toast-container"></div>
<script>
    function showToast(msg) {
        const container = document.getElementById('anime-toast-container');
        if(!container) return;
        const toast = document.createElement('div');
        toast.className = 'anime-toast';
        toast.innerText = msg;
        container.appendChild(toast);
        setTimeout(() => {
            if(container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 3000);
    }
    
    // 覆盖全局 alert
    window.alert = function(msg) {
        showToast(msg);
    };
</script>
