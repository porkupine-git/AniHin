<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/extra/tracking.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate unique visitor ID if not exists
if (!isset($_SESSION['visitor_id'])) {
    $_SESSION['visitor_id'] = uniqid('visitor_', true);
}
$visitor_id = $_SESSION['visitor_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?= $websiteTitle ?> - Support Us</title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <meta name="theme-color" content="#202125">
    <link rel="shortcut icon" href="<?= $favicon ?>" type="image/x-icon" />
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/styles.min.css?v=<?= $versionCss ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap">
    </noscript>
    
    <style>
    /* Keep ALL original styles from your site */
    .pizza {
        margin: 2rem auto;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: .6rem;
        text-align: center;
        color: #000;
        font-size: 16px;
        font-weight: 400;
        background-color: #FAACA8;
        background-image: linear-gradient(19deg, #FAACA8 0%, #DDD6F3 100%);
    }

    .pizza a {
        color: #000;
        font-weight: 500;
        text-shadow: 0 1px 0 #fff;
    }

    .pizza a:hover {
        text-shadow: 0 1px 3px #fff;
    }

    .pizza-x {
        max-width: 800px;
        width: calc(100% - 32px);
        min-height: 90px;
    }

    .pizza-y {
        max-width: 400px;
        min-height: 300px;
    }

    .pizza .in-text {
        font-size: 1.5em;
        font-weight: 600;
    }

    .pizza .in-contact {
        font-size: 1em;
    }

    /* Live counter */
    .live-counter {
        position: fixed;
        top: 80px;
        right: 20px;
        background: #1e1e1e;
        border: 1px solid #333;
        border-radius: 50px;
        padding: 10px 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .live-indicator {
        width: 10px;
        height: 10px;
        background: #4caf50;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
        100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
    }
    
    .live-text {
        color: #b0b0b0;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .live-number {
        background: #ff6b6b;
        color: white;
        padding: 4px 10px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 1rem;
        min-width: 45px;
        text-align: center;
    }

    /* Support header - matching your site */
    .support-header {
        background: #202125;
        padding: 40px 0;
        margin-bottom: 30px;
        border-bottom: 4px solid #ff6b6b;
        text-align: center;
    }
    
    .support-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: white;
    }
    
    .support-header p {
        font-size: 1.1rem;
        color: #b0b0b0;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Stats bar - matching your site's block areas */
    .stats-bar {
        background: #1e1e1e;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 40px;
        border: 1px solid #333;
    }
    
    .stat-item {
        text-align: center;
        border-right: 1px solid #333;
    }
    
    .stat-item:last-child {
        border-right: none;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #ff6b6b;
        line-height: 1.2;
    }
    
    .stat-label {
        color: #b0b0b0;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    /* Ad container - simple like original */
    .ad-container {
        background: #252525;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: center;
        border: 1px solid #333;
    }

    /* Message box */
    .message-box {
        background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
        color: white;
        padding: 40px;
        border-radius: 12px;
        margin: 40px 0;
        text-align: center;
        border: 1px solid #333;
    }
    
    .message-box h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: #ff6b6b;
    }

    /* Feature grid */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 40px 0;
    }
    
    .feature-item {
        text-align: center;
        padding: 20px;
        background: #1e1e1e;
        border-radius: 8px;
        border: 1px solid #333;
    }
    
    .feature-item i {
        font-size: 2rem;
        color: #ff6b6b;
        margin-bottom: 15px;
    }

    .btn-home {
        display: inline-block;
        padding: 12px 30px;
        background: #ff6b6b;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-home:hover {
        background: #ff5252;
        color: white;
        text-decoration: none;
    }

    /* Copyright */
    .copyright {
        text-align: center;
        padding: 30px 0;
        color: #808080;
        border-top: 1px solid #333;
        margin-top: 40px;
    }

    @media screen and (max-width: 768px) {
        .stat-item {
            border-right: none;
            border-bottom: 1px solid #333;
            padding: 15px 0;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .feature-grid {
            grid-template-columns: 1fr;
        }
        .live-counter {
            top: 60px;
            right: 10px;
            left: 10px;
            justify-content: center;
        }
    }
    </style>
</head>

<body>
    <div id="wrapper">
        <!-- Header - EXACT same as your site -->
        <div id="header">
            <div class="container">
                <a href="/" id="logo">
                    <img src="<?= $websiteLogo ?>" alt="<?= $websiteTitle ?>" width="165" height="40" decoding="async">
                </a>
                <div class="clearfix"></div>
            </div>
        </div>

        <!-- Live Counter -->
        <div class="live-counter" id="liveCounter">
            <div class="live-indicator"></div>
            <span class="live-text">Watching now</span>
            <span class="live-number" id="liveCount">0</span>
        </div>

        <!-- Support Header -->
        <div class="support-header">
            <div class="container">
                <h1><?= $websiteTitle ?></h1>
                <p>Keep Anime Streaming Free</p>
                <p style="margin-top: 20px;">Every view helps us pay for servers, bandwidth, and keeping the site running</p>
            </div>
        </div>

        <div class="container">
            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="row">
                    <div class="col-md-4 stat-item">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Server Uptime</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-value">10k+</div>
                        <div class="stat-label">Daily Users</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-value">$0</div>
                        <div class="stat-label">Cost to You</div>
                    </div>
                </div>
            </div>

            <!-- Pizza message - USING YOUR EXACT STYLE -->
            <div class="pizza pizza-x">
                <div class="in-text">🎉 Thank You for Your Support! 🎉</div>
                <div class="in-contact">Every view helps us maintain and improve our service</div>
            </div>

            <!-- AD 1 - Script 1 (468x60) -->
            <div class="ad-container">
                <script>
                  atOptions = {
                    'key' : '4919d3570a9fa85e5c0eb033b2f648dd',
                    'format' : 'iframe',
                    'height' : 60,
                    'width' : 468,
                    'params' : {}
                  };
                </script>
                
            </div>

            <!-- AD 2 - Script 2 -->
            <div class="ad-container">
                
            </div>

            <!-- AD 3 - Script 3 -->
            <div class="ad-container">
                
            </div>

            <!-- Message Box -->
            <div class="message-box">
                <h3>Your Support Means Everything ❤️</h3>
                <p>Thanks to viewers like you, we can continue providing free anime streaming</p>
                <p style="margin-top: 20px;"><strong>Every ad view helps us pay for servers, bandwidth, and development</strong></p>
            </div>

            <!-- Keep Anime Free Forever -->
            <div style="background: #1e1e1e; border-radius: 12px; padding: 40px; margin-top: 40px; border: 1px solid #333; text-align: center;">
                <h4 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 20px; color: white;">Keep Anime Free Forever</h4>
                <p style="color: #b0b0b0; max-width: 600px; margin: 0 auto;">We're committed to providing the best anime streaming experience without charging you a dime. Our servers cost thousands each month, but with your support through ads, we can keep going strong!</p>
                
                <div class="feature-grid">
                    <div class="feature-item">
                        <i class="fas fa-server"></i>
                        <h5 style="color: white; margin: 10px 0;">Server Costs</h5>
                        <p style="color: #808080;">$2,500/month for bandwidth</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-code"></i>
                        <h5 style="color: white; margin: 10px 0;">Development</h5>
                        <p style="color: #808080;">Constant updates & features</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-headset"></i>
                        <h5 style="color: white; margin: 10px 0;">Support Team</h5>
                        <p style="color: #808080;">24/7 community support</p>
                    </div>
                </div>
                
                <a href="/home" class="btn-home">
                    <i class="fas fa-home mr-2"></i>Return to Home
                </a>
            </div>

            <!-- Copyright -->
            <div class="copyright">
                <p>© <?= date('Y') ?> <?= $websiteTitle ?>. All rights reserved.</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">
                    Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i> for anime fans everywhere
                </p>
            </div>
        </div>
    </div>

    <!-- Real Live Counter Script -->
    <script>
    const PAGE_URL = window.location.pathname;
    const HEARTBEAT_INTERVAL = 15000;

    function sendHeartbeat() {
        let tabId = sessionStorage.getItem('tab_id');
        if (!tabId) {
            tabId = 'tab_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('tab_id', tabId);
        }
        
        let activeTabs = JSON.parse(localStorage.getItem('active_tabs_' + PAGE_URL)) || {};
        const now = Date.now();
        
        // Clean up old entries
        Object.keys(activeTabs).forEach(key => {
            if (now - activeTabs[key] > 30000) delete activeTabs[key];
        });
        
        activeTabs[tabId] = now;
        localStorage.setItem('active_tabs_' + PAGE_URL, JSON.stringify(activeTabs));
        document.getElementById('liveCount').textContent = Object.keys(activeTabs).length;
        localStorage.setItem('counter_update_' + PAGE_URL, now.toString());
    }

    window.addEventListener('storage', function(e) {
        if (e.key === 'counter_update_' + PAGE_URL) {
            let activeTabs = JSON.parse(localStorage.getItem('active_tabs_' + PAGE_URL)) || {};
            const now = Date.now();
            Object.keys(activeTabs).forEach(key => {
                if (now - activeTabs[key] > 30000) delete activeTabs[key];
            });
            document.getElementById('liveCount').textContent = Object.keys(activeTabs).length;
            localStorage.setItem('active_tabs_' + PAGE_URL, JSON.stringify(activeTabs));
        }
    });

    window.addEventListener('beforeunload', function() {
        let tabId = sessionStorage.getItem('tab_id');
        if (tabId) {
            let activeTabs = JSON.parse(localStorage.getItem('active_tabs_' + PAGE_URL)) || {};
            delete activeTabs[tabId];
            localStorage.setItem('active_tabs_' + PAGE_URL, JSON.stringify(activeTabs));
            localStorage.setItem('counter_update_' + PAGE_URL, Date.now().toString());
        }
    });

    sendHeartbeat();
    setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
    </script>

    <!-- Your original JS -->
    <script src="/js/app.min.js?v=<?= $versionJs ?>"></script>
</body>

</html>