<?php
// env_helper.php - Environment Variable Loader for PHP

if (!function_exists('loadEnv')) {
    function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments
            if (strpos($line, '#') === 0 || empty($line)) {
                continue;
            }

            // Split by the first '='
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);

                // Remove surrounding quotes
                if (preg_match('/^"(.+)"$/', $val, $matches) || preg_match('/^\'(.+)\'$/', $val, $matches)) {
                    $val = $matches[1];
                }

                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
    loadEnv($_SERVER['DOCUMENT_ROOT'] . '/.env');
}
loadEnv(__DIR__ . '/../../.env');

if (!function_exists('getClientIP')) {
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            if (filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
                return $_SERVER['HTTP_X_REAL_IP'];
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
?>
