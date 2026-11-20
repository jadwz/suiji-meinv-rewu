<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>接口文档 - 樱舞次元</title>
    <!-- 和首页共用样式文件，完全统一顶部 nav 与深色二次元主题 -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="page-docs">
    <!-- 和首页完全一致顶部导航（引入同一个nav.php） -->
    <?php include 'includes/nav.php'; ?>

    <div class="page-wrap">
        <div class="doc-card">
            <h1>📚 随机视频 API 接口文档</h1>
            <p>本接口用于获取随机视频资源，所有视频均是网络采集，如有侵权，请联系删除，不承担任何法律责任。</p>

            <h2>1. 视频接口 /api.php</h2>
            <div class="endpoint">
                <span class="badge-get">GET</span> /api.php
            </div>
            <div class="tip-info">
                三种访问模式：<br>
                1. 无任何参数：302直接跳转视频（直接打开视频）<br>
                2. ?format=json：返回JSON数据（首页AJAX调用专用）<br>
                3. ?format=text：返回纯视频链接（第三方嵌入使用）
            </div>
            <table class="param-table">
                <tr>
                    <th>参数名</th>
                    <th>是否必填</th>
                    <th>类型</th>
                    <th>说明</th>
                </tr>
                <tr>
                    <td>format</td>
                    <td>否</td>
                    <td>string</td>
                    <td>可选 json / text；不传参数自动跳转目标地址</td>
                </tr>
            </table>

            <h3>访问示例（直接跳转）</h3>
            <div class="code-block">http://douyin.npcq.cn/api.php</div>

            <h3>format=json 正常返回</h3>
<pre>{
    "code": 200,
    "url": "https://xxx.mp4"
}</pre>

            <h3>format=text 纯链接返回</h3>
<pre>https://xxx.mp4</pre>

            <h3>异常返回（黑名单）</h3>
<pre>{
    "code": 403,
    "msg": "Access Denied: 您的IP或域名已被拉黑"
}</pre>

            <h3>异常返回（无视频缓存）</h3>
<pre>{"code":404,"msg":"暂无视频缓存，请稍后再试"}</pre>

            <hr>

            <h2>2. 统计接口 /api/video/stats.php</h2>
            <div class="endpoint">
                <span class="badge-get">GET</span> /api/video/stats.php
            </div>
            <p>用于获取全站总播放、实时在线人数，首页左上角统计调用。</p>
<pre>{
    "code": 200,
    "data": {
        "total_plays": 15420,
        "online_users": 128
    }
}</pre>

            <hr>

            <h2>3. 第三方嵌入式播放器代码</h2>
            <p>复制下方完整代码，嵌入任意网站即可加载随机视频</p>
            <div class="copy-msg" id="copyTip">✅ 代码复制成功</div>
            <button class="copy-btn" onclick="copyEmbedCode()">一键复制嵌入代码</button>
            <div class="code-block" id="codeBox">&lt;div style="position:relative;max-width:100%"&gt;
&lt;video id="v" controls autoplay playsinline style="width:100%"&gt;&lt;/video&gt;
&lt;div id="l" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#000;color:#fff;padding:8px 12px;display:none"&gt;加载中&lt;/div&gt;
&lt;button onclick="n()" style="position:absolute;top:5px;right:5px;padding:4px;background:#000;color:#fff;border:none;border-radius:50%;cursor:pointer"&gt;⏭️&lt;/button&gt;
&lt;/div&gt;
&lt;script&gt;eval(unescape('%76%61%72%20%76%3D%64%6F%63%75%6D%65%6E%74%2E%67%65%74%45%6C%65%6D%65%6E%74%42%79%49%64%28%22%76%22%29%3B%76%61%72%20%6C%3D%64%6F%63%75%6D%65%6E%74%2E%67%65%74%45%6C%65%6D%65%6E%74%42%79%49%64%28%22%6C%22%29%3B%76%2E%6F%6E%6C%6F%61%64%73%74%61%72%74%3D%76%2E%6F%6E%77%61%69%74%69%6E%67%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%6C%2E%73%74%79%6C%65%2E%64%69%73%70%6C%61%79%3D%22%62%6C%6F%63%6B%22%7D%3B%76%2E%6F%6E%63%61%6E%70%6C%61%79%3D%76%2E%6F%6E%70%6C%61%79%69%6E%67%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%6C%2E%73%74%79%6C%65%2E%64%69%73%70%6C%61%79%3D%22%6E%6F%6E%65%22%7D%3B%77%69%6E%64%6F%77%2E%6E%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%76%2E%73%72%63%3D%22%68%74%74%70%3A%2F%2F%64%6F%75%79%69%6E%2E%6E%70%63%71%2E%63%6E%2F%61%70%69%2E%70%68%70%3F%74%3D%22%2B%44%61%74%65%2E%6E%6F%77%28%29%3B%76%2E%70%6C%61%79%28%29%7D%3B%6E%28%29%3B%76%2E%6F%6E%65%6E%64%65%64%3D%6E'))&lt;/script&gt;</div>

         <div class="preview-box">
                <h3>嵌入效果实时预览</h3>
                <div class="preview-player">
                    <div style="position:relative;max-width:100%">
<video id="preV" controls autoplay playsinline style="width:100%"></video>
<div id="preL" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#000;color:#fff;padding:8px 12px;display:none">加载中</div>
<button onclick="preN()" style="position:absolute;top:5px;right:5px;padding:4px;background:#000;color:#fff;border:none;border-radius:50%;cursor:pointer">⏭️</button>
                    </div>
                    <script>eval(unescape('%76%61%72%20%70%72%65%56%3D%64%6F%63%75%6D%65%6E%74%2E%67%65%74%45%6C%65%6D%65%6E%74%42%79%49%64%28%22%70%72%65%56%22%29%3B%76%61%72%20%70%72%65%4C%3D%64%6F%63%75%6D%65%6E%74%2E%67%65%74%45%6C%65%6D%65%6E%74%42%79%49%64%28%22%70%72%65%4C%22%29%3B%70%72%65%56%2E%6F%6E%6C%6F%61%64%73%74%61%72%74%3D%70%72%65%56%2E%6F%6E%77%61%69%74%69%6E%67%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%70%72%65%4C%2E%73%74%79%6C%65%2E%64%69%73%70%6C%61%79%3D%22%62%6C%6F%63%6B%22%7D%3B%70%72%65%56%2E%6F%6E%63%61%6E%70%6C%61%79%3D%70%72%65%56%2E%6F%6E%70%6C%61%79%69%6E%67%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%70%72%65%4C%2E%73%74%79%6C%65%2E%64%69%73%70%6C%61%79%3D%22%6E%6F%6E%65%22%7D%3B%77%69%6E%64%6F%77%2E%70%72%65%4E%3D%66%75%6E%63%74%69%6F%6E%28%29%7B%70%72%65%56%2E%73%72%63%3D%22%68%74%74%70%3A%2F%2F%64%6F%75%79%69%6E%2E%6E%70%63%71%2E%63%6E%2F%61%70%69%2E%70%68%70%3F%74%3D%22%2B%44%61%74%65%2E%6E%6F%77%28%29%3B%70%72%65%56%2E%70%6C%61%79%28%29%7D%3B%70%72%65%4E%28%29%3B%70%72%65%56%2E%6F%6E%65%6E%64%65%64%3D%70%72%65%4E'))</script>
                </div>
            </div>
            <a href="index.php" class="back-home">← 返回首页</a>
        </div>
    </div>

<script>
// 复制嵌入代码
function copyEmbedCode(){
    const code = document.getElementById('codeBox').innerText;
    const tip = document.getElementById('copyTip');
    const ta = document.createElement('textarea');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    ta.value = code;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    tip.style.display = 'block';
    setTimeout(()=>tip.style.display='none',2500);
}
</script>
</body>
</html>
