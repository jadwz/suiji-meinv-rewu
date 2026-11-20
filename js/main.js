// js/main.js
const player = document.getElementById('player');
const nextBtn = document.getElementById('nextBtn');
const onlineCount = document.getElementById('onlineCount');
const playCount = document.getElementById('playCount');
const likeBtn = document.getElementById('likeBtn');
const loadingOverlay = document.getElementById('loadingOverlay');
const autoPlaySwitch = document.getElementById('autoPlaySwitch');

let currentVideoUrl = '';
let adTimeout = null;

// 获取随机视频
async function fetchRandomVideo() {
    if (adTimeout) {
        clearTimeout(adTimeout);
        adTimeout = null;
    }
    try {
        const response = await fetch('api.php?format=json');
        const data = await response.json();
        
        if (data.code === 200 && data.url) {
            loadingOverlay.style.display = 'none';
            currentVideoUrl = data.url;
            player.src = currentVideoUrl;
            player.play().catch(e => {
                console.log('自动播放被拦截，需要用户交互', e);
            });
            updateStats();

            // 广告倒计时逻辑
            if (data.is_ad && data.duration && data.duration > 0) {
                adTimeout = setTimeout(() => {
                    fetchRandomVideo();
                }, data.duration * 1000);
            }
        } else if (data.code === 404) {
            // 没有视频，显示加载动画，并过几秒重试
            loadingOverlay.style.display = 'flex';
            setTimeout(fetchRandomVideo, 3000);
        }
    } catch (error) {
        console.error('获取视频失败:', error);
        // 出错也显示加载并重试
        loadingOverlay.style.display = 'flex';
        setTimeout(fetchRandomVideo, 3000);
    }
}

// 更新统计数据
async function updateStats() {
    try {
        const response = await fetch(`api.php?action=stats&t=${new Date().getTime()}`);
        const data = await response.json();
        if (data.code === 200) {
            onlineCount.innerText = data.data.online;
            playCount.innerText = data.data.total;
        }
    } catch (error) {
        console.error('获取统计数据失败:', error);
    }
}

// 点赞功能 (API 请求)
async function submitLike() {
    if (!currentVideoUrl) return;
    try {
        const formData = new FormData();
        formData.append('video_url', currentVideoUrl);
        const response = await fetch('api.php?action=like', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.code === 200) {
            // 不再这里弹窗，避免干扰双击的静默体验
        } else {
            showToast(data.msg || '点赞失败');
        }
    } catch (error) {
        console.error('点赞失败:', error);
    }
}

// 触发红心动画
function triggerHeartAnimation(e) {
    const heart = document.createElement('div');
    heart.className = 'heart-animation';
    heart.innerText = '❤️';
    
    // 如果有点击坐标，则在点击位置显示，否则居中显示
    if (e && e.clientX && e.clientY) {
        heart.style.left = e.clientX + 'px';
        heart.style.top = e.clientY + 'px';
    }
    
    document.body.appendChild(heart);
    
    setTimeout(() => {
        if(document.body.contains(heart)) {
            document.body.removeChild(heart);
        }
    }, 2000);
}

// 淡化提示功能
function showSubtleHint(text) {
    const hint = document.createElement('div');
    hint.innerText = text;
    hint.style.position = 'fixed';
    hint.style.bottom = '15%';
    hint.style.left = '50%';
    hint.style.transform = 'translateX(-50%)';
    hint.style.background = 'rgba(0,0,0,0.5)';
    hint.style.color = '#fff';
    hint.style.padding = '6px 12px';
    hint.style.borderRadius = '15px';
    hint.style.fontSize = '12px';
    hint.style.pointerEvents = 'none';
    hint.style.zIndex = '9999';
    hint.style.opacity = '0';
    hint.style.transition = 'opacity 0.5s';
    document.body.appendChild(hint);
    
    setTimeout(() => hint.style.opacity = '1', 10);
    setTimeout(() => {
        hint.style.opacity = '0';
        setTimeout(() => hint.remove(), 500);
    }, 2000);
}

likeBtn.addEventListener('click', () => {
    // 按钮动画效果
    likeBtn.style.transform = 'scale(1.3)';
    setTimeout(() => { likeBtn.style.transform = 'scale(1)'; }, 200);
    triggerHeartAnimation();
    submitLike();
    showSubtleHint('双击画面也能点赞哦～');
});

// 点击下一首
nextBtn.addEventListener('click', () => {
    showSubtleHint('上下滑动可以切换视频哦～');
    fetchRandomVideo();
});

// 视频播放完毕自动下一首 (根据开关状态)
player.addEventListener('ended', () => {
    if (autoPlaySwitch && autoPlaySwitch.checked) {
        fetchRandomVideo();
    }
});

// 双击点赞及滑动交互逻辑
let lastTapTime = 0;
let touchStartY = 0;

player.addEventListener('touchstart', (e) => {
    touchStartY = e.changedTouches[0].screenY;
}, { passive: true });

player.addEventListener('touchend', (e) => {
    const touchEndY = e.changedTouches[0].screenY;
    
    // 滑动判断：上下滑动超过 50px 则切换视频
    if (touchStartY - touchEndY > 50 || touchEndY - touchStartY > 50) {
        fetchRandomVideo();
        return;
    }
    
    // 双击判断
    const currentTime = new Date().getTime();
    const tapLength = currentTime - lastTapTime;
    if (tapLength < 300 && tapLength > 0) {
        // 是双击
        triggerHeartAnimation(e.changedTouches[0]);
        submitLike();
        e.preventDefault(); // 阻止默认的双击放大等行为
    } else {
        // 单击：暂停或播放
        setTimeout(() => {
            // 确保不是双击的第一下才触发暂停
            const newCurrentTime = new Date().getTime();
            if (newCurrentTime - lastTapTime >= 300) {
                if (player.paused) {
                    player.play();
                } else {
                    player.pause();
                }
            }
        }, 300);
    }
    lastTapTime = currentTime;
});

// PC 端双击
player.addEventListener('dblclick', (e) => {
    triggerHeartAnimation(e);
    submitLike();
});

// 初始化
fetchRandomVideo();
// 每隔10秒刷新一次统计 (也是维持心跳)
setInterval(updateStats, 10000);
