<?php
function check_rate_limit($maxRequests = 10, $timeWindow = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $hash = md5($ip);
    $rateLimitDir = STORAGE_PATH . 'rate_limits/';
    $file = $rateLimitDir . $hash . '.json';

    $currentTime = time();
    $data = ['count' => 0, 'reset_time' => $currentTime + $timeWindow];

    if (file_exists($file)) {
        $content = file_get_contents($file);
        $decoded = json_decode($content, true);
        if ($decoded && $decoded['reset_time'] > $currentTime) {
            $data = $decoded;
        }
    }

    if ($data['count'] >= $maxRequests) {
        throw new Exception('Rate limit exceeded. Please try again later.');
    }

    $data['count']++;
    file_put_contents($file, json_encode($data), LOCK_EX);
}