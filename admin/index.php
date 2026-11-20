<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("未登录或权限不足，请先 <a href='login.php'>登录</a>");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理后台 - 樱舞次元</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 图表功能依赖：chart.js 必须保留 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- 全站统一二次元皮肤（本分发包自带，不引用上级目录） -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-admin">
    <!-- 侧边栏：所有可点击项保持为 div，tab 切换脚本依赖 .sidebar div -->
    <div class="sidebar">
        <h3>后台管理</h3>
        <div class="nav-item active" onclick="switchTab('dashboard')">📊 数据统计</div>
        <div class="nav-item" onclick="switchTab('users')">👥 用户管理</div>
        <div class="nav-item" onclick="switchTab('api_settings')">🔌 接口对接设置</div>
        <div class="sidebar-footer">
            <a href="../index.php">🏠 返回前台</a>
            <a href="login.php?logout=1">🚪 退出登录</a>
        </div>
    </div>

    <div class="main">
        <!-- 数据统计 -->
        <div id="dashboard" class="tab-content active">
            <h2>数据统计</h2>
            <div class="stats-cards">
                <div class="card"><h3>在线人数</h3><p id="stat-online">0</p></div>
                <div class="card"><h3>今日请求</h3><p id="stat-day">0</p></div>
                <div class="card"><h3>总访问量</h3><p id="stat-total">0</p></div>
            </div>

            <div class="flex-row">
                <div class="flex-col">
                    <h3>今日每小时请求曲线</h3>
                    <canvas id="hourlyChart"></canvas>
                </div>
                <div class="flex-col">
                    <h3>近30天每日请求曲线</h3>
                    <canvas id="dailyChart"></canvas>
                </div>
                <div class="flex-col">
                    <h3>近12个月每月请求曲线</h3>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <h3>点赞排行 (TOP 10)</h3>
            <div class="admin-panel">
                <table>
                    <thead><tr><th>视频地址</th><th>点赞数</th></tr></thead>
                    <tbody id="like-list"></tbody>
                </table>
            </div>
        </div>

        <!-- 用户管理 -->
        <div id="users" class="tab-content">
            <h2>用户管理</h2>
            <div class="admin-inline-form">
                <input type="text" id="new_username" placeholder="新用户名">
                <input type="password" id="new_password" placeholder="密码">
                <select id="new_role">
                    <option value="admin">管理员</option>
                    <option value="user">普通用户</option>
                </select>
                <button onclick="addUser()">添加用户</button>
            </div>
            <table>
                <thead><tr><th>ID</th><th>用户名</th><th>角色</th><th>创建时间</th><th>操作</th></tr></thead>
                <tbody id="user-list"></tbody>
            </table>
        </div>

        <!-- 接口对接设置 -->
        <div id="api_settings" class="tab-content">
            <h2>接口对接设置</h2>
            <p class="tip-text">配置你要对接的第三方视频 API。系统会自动拉取视频流展示到前端。</p>
            <div class="form-group">
                <label>API 接口地址</label>
                <input type="text" id="api_url" placeholder="http://...">
            </div>
            <div class="form-group">
                <label>数据返回格式</label>
                <select id="api_format">
                    <option value="json">JSON 格式</option>
                    <option value="text">纯文本格式</option>
                </select>
            </div>
            <div class="form-group">
                <label>视频字段解析</label>
                <input type="text" id="api_key" placeholder="例如: data.url (仅 JSON 需要)">
            </div>
            <button onclick="saveApiSettings()">保存配置</button>
            <span id="save-msg" class="save-msg">保存成功！</span>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.sidebar div').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
            if (tabId === 'dashboard') loadStats();
            if (tabId === 'api_settings') loadApiSettings();
            if (tabId === 'users') loadUsers();
        }

        async function loadUsers() {
            const res = await fetch('api/admin.php?action=users');
            const data = await res.json();
            let html = '';
            data.data.forEach(u => {
                const roleStr = u.role === 'admin' ? '<span style="color:red">管理员</span>' : '普通用户';
                html += `<tr><td>${u.id}</td><td>${u.username}</td><td>${roleStr}</td><td>${u.created_at}</td>
                <td><button style="background:#e74c3c; padding:4px 8px; font-size:12px;" onclick="deleteUser(${u.id})">删除</button></td></tr>`;
            });
            document.getElementById('user-list').innerHTML = html || '<tr><td colspan="5" style="text-align:center;">暂无数据</td></tr>';
        }

        async function addUser() {
            const fd = new FormData();
            fd.append('action', 'add_user');
            fd.append('username', document.getElementById('new_username').value);
            fd.append('password', document.getElementById('new_password').value);
            fd.append('role', document.getElementById('new_role').value);
            
            const res = await fetch('api/admin.php', { method: 'POST', body: fd });
            const data = await res.json();
            alert(data.msg);
            if(data.code === 200) {
                document.getElementById('new_username').value = '';
                document.getElementById('new_password').value = '';
                loadUsers();
            }
        }

        async function deleteUser(id) {
            if(!confirm('确定要删除该用户吗？')) return;
            const fd = new FormData();
            fd.append('action', 'delete_user');
            fd.append('id', id);
            const res = await fetch('api/admin.php', { method: 'POST', body: fd });
            const data = await res.json();
            alert(data.msg);
            if(data.code === 200) loadUsers();
        }

        async function loadStats() {
            const res = await fetch('api/admin.php?action=stats');
            const data = await res.json();
            document.getElementById('stat-online').innerText = data.data.online;
            document.getElementById('stat-day').innerText = data.data.today;
            document.getElementById('stat-total').innerText = data.data.total;
            
            let html = '';
            data.data.likes.forEach(l => {
                html += `<tr><td><a href="${l.video_url}" target="_blank">查看视频</a></td><td>${l.c}</td></tr>`;
            });
            document.getElementById('like-list').innerHTML = html || '<tr><td colspan="2" style="text-align:center;">暂无数据</td></tr>';

            renderCharts(data.data.hourly_chart, data.data.daily_chart, data.data.monthly_chart);
        }

        let hourlyChartInstance = null;
        let dailyChartInstance = null;
        let monthlyChartInstance = null;

        function renderCharts(hourlyData, dailyData, monthlyData) {
            const hourCtx = document.getElementById('hourlyChart').getContext('2d');
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

            // 格式化今日每小时数据
            let hLabels = Array.from({length: 24}, (_, i) => i + ':00');
            let hData = Array(24).fill(0);
            if (hourlyData) hourlyData.forEach(item => { hData[parseInt(item.h)] = parseInt(item.c); });

            if (hourlyChartInstance) {
                hourlyChartInstance.data.datasets[0].data = hData;
                hourlyChartInstance.update();
            } else {
                hourlyChartInstance = new Chart(hourCtx, {
                    type: 'line',
                    data: {
                        labels: hLabels,
                        datasets: [{ label: '请求数', data: hData, borderColor: '#a1c4fd', backgroundColor: 'rgba(161,196,253,0.2)', fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 格式化近30天每日数据
            let dLabels = [];
            let dData = [];
            if (dailyData) dailyData.forEach(item => { dLabels.push(item.d); dData.push(item.c); });

            if (dailyChartInstance) {
                dailyChartInstance.data.labels = dLabels;
                dailyChartInstance.data.datasets[0].data = dData;
                dailyChartInstance.update();
            } else {
                dailyChartInstance = new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: dLabels,
                        datasets: [{ label: '请求数', data: dData, borderColor: '#6c5ce7', backgroundColor: 'rgba(108,92,231,0.2)', fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 格式化近12个月每月数据
            let mLabels = [];
            let mData = [];
            if (monthlyData) monthlyData.forEach(item => { mLabels.push(item.m); mData.push(item.c); });

            if (monthlyChartInstance) {
                monthlyChartInstance.data.labels = mLabels;
                monthlyChartInstance.data.datasets[0].data = mData;
                monthlyChartInstance.update();
            } else {
                monthlyChartInstance = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: mLabels,
                        datasets: [{ label: '请求数', data: mData, borderColor: '#00cec9', backgroundColor: 'rgba(0,206,201,0.2)', fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        }

        async function loadApiSettings() {
            const res = await fetch('api/admin.php?action=get_settings');
            const data = await res.json();
            document.getElementById('api_url').value = data.data.api_url || '';
            document.getElementById('api_format').value = data.data.api_format || 'json';
            document.getElementById('api_key').value = data.data.api_key || '';
        }

        async function saveApiSettings() {
            const fd = new FormData();
            fd.append('action', 'save_settings');
            fd.append('api_url', document.getElementById('api_url').value);
            fd.append('api_format', document.getElementById('api_format').value);
            fd.append('api_key', document.getElementById('api_key').value);
            
            await fetch('api/admin.php', { method: 'POST', body: fd });
            const msg = document.getElementById('save-msg');
            msg.style.display = 'inline';
            setTimeout(() => msg.style.display = 'none', 2000);
        }

        // 初始化
        loadStats();
        // 设置每秒刷新一次统计数据
        setInterval(loadStats, 2000);
    </script>
</body>
</html>
