<?php
/**
 * Комплексная диагностика AI-Scribe плагина
 * Тестирует все аспекты работы плагина для выявления проблем
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Устанавливаем заголовки для AJAX
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Функция для логирования
function log_diagnostic($message) {
    error_log("AI-Scribe Diagnostic: " . $message);
}

// Функция для безопасного выполнения
function safe_execute($function, $description) {
    try {
        $start_time = microtime(true);
        $start_memory = memory_get_usage(true);
        
        $result = $function();
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);
        
        return [
            'success' => true,
            'description' => $description,
            'result' => $result,
            'execution_time' => round($end_time - $start_time, 3),
            'memory_used' => round(($end_memory - $start_memory) / 1024 / 1024, 2) . 'MB'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'description' => $description,
            'error' => $e->getMessage(),
            'execution_time' => 0,
            'memory_used' => '0MB'
        ];
    }
}

$diagnostics = [];

// Тест 1: Проверка настроек сервера
$diagnostics[] = safe_execute(function() {
    return [
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time'),
        'current_memory' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
        'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
    ];
}, 'Проверка настроек сервера');

// Тест 2: Проверка AI-Scribe плагина
$diagnostics[] = safe_execute(function() {
    $plugin_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/article_builder.php';
    
    if (!file_exists($plugin_file)) {
        throw new Exception('AI-Scribe плагин не найден');
    }
    
    // Проверяем, что плагин активен
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    $plugin_path = 'ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/article_builder.php';
    $is_active = is_plugin_active($plugin_path);
    
    return [
        'file_exists' => true,
        'file_size' => round(filesize($plugin_file) / 1024 / 1024, 2) . 'MB',
        'last_modified' => date('Y-m-d H:i:s', filemtime($plugin_file)),
        'is_active' => $is_active,
        'plugin_path' => $plugin_path
    ];
}, 'Проверка AI-Scribe плагина');

// Тест 3: Проверка настроек AI
$diagnostics[] = safe_execute(function() {
    $ai_settings = get_option('ab_gpt_ai_engine_settings');
    $content_settings = get_option('ab_gpt_content_settings');
    
    if (!$ai_settings) {
        throw new Exception('Настройки AI не найдены');
    }
    
    return [
        'api_key_configured' => !empty($ai_settings['api_key']),
        'api_key_length' => strlen($ai_settings['api_key'] ?? ''),
        'model' => $ai_settings['model'] ?? 'не установлена',
        'content_settings' => $content_settings ? 'настроены' : 'не настроены'
    ];
}, 'Проверка настроек AI');

// Тест 4: Проверка HTTP соединений
$diagnostics[] = safe_execute(function() {
    $test_urls = [
        'https://api.openai.com/v1/models',
        'https://api.anthropic.com/v1/messages'
    ];
    
    $results = [];
    
    foreach ($test_urls as $url) {
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            ]
        ]);
        
        if (is_wp_error($response)) {
            $results[$url] = [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $results[$url] = [
                'success' => true,
                'http_code' => $code,
                'expected' => in_array($code, [403, 401]) ? 'OK (без API ключа)' : 'Неожиданный код'
            ];
        }
    }
    
    return $results;
}, 'Проверка HTTP соединений');

// Тест 5: Проверка mu-plugins
$diagnostics[] = safe_execute(function() {
    $mu_plugins = [
        'http-fix.php' => '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/http-fix.php',
        'server-monitor.php' => '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/server-monitor.php'
    ];
    
    $results = [];
    foreach ($mu_plugins as $name => $path) {
        $results[$name] = [
            'exists' => file_exists($path),
            'size' => file_exists($path) ? round(filesize($path) / 1024, 2) . 'KB' : 'N/A',
            'last_modified' => file_exists($path) ? date('Y-m-d H:i:s', filemtime($path)) : 'N/A'
        ];
    }
    
    return $results;
}, 'Проверка mu-plugins');

// Тест 6: Тест AJAX функций AI-Scribe
$diagnostics[] = safe_execute(function() {
    // Проверяем, что AJAX действия зарегистрированы
    global $wp_filter;
    
    $ajax_actions = [
        'wp_ajax_al_scribe_suggest_content',
        'wp_ajax_al_scribe_content_data',
        'wp_ajax_get_article'
    ];
    
    $results = [];
    foreach ($ajax_actions as $action) {
        $results[$action] = isset($wp_filter[$action]);
    }
    
    return $results;
}, 'Проверка AJAX функций');

// Тест 7: Проверка nonce и безопасности
$diagnostics[] = safe_execute(function() {
    $nonce = wp_create_nonce('ai_scribe_nonce');
    $verify = wp_verify_nonce($nonce, 'ai_scribe_nonce');
    
    return [
        'nonce_created' => !empty($nonce),
        'nonce_verified' => $verify,
        'nonce_length' => strlen($nonce)
    ];
}, 'Проверка nonce и безопасности');

// Тест 8: Проверка базы данных
$diagnostics[] = safe_execute(function() {
    global $wpdb;
    
    $tables = [
        $wpdb->options,
        $wpdb->posts,
        $wpdb->users
    ];
    
    $results = [];
    foreach ($tables as $table) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $results[basename($table)] = [
            'exists' => $count !== null,
            'count' => $count
        ];
    }
    
    return $results;
}, 'Проверка базы данных');

// Тест 9: Симуляция запроса AI-Scribe
$diagnostics[] = safe_execute(function() {
    // Получаем настройки AI
    $ai_settings = get_option('ab_gpt_ai_engine_settings');
    
    if (empty($ai_settings['api_key'])) {
        throw new Exception('API ключ не настроен');
    }
    
    // Создаем тестовый запрос
    $test_data = [
        'model' => $ai_settings['model'] ?? 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Test message for diagnostic'
            ]
        ],
        'max_tokens' => 10
    ];
    
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $ai_settings['api_key'],
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($test_data)
    ]);
    
    if (is_wp_error($response)) {
        return [
            'success' => false,
            'error' => $response->get_error_message()
        ];
    }
    
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    return [
        'success' => true,
        'http_code' => $code,
        'response_length' => strlen($body),
        'is_valid_response' => $code === 200 && !empty($body)
    ];
}, 'Тест реального API запроса');

// Собираем общие результаты
$summary = [
    'total_tests' => count($diagnostics),
    'passed_tests' => count(array_filter($diagnostics, function($test) { return $test['success']; })),
    'failed_tests' => count(array_filter($diagnostics, function($test) { return !$test['success']; })),
    'total_execution_time' => array_sum(array_column($diagnostics, 'execution_time')),
    'timestamp' => date('Y-m-d H:i:s')
];

$result = [
    'summary' => $summary,
    'diagnostics' => $diagnostics
];

// Логируем результаты
log_diagnostic("Diagnostic completed: {$summary['passed_tests']}/{$summary['total_tests']} tests passed");

// Возвращаем результат
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>





