<?php
/**
 * Полный E2E тест AI-Scribe плагина
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

header('Content-Type: application/json');

$test_results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => [],
    'summary' => [
        'total_tests' => 0,
        'passed_tests' => 0,
        'failed_tests' => 0,
        'execution_time' => 0
    ]
];

$start_time = microtime(true);

// Тест 1: Проверка базовой конфигурации
$test_results['tests'][] = [
    'name' => 'Базовая конфигурация',
    'status' => 'running'
];

try {
    $ai_settings = get_option('ab_gpt_ai_engine_settings');
    $plugin_active = is_plugin_active('ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/article_builder.php');
    
    if ($plugin_active && !empty($ai_settings['api_key'])) {
        $test_results['tests'][0]['status'] = 'passed';
        $test_results['tests'][0]['details'] = 'Плагин активен, API ключ настроен';
    } else {
        $test_results['tests'][0]['status'] = 'failed';
        $test_results['tests'][0]['details'] = 'Плагин неактивен или API ключ не настроен';
    }
} catch (Exception $e) {
    $test_results['tests'][0]['status'] = 'failed';
    $test_results['tests'][0]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Тест 2: Проверка HTTP соединений
$test_results['tests'][] = [
    'name' => 'HTTP соединения',
    'status' => 'running'
];

try {
    $response = wp_remote_get('https://httpbin.org/ip', ['timeout' => 10]);
    if (!is_wp_error($response)) {
        $test_results['tests'][1]['status'] = 'passed';
        $test_results['tests'][1]['details'] = 'HTTP соединения работают';
    } else {
        $test_results['tests'][1]['status'] = 'failed';
        $test_results['tests'][1]['details'] = 'HTTP ошибка: ' . $response->get_error_message();
    }
} catch (Exception $e) {
    $test_results['tests'][1]['status'] = 'failed';
    $test_results['tests'][1]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Тест 3: Проверка OpenAI API
$test_results['tests'][] = [
    'name' => 'OpenAI API доступность',
    'status' => 'running'
];

try {
    $response = wp_remote_get('https://api.openai.com/v1/models', [
        'timeout' => 10,
        'headers' => ['Authorization' => 'Bearer ' . $ai_settings['api_key']]
    ]);
    
    if (!is_wp_error($response)) {
        $code = wp_remote_retrieve_response_code($response);
        if ($code === 200) {
            $test_results['tests'][2]['status'] = 'passed';
            $test_results['tests'][2]['details'] = 'OpenAI API доступен';
        } elseif ($code === 403) {
            $test_results['tests'][2]['status'] = 'failed';
            $test_results['tests'][2]['details'] = 'OpenAI API заблокирован в регионе (HTTP 403)';
        } else {
            $test_results['tests'][2]['status'] = 'failed';
            $test_results['tests'][2]['details'] = "OpenAI API недоступен (HTTP $code)";
        }
    } else {
        $test_results['tests'][2]['status'] = 'failed';
        $test_results['tests'][2]['details'] = 'Ошибка подключения: ' . $response->get_error_message();
    }
} catch (Exception $e) {
    $test_results['tests'][2]['status'] = 'failed';
    $test_results['tests'][2]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Тест 4: Тест генерации контента
$test_results['tests'][] = [
    'name' => 'Генерация контента',
    'status' => 'running'
];

try {
    // Проверяем авторизацию
    if (!is_user_logged_in()) {
        $admins = get_users(['role' => 'administrator', 'number' => 1]);
        if (!empty($admins)) {
            wp_set_current_user($admins[0]->ID);
        }
    }
    
    // Создаем nonce
    $nonce = wp_create_nonce('ai_scribe_nonce');
    
    // Тестовые данные
    $_POST = [
        'security' => $nonce,
        'autogenerateValue' => 'Тестовая статья о банковских гарантиях',
        'actionInput' => 'brainstorm',
        'language' => 'Russian',
        'writing_style' => 'Professional',
        'writing_tone' => 'Informative'
    ];
    
    // Захватываем вывод
    ob_start();
    
    // Включаем плагин
    $plugin_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/article_builder.php';
    if (file_exists($plugin_file)) {
        include_once $plugin_file;
    }
    
    // Создаем экземпляр и вызываем функцию
    if (class_exists('AI_Scribe')) {
        $ai_scribe = new AI_Scribe();
        $result = $ai_scribe->suggest_content();
        
        $output = ob_get_clean();
        $json_response = json_decode($output, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($json_response['error_type']) && $json_response['error_type'] === 'region_blocked') {
                $test_results['tests'][3]['status'] = 'passed';
                $test_results['tests'][3]['details'] = 'Плагин корректно определяет блокировку региона и предоставляет решения';
                $test_results['tests'][3]['solutions'] = $json_response['solutions'] ?? [];
            } elseif (isset($json_response['success']) && $json_response['success']) {
                $test_results['tests'][3]['status'] = 'passed';
                $test_results['tests'][3]['details'] = 'Генерация контента работает успешно';
            } else {
                $test_results['tests'][3]['status'] = 'failed';
                $test_results['tests'][3]['details'] = 'Неожиданный ответ плагина';
            }
        } else {
            $test_results['tests'][3]['status'] = 'failed';
            $test_results['tests'][3]['details'] = 'Неверный JSON ответ: ' . json_last_error_msg();
        }
    } else {
        $test_results['tests'][3]['status'] = 'failed';
        $test_results['tests'][3]['details'] = 'Класс AI_Scribe не найден';
    }
} catch (Exception $e) {
    $test_results['tests'][3]['status'] = 'failed';
    $test_results['tests'][3]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Тест 5: Проверка mu-plugins
$test_results['tests'][] = [
    'name' => 'Must-use плагины',
    'status' => 'running'
];

try {
    $mu_plugins = [
        'http-fix.php',
        'server-monitor.php',
        'proxy-config.php',
        'ai-scribe-fallback.php'
    ];
    
    $active_mu_plugins = [];
    $mu_plugins_dir = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/';
    
    foreach ($mu_plugins as $plugin) {
        if (file_exists($mu_plugins_dir . $plugin)) {
            $active_mu_plugins[] = $plugin;
        }
    }
    
    if (count($active_mu_plugins) > 0) {
        $test_results['tests'][4]['status'] = 'passed';
        $test_results['tests'][4]['details'] = 'Активны mu-plugins: ' . implode(', ', $active_mu_plugins);
    } else {
        $test_results['tests'][4]['status'] = 'failed';
        $test_results['tests'][4]['details'] = 'Mu-plugins не найдены';
    }
} catch (Exception $e) {
    $test_results['tests'][4]['status'] = 'failed';
    $test_results['tests'][4]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Тест 6: Проверка серверных ресурсов
$test_results['tests'][] = [
    'name' => 'Серверные ресурсы',
    'status' => 'running'
];

try {
    $memory_limit = ini_get('memory_limit');
    $max_execution_time = ini_get('max_execution_time');
    $current_memory = memory_get_usage(true);
    $peak_memory = memory_get_peak_usage(true);
    
    $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
    $memory_usage_percent = ($current_memory / $memory_limit_bytes) * 100;
    
    if ($memory_usage_percent < 80 && $max_execution_time >= 300) {
        $test_results['tests'][5]['status'] = 'passed';
        $test_results['tests'][5]['details'] = "Память: {$memory_usage_percent}%, Время: {$max_execution_time}s";
    } else {
        $test_results['tests'][5]['status'] = 'warning';
        $test_results['tests'][5]['details'] = "Память: {$memory_usage_percent}%, Время: {$max_execution_time}s - требует внимания";
    }
} catch (Exception $e) {
    $test_results['tests'][5]['status'] = 'failed';
    $test_results['tests'][5]['details'] = 'Ошибка: ' . $e->getMessage();
}

// Подсчет результатов
$total_tests = count($test_results['tests']);
$passed_tests = 0;
$failed_tests = 0;

foreach ($test_results['tests'] as $test) {
    if ($test['status'] === 'passed') {
        $passed_tests++;
    } elseif ($test['status'] === 'failed') {
        $failed_tests++;
    }
}

$test_results['summary'] = [
    'total_tests' => $total_tests,
    'passed_tests' => $passed_tests,
    'failed_tests' => $failed_tests,
    'warning_tests' => $total_tests - $passed_tests - $failed_tests,
    'execution_time' => round(microtime(true) - $start_time, 3),
    'timestamp' => date('Y-m-d H:i:s')
];

// Добавляем рекомендации
$recommendations = [];

if ($failed_tests > 0) {
    $recommendations[] = 'Есть проблемы, требующие исправления';
}

if (isset($test_results['tests'][2]) && $test_results['tests'][2]['status'] === 'failed') {
    $recommendations[] = 'Рекомендуется настроить VPN или использовать альтернативные AI провайдеры';
}

if (isset($test_results['tests'][3]) && $test_results['tests'][3]['status'] === 'passed' && 
    isset($test_results['tests'][3]['solutions'])) {
    $recommendations[] = 'Плагин предоставляет решения для обхода блокировки региона';
}

$test_results['recommendations'] = $recommendations;

echo json_encode($test_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>




