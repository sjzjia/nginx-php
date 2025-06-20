<?php
// 获取当前日期和时间
$currentDateTime = date('Y-m-d H:i:s');

// 尝试获取 Nginx 版本信息
// Nginx 通常会在 SERVER_SOFTWARE 变量中包含其版本信息
$nginxVersion = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '未知';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nginx + PHP Docker 示例</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding-top: 50px;
        }
        .container {
            background-color: #fff;
            margin: 0 auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        h1 {
            color: #0056b3;
        }
        p {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>你好，来自 Docker 容器！</h1>
        <p>这个页面由 Nginx 提供服务，并由 PHP 渲染。</p>
        <p>当前日期和时间是：<strong id="live-time"><?php echo $currentDateTime; ?></strong></p>
        <p>PHP 版本：<strong><?php echo phpversion(); ?></strong></p>
        <p>Nginx 版本：<strong><?php echo htmlspecialchars($nginxVersion); ?></strong></p>
    </div>

    <script>
        // 获取显示时间的元素
        const liveTimeElement = document.getElementById('live-time');

        // 定义一个函数来更新时间
        function updateLiveTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0'); // 月份从0开始，所以+1
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            // 格式化时间字符串
            const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

            // 更新页面上的时间
            if (liveTimeElement) {
                liveTimeElement.textContent = formattedTime;
            }
        }

        // 首次加载时立即更新时间
        updateLiveTime();

        // 每秒更新一次时间
        setInterval(updateLiveTime, 1000);
    </script>
</body>
</html>
