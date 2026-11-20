<?php
// api/user/captcha.php
require_once __DIR__ . '/../../config.php';

$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// 背景色 (白色)
$bg_color = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bg_color);

// 生成随机验证码字符 (4位随机纯数字)
$chars = '0123456789';
$captcha_str = '';
for ($i = 0; $i < 4; $i++) {
    $captcha_str .= $chars[rand(0, strlen($chars) - 1)];
}

// 存入 session
$_SESSION['captcha'] = $captcha_str;

// 添加干扰线
for ($i = 0; $i < 5; $i++) {
    $line_color = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// 添加干扰点
for ($i = 0; $i < 50; $i++) {
    $pixel_color = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
    imagesetpixel($image, rand(0, $width), rand(0, $height), $pixel_color);
}

// 将字符写入图片
for ($i = 0; $i < 4; $i++) {
    $text_color = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
    $x = 10 + ($width - 20) / 4 * $i + rand(-5, 5);
    $y = rand(5, 15);
    // 使用内置字体
    imagestring($image, 5, $x, $y, $captcha_str[$i], $text_color);
}

// 输出图像
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
imagepng($image);
imagedestroy($image);
?>
