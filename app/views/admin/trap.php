<?php
// Get system info
$ipAddress = get_client_ip();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$time = date('Y-m-d H:i:s T');

// Simple OS detection
$os = "Unknown OS";
if (preg_match('/windows|win32/i', $userAgent)) $os = 'Windows';
elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'macOS';
elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';
elseif (preg_match('/iphone|ipad/i', $userAgent)) $os = 'iOS';
elseif (preg_match('/android/i', $userAgent)) $os = 'Android';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 ការចូលប្រើប្រាស់ត្រូវបានបដិសេធ (ACCESS DENIED) 🚨</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23ef4444'/%3E%3Ctext x='16' y='22' font-family='Inter' font-size='18' font-weight='bold' fill='white' text-anchor='middle'%3E!%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-950: #450a0a;
            --bg-dark: #030712;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Kantumruy Pro', 'Share Tech Mono', monospace;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }

        /* Scanline and Hacker effect */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                rgba(18, 16, 16, 0) 50%, 
                rgba(0, 0, 0, 0.25) 50%
            );
            background-size: 100% 4px;
            z-index: 10;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, transparent 40%, rgba(3, 7, 18, 0.8) 100%);
            z-index: 5;
            pointer-events: none;
        }

        /* Radar scan effect */
        .radar-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 15px 5px var(--red-500);
            z-index: 4;
            animation: radar 6s linear infinite;
        }

        @keyframes radar {
            0% { top: 0; }
            100% { top: 100vh; }
        }

        .container {
            width: 100%;
            max-width: 580px;
            background: rgba(239, 68, 68, 0.02);
            border: 2px solid rgba(239, 68, 68, 0.25);
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.15), inset 0 0 20px rgba(239, 68, 68, 0.05);
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 20;
            position: relative;
            animation: glitch-border 2s infinite alternate;
        }

        @keyframes glitch-border {
            0%, 100% { border-color: rgba(239, 68, 68, 0.25); }
            50% { border-color: rgba(239, 68, 68, 0.5); }
        }

        .warning-icon {
            font-size: 4rem;
            color: var(--red-500);
            text-shadow: 0 0 15px var(--red-500);
            margin-bottom: 16px;
            animation: pulse 1s infinite alternate;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.08); }
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--red-500);
            margin-bottom: 12px;
            text-shadow: 0 0 8px rgba(239, 68, 68, 0.3);
        }

        .alert-title {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b6b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .alert-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        /* Tracker grid */
        .tracker-card {
            background: rgba(3, 7, 18, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
            font-family: 'Share Tech Mono', monospace;
        }

        .tracker-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
            font-size: 0.85rem;
        }

        .tracker-row:last-child {
            border-bottom: none;
        }

        .tracker-label {
            color: var(--text-secondary);
        }

        .tracker-value {
            color: var(--red-500);
            font-weight: 700;
            text-align: right;
            word-break: break-all;
        }

        .exposure-timer {
            margin: 20px 0;
            padding: 12px;
            background: rgba(239, 68, 68, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.1);
        }

        .timer-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .timer-countdown {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--red-500);
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
            font-family: 'Share Tech Mono', monospace;
        }

        .apologize-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--red-500), var(--red-600));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .apologize-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
            background: linear-gradient(135deg, var(--red-600), var(--red-500));
        }

        .apologize-btn svg {
            fill: currentColor;
            width: 20px;
            height: 20px;
        }

        .disclaimer {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 16px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<div class="radar-line"></div>

<div class="container">
    <div class="warning-icon">💀</div>
    <h1>ប្រយ័ត្នជាប់គុកវើយ!</h1>
    <div class="alert-title">SYSTEM SECURITY COMPROMISED</div>
    <div class="alert-desc">
        លោកអ្នកកំពុងព្យាយាមលួចចូលប្រព័ន្ធគ្រប់គ្រង (Admin Dashboard) ដោយគ្មានការអនុញ្ញាត។ ព័ត៌មានឧបករណ៍ និងទីតាំងបណ្តាញរបស់អ្នកត្រូវបានរក្សាទុកក្នុងប្រព័ន្ធសុវត្ថិភាព។
    </div>

    <!-- Attacker Data Display -->
    <div class="tracker-card">
        <div class="tracker-row">
            <span class="tracker-label">IP ADDRESS:</span>
            <span class="tracker-value" id="tracker-ip"><?= e($ipAddress) ?></span>
        </div>
        <div class="tracker-row">
            <span class="tracker-label">ESTIMATED LOCATION:</span>
            <span class="tracker-value" id="tracker-location">កំពុងស្វែងរកទីតាំង (Locating...)...</span>
        </div>
        <div class="tracker-row">
            <span class="tracker-label">OPERATING SYSTEM:</span>
            <span class="tracker-value"><?= e($os) ?></span>
        </div>
        <div class="tracker-row">
            <span class="tracker-label">ACCESS TIME:</span>
            <span class="tracker-value"><?= e($time) ?></span>
        </div>
        <div class="tracker-row">
            <span class="tracker-label">SECURITY LOG STATE:</span>
            <span class="tracker-value" style="color: var(--red-500); animation: pulse 0.5s infinite alternate;">REPORTED TO AUTHORITIES</span>
        </div>
    </div>

    <!-- Countdown Timer -->
    <div class="exposure-timer">
        <div class="timer-label">ពេលវេលារហូតដល់ផ្សព្វផ្សាយជាសាធារណៈ (Time to exposure)</div>
        <div class="timer-countdown" id="countdown-timer">05:00</div>
    </div>

    <!-- CTA button to Apologize -->
    <a href="<?= e(ADMIN_TELEGRAM) ?>" target="_blank" class="apologize-btn">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.11.02-1.93 1.23-5.46 3.62-.51.35-.98.53-1.39.51-.46-.01-1.35-.26-2.01-.48-.81-.27-1.46-.42-1.4-.88.03-.24.36-.49.99-.75 3.88-1.69 6.46-2.8 7.74-3.32 3.68-1.5 4.44-1.76 4.94-1.77.11 0 .36.03.52.16.13.11.17.27.19.38 0 .09.01.23.01.35z"/>
        </svg>
        <span>បើមិនចង់ខ្មាស់គេ ឆាតមកសុំទោសជាបន្ទាន់!</span>
    </a>

    <div class="disclaimer">
        * ចុចប៊ូតុងខាងលើដើម្បីសុំទោសម្ចាស់គេហទំព័រ ដើម្បីលុបព័ត៌មានរបស់អ្នកចេញពីកំណត់ហេតុសន្តិសុខ។
    </div>
</div>

<script>
    // Geolocation Fetch client-side (safe and precise!)
    async function fetchLocation() {
        const locEl = document.getElementById('tracker-location');
        try {
            // Try ipwho.is first (highly detailed, supports CORS)
            const response = await fetch('https://ipwho.is/');
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.city && data.country) {
                    const isp = data.connection ? data.connection.isp : '';
                    locEl.textContent = `${data.city}, ${data.country} ${isp ? '(' + isp + ')' : ''}`;
                    locEl.style.color = '#34d399'; // green text when found!
                    if (data.ip) {
                        document.getElementById('tracker-ip').textContent = data.ip;
                    }
                    return;
                }
            }
        } catch (e) {
            console.log("ipwho.is failed, trying backup...");
        }

        try {
            // Backup with ipinfo.io (reliable, supports CORS)
            const response = await fetch('https://ipinfo.io/json');
            if (response.ok) {
                const data = await response.json();
                if (data.city && data.country) {
                    locEl.textContent = `${data.city}, ${data.country} (${data.org || 'Unknown ISP'})`;
                    locEl.style.color = '#34d399';
                    if (data.ip) {
                        document.getElementById('tracker-ip').textContent = data.ip;
                    }
                    return;
                }
            }
        } catch (e) {
            console.log("Backup geo failed");
        }

        // Fallback for localhost / offline
        locEl.textContent = "<?= $ipAddress === '127.0.0.1' ? 'Localhost (Internal LAN Network)' : 'មិនអាចកំណត់បាន (Undetected)' ?>";
        locEl.style.color = '#f87171';
    }

    // Countdown Timer (5 minutes)
    function startTimer(duration, display) {
        let timer = duration, minutes, seconds;
        const interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = minutes + ":" + seconds;

            if (--timer < 0) {
                // Keep at 00:00
                clearInterval(interval);
                display.textContent = "00:00";
            }
        }, 1000);
    }

    window.onload = function () {
        fetchLocation();
        const fiveMinutes = 60 * 5,
            display = document.querySelector('#countdown-timer');
        startTimer(fiveMinutes, display);
    };
</script>

</body>
</html>
