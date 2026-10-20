<?php
// maintenance.php - Premium Animated Maintenance Page
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';

$msg = 'We are performing scheduled database maintenance. We will be back shortly!';
$countdown = '';

$res = $conn->query("SELECT value FROM site_config WHERE name = 'maintenance_message' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $msg = htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8');
}
$res = $conn->query("SELECT value FROM site_config WHERE name = 'maintenance_countdown' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $countdown = htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Scheduled Maintenance - <?= htmlspecialchars($websiteTitle) ?></title>
    <link rel="shortcut icon" href="<?= $favicon ?>" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </noscript>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000000;
            font-family: 'Poppins', sans-serif;
            color: #e5e5e5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glow Backgrounds */
        .ambient-glow-1 {
            position: absolute;
            top: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
            pointer-events: none;
        }

        .maintenance-card {
            background: rgba(18, 18, 18, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%;
            max-width: 650px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 40px rgba(255, 255, 255, 0.02);
            z-index: 2;
            position: relative;
            margin: 20px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo-container img {
            max-height: 50px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
        }

        .icon-wrapper {
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }

        .cog-main {
            font-size: 80px;
            color: #ffffff;
            filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.3));
            animation: spin 8s linear infinite;
        }

        .cog-sub {
            font-size: 40px;
            color: rgba(255, 255, 255, 0.5);
            position: absolute;
            bottom: -5px;
            right: -10px;
            animation: spin-reverse 6s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @keyframes spin-reverse {
            100% { transform: rotate(-360deg); }
        }

        h1 {
            color: #ffffff;
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        p.message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 35px;
            color: #a0a0a0;
        }

        /* Countdown Timer */
        .countdown-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            min-width: 90px;
            padding: 15px 10px;
            box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }

        .countdown-item:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.07);
        }

        .countdown-num {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.1;
        }

        .countdown-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888888;
            margin-top: 5px;
            font-weight: 600;
        }

        /* Progress Bar */
        .progress-container {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            height: 8px;
            width: 80%;
            margin: 0 auto 40px auto;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ffffff 0%, rgba(255, 255, 255, 0.4) 100%);
            width: 65%; /* default fallback width */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
            animation: pulse-progress 2s infinite ease-in-out;
        }

        @keyframes pulse-progress {
            0% { opacity: 0.7; }
            50% { opacity: 1; }
            100% { opacity: 0.7; }
        }

        /* Social / Discord Button */
        .discord-btn {
            background: #ffffff;
            color: #000000;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
            font-size: 15px;
            margin-bottom: 30px;
        }

        .discord-btn:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.35);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 25px;
        }

        .social-icon {
            color: #888888;
            font-size: 20px;
            transition: all 0.3s;
        }

        .social-icon:hover {
            color: #ffffff;
            transform: scale(1.15);
        }

        .social-icon.telegram:hover { color: #0088cc; }
        .social-icon.reddit:hover { color: #ff4500; }
        .social-icon.instagram:hover { color: #e1306c; }

        @media (max-width: 500px) {
            .maintenance-card {
                padding: 35px 20px;
            }
            .countdown-container {
                gap: 8px;
            }
            .countdown-item {
                min-width: 70px;
                padding: 10px 5px;
            }
            .countdown-num {
                font-size: 20px;
            }
            .countdown-label {
                font-size: 9px;
            }
        }

        @media (min-width: 1200px) {
            .maintenance-card {
                max-width: 780px;
                padding: 65px 55px;
            }
            h1 {
                font-size: 38px;
            }
            p.message {
                font-size: 18px;
            }
            .countdown-num {
                font-size: 36px;
            }
            .countdown-item {
                min-width: 110px;
                padding: 20px 15px;
            }
            .cog-main {
                font-size: 100px;
            }
            .cog-sub {
                font-size: 50px;
            }
        }

        @media (min-width: 1900px) {
            .maintenance-card {
                max-width: 960px;
                padding: 85px 75px;
                border-radius: 32px;
            }
            h1 {
                font-size: 46px;
                margin-bottom: 25px;
            }
            p.message {
                font-size: 22px;
                margin-bottom: 45px;
            }
            .countdown-num {
                font-size: 46px;
            }
            .countdown-item {
                min-width: 140px;
                padding: 25px 20px;
                border-radius: 20px;
            }
            .cog-main {
                font-size: 120px;
            }
            .cog-sub {
                font-size: 60px;
            }
            .discord-btn {
                padding: 20px 40px;
                font-size: 18px;
                border-radius: 35px;
            }
            .social-icon {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="maintenance-card">
        <div class="logo-container">
            <img src="<?= $websiteLogo ?>" alt="<?= htmlspecialchars($websiteTitle) ?>">
        </div>

        <div class="icon-wrapper">
            <i class="fas fa-cog cog-main"></i>
            <i class="fas fa-cog cog-sub"></i>
        </div>

        <h1>Under Maintenance</h1>
        <p class="message"><?= $msg ?></p>

        <?php if (!empty($countdown)): ?>
        <div class="countdown-container" id="countdown-timer" data-time="<?= $countdown ?>">
            <div class="countdown-item">
                <div class="countdown-num" id="days">00</div>
                <div class="countdown-label">Days</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-num" id="hours">00</div>
                <div class="countdown-label">Hours</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-num" id="minutes">00</div>
                <div class="countdown-label">Minutes</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-num" id="seconds">00</div>
                <div class="countdown-label">Seconds</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>

        <div>
            <a href="<?= $discord ?>" target="_blank" class="discord-btn">
                <i class="fab fa-discord"></i>
                Join our Discord community
            </a>
        </div>

        <div class="social-links">
            <a href="<?= $telegram ?>" target="_blank" class="social-icon telegram" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
            <a href="<?= $reddit ?>" target="_blank" class="social-icon reddit" title="Reddit"><i class="fab fa-reddit-alien"></i></a>
            <a href="<?= $instagram ?>" target="_blank" class="social-icon instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timerContainer = document.getElementById('countdown-timer');
            if (!timerContainer) return;

            const targetTimeStr = timerContainer.getAttribute('data-time').trim().replace(' ', 'T');
            const targetDate = new Date(targetTimeStr).getTime();

            const daysVal = document.getElementById('days');
            const hoursVal = document.getElementById('hours');
            const minutesVal = document.getElementById('minutes');
            const secondsVal = document.getElementById('seconds');

            function updateTimer() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    daysVal.textContent = "00";
                    hoursVal.textContent = "00";
                    minutesVal.textContent = "00";
                    secondsVal.textContent = "00";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysVal.textContent = String(days).padStart(2, '0');
                hoursVal.textContent = String(hours).padStart(2, '0');
                minutesVal.textContent = String(minutes).padStart(2, '0');
                secondsVal.textContent = String(seconds).padStart(2, '0');
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        });
    </script>
</body>
</html>
