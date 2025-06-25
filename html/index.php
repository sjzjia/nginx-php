<?php
// 获取系统信息
$currentDateTime = date('Y-m-d H:i:s');
$nginxVersion = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '未知';
$phpVersion = phpversion();
$hostname = gethostname();

// 获取运行时间
$uptimeData = @file_get_contents('/proc/uptime');
if ($uptimeData !== false) {
    $uptimeParts = explode(' ', $uptimeData);
    $uptime = round($uptimeParts[0] / 86400, 2);
} else {
    $uptime = '未知';
}

// 尝试获取更精确的Nginx版本
if (function_exists('shell_exec')) {
    $nginxVersionDetailed = @shell_exec('nginx -v 2>&1');
    if (!empty($nginxVersionDetailed)) {
        $nginxVersion = trim($nginxVersionDetailed);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>容器监控面板 | Nginx + PHP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5649c0;
            --primary-light: #a29bfe;
            --secondary: #00cec9;
            --secondary-dark: #00a8a5;
            --success: #00b894;
            --danger: #d63031;
            --warning: #fdcb6e;
            --info: #0984e3;
            --light: #f8f9fa;
            --dark: #2d3436;
            --gray: #636e72;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-gradient: linear-gradient(to bottom right, rgba(255,255,255,0.95), rgba(255,255,255,0.98));
            --glass-gradient: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            --border-radius-lg: 16px;
            --border-radius-sm: 10px;
            --box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient) fixed;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .dashboard {
            width: 100%;
            max-width: 500px;
            display: grid;
            gap: 20px;
        }
        
        .status-card {
            background: var(--card-gradient);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow);
            padding: 25px;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        
        .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.3);
            position: relative;
            overflow: hidden;
            transform: rotate(45deg);
        }
        
        .logo i {
            color: white;
            font-size: 2rem;
            z-index: 1;
            transform: rotate(-45deg);
        }
        
        .app-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
        }
        
        .app-description {
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 300;
            letter-spacing: 0.3px;
        }
        
        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
            position: relative;
            padding-left: 15px;
        }
        
        .status-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 18px;
            width: 4px;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
            border-radius: 2px;
        }
        
        .status-badge {
            background: rgba(0, 206, 201, 0.15);
            color: var(--secondary-dark);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        
        .status-badge i {
            margin-right: 6px;
            font-size: 0.6rem;
        }
        
        .time-card {
            background: var(--glass-gradient);
            border-radius: var(--border-radius-sm);
            padding: 18px;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        
        .time-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .time-title {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            letter-spacing: 0.3px;
        }
        
        .time-title i {
            margin-right: 8px;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .time-value {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        
        .time-desc {
            font-size: 0.75rem;
            color: var(--gray);
            opacity: 0.8;
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .system-info {
            margin-top: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            align-items: center;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            align-items: center;
        }
        
        .info-label i {
            margin-right: 8px;
            font-size: 0.9rem;
            color: var(--primary);
            width: 18px;
            text-align: center;
        }
        
        .info-value {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--dark);
            background: rgba(0, 0, 0, 0.03);
            padding: 4px 10px;
            border-radius: 6px;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .status-card {
                padding: 20px;
            }
            
            .time-value {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="status-card">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-server"></i>
                </div>
                <h1 class="app-name">容器监控中心</h1>
                <p class="app-description">轻量级容器监控面板</p>
            </div>
            
            <div class="status-header">
                <h2 class="status-title">系统信息</h2>
                <div class="status-badge">
                    <i class="fas fa-circle"></i>
                    <span>运行正常</span>
                </div>
            </div>
            
            <div class="time-card">
                <div class="time-title">
                    <i class="fas fa-clock"></i>
                    <span>当前时间</span>
                </div>
                <div class="time-value" id="live-time"><?php echo $currentDateTime; ?></div>
                <div class="time-desc">服务器系统时间 (实时更新)</div>
            </div>
            
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-hourglass-half"></i>
                        <span>运行时间</span>
                    </span>
                    <span class="info-value"><?php echo is_numeric($uptime) ? round($uptime, 2) . ' 天' : $uptime; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fab fa-php"></i>
                        <span>PHP版本</span>
                    </span>
                    <span class="info-value"><?php echo $phpVersion; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-globe"></i>
                        <span>Nginx版本</span>
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($nginxVersion); ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 实时更新时间
        function updateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false
            };
            
            const formattedTime = now.toLocaleString('zh-CN', options)
                .replace(/\//g, '-')
                .replace(/(\d{4})-(\d{2})-(\d{2})/, '$1-$2-$3');
            
            document.getElementById('live-time').textContent = formattedTime;
            
            // 随机动画效果
            if (Math.random() > 0.8) {
                document.querySelector('.time-card').classList.add('animate-pulse');
                setTimeout(() => document.querySelector('.time-card').classList.remove('animate-pulse'), 2000);
            }
        }

        // 初始更新并设置定时器
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
