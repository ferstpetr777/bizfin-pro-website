<?php
/*
Plugin Name: Server Resource Monitor
Description: Мониторинг ресурсов сервера для AI плагинов
Version: 1.0
Author: System Admin
*/

// Добавляем мониторинг ресурсов сервера
add_action('wp_ajax_ai_scribe_server_status', 'check_server_resources');
add_action('wp_ajax_nopriv_ai_scribe_server_status', 'check_server_resources');

function check_server_resources() {
    $memory_usage = memory_get_usage(true);
    $memory_limit = ini_get('memory_limit');
    $memory_limit_bytes = return_bytes($memory_limit);
    $memory_percent = ($memory_usage / $memory_limit_bytes) * 100;
    
    $execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    
    $status = array(
        'memory_usage' => $memory_usage,
        'memory_limit' => $memory_limit,
        'memory_percent' => round($memory_percent, 2),
        'execution_time' => round($execution_time, 2),
        'max_execution_time' => ini_get('max_execution_time'),
        'server_load' => sys_getloadavg(),
        'timestamp' => time()
    );
    
    // Предупреждения
    $warnings = array();
    if ($memory_percent > 80) {
        $warnings[] = 'High memory usage: ' . round($memory_percent, 2) . '%';
    }
    if ($execution_time > 240) {
        $warnings[] = 'Long execution time: ' . round($execution_time, 2) . 's';
    }
    
    if (!empty($warnings)) {
        $status['warnings'] = $warnings;
    }
    
    wp_send_json_success($status);
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

// Добавляем проверку ресурсов перед AI запросами
add_action('wp_ajax_al_scribe_suggest_content', 'check_resources_before_ai', 5);
add_action('wp_ajax_nopriv_al_scribe_suggest_content', 'check_resources_before_ai', 5);

function check_resources_before_ai() {
    $memory_usage = memory_get_usage(true);
    $memory_limit = return_bytes(ini_get('memory_limit'));
    $memory_percent = ($memory_usage / $memory_limit) * 100;
    
    // Если память превышает 90%, блокируем запрос
    if ($memory_percent > 90) {
        wp_send_json_error([
            'msg' => 'Server memory usage too high (' . round($memory_percent, 2) . '%). Please try again later.',
            'server_status' => [
                'memory_usage' => round($memory_usage / 1024 / 1024, 2) . 'MB',
                'memory_limit' => ini_get('memory_limit'),
                'memory_percent' => round($memory_percent, 2)
            ]
        ]);
        return;
    }
    
    // Если время выполнения превышает 5 минут, блокируем запрос
    $execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    if ($execution_time > 300) {
        wp_send_json_error([
            'msg' => 'Request timeout. Please try with a shorter prompt.',
            'execution_time' => round($execution_time, 2)
        ]);
        return;
    }
}
?>





