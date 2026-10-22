<?php
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function write_log($message, $level = 'INFO') {
    $logFile = LOGS_PATH . 'app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($formatted, 3, $logFile);
}