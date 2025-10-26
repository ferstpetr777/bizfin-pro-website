<?php
/**
 * Тест скорости и стабильности голландского прокси
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ТЕСТ СКОРОСТИ И СТАБИЛЬНОСТИ ГОЛЛАНДСКОГО ПРОКСИ ===\n\n";

$proxy_url = 'http://89.110.80.198:8889';
$test_urls = [
    'https://api.openai.com/v1/models',
    'https://api.openai.com/v1/chat/completions',
    'https://api.openai.com/v1/images/generations'
];

$total_tests = 5; // Уменьшаем количество тестов для быстрого результата
$successful_tests = 0;
$total_time = 0;
$response_times = [];

echo "Выполняем $total_tests тестов для каждого API...\n\n";

foreach ($test_urls as $url) {
    echo "Тестирование: $url\n";
    $url_success = 0;
    $url_times = [];
    
    for ($i = 1; $i <= $total_tests; $i++) {
        $start_time = microtime(true);
        
        $headers = [
            'Authorization' => 'Bearer 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'
        ];
        
        if (strpos($url, 'chat/completions') !== false) {
            $headers['Content-Type'] = 'application/json';
            $body = json_encode([
                'model' => 'gpt-4o',
                'messages' => [['role' => 'user', 'content' => 'Test']],
                'max_tokens' => 5
            ]);
            $response = wp_remote_post($url, [
                'proxy' => $proxy_url,
                'timeout' => 30,
                'headers' => $headers,
                'body' => $body
            ]);
        } elseif (strpos($url, 'images/generations') !== false) {
            $headers['Content-Type'] = 'application/json';
            $body = json_encode([
                'model' => 'dall-e-2',
                'prompt' => 'test',
                'size' => '256x256',
                'n' => 1
            ]);
            $response = wp_remote_post($url, [
                'proxy' => $proxy_url,
                'timeout' => 60,
                'headers' => $headers,
                'body' => $body
            ]);
        } else {
            $response = wp_remote_get($url, [
                'proxy' => $proxy_url,
                'timeout' => 15,
                'headers' => $headers
            ]);
        }
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        $url_times[] = $response_time;
        
        if (!is_wp_error($response)) {
            $code = wp_remote_retrieve_response_code($response);
            if ($code === 200) {
                $url_success++;
                $successful_tests++;
                echo "  Тест $i: ✅ Успех ({$response_time} мс)\n";
            } else {
                echo "  Тест $i: ❌ HTTP $code ({$response_time} мс)\n";
            }
        } else {
            echo "  Тест $i: ❌ Ошибка: " . $response->get_error_message() . " ({$response_time} мс)\n";
        }
        
        $total_time += $response_time;
        usleep(500000); // 0.5 секунды между тестами
    }
    
    $avg_time = round(array_sum($url_times) / count($url_times), 2);
    $min_time = min($url_times);
    $max_time = max($url_times);
    $success_rate = round(($url_success / $total_tests) * 100, 1);
    
    echo "  Результаты для $url:\n";
    echo "    Успешность: $url_success/$total_tests ($success_rate%)\n";
    echo "    Среднее время: $avg_time мс\n";
    echo "    Мин/Макс время: $min_time/$max_time мс\n\n";
}

$overall_success_rate = round(($successful_tests / ($total_tests * count($test_urls))) * 100, 1);
$avg_response_time = round($total_time / ($total_tests * count($test_urls)), 2);

echo "=== ОБЩИЕ РЕЗУЛЬТАТЫ ===\n";
echo "Общая успешность: $successful_tests/" . ($total_tests * count($test_urls)) . " ($overall_success_rate%)\n";
echo "Среднее время ответа: $avg_response_time мс\n";
echo "Общее время тестирования: " . round($total_time / 1000, 2) . " секунд\n";

if ($overall_success_rate >= 90) {
    echo "Статус: 🟢 ОТЛИЧНО - Прокси работает стабильно\n";
} elseif ($overall_success_rate >= 70) {
    echo "Статус: 🟡 ХОРОШО - Прокси работает с незначительными перебоями\n";
} else {
    echo "Статус: 🔴 ПЛОХО - Прокси работает нестабильно\n";
}

// Дополнительный тест доступности openai.com
echo "\n=== ТЕСТ ДОСТУПНОСТИ OPENAI.COM ===\n";

$openai_tests = [
    'https://api.openai.com/v1/models',
    'https://api.openai.com/v1/chat/completions',
    'https://api.openai.com/v1/images/generations',
    'https://api.openai.com/v1/embeddings'
];

foreach ($openai_tests as $test_url) {
    $start_time = microtime(true);
    
    $response = wp_remote_get($test_url, [
        'proxy' => $proxy_url,
        'timeout' => 10,
        'headers' => [
            'Authorization' => 'Bearer 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'
        ]
    ]);
    
    $response_time = round((microtime(true) - $start_time) * 1000, 2);
    
    if (!is_wp_error($response)) {
        $code = wp_remote_retrieve_response_code($response);
        echo "✅ $test_url - HTTP $code ({$response_time} мс)\n";
    } else {
        echo "❌ $test_url - Ошибка: " . $response->get_error_message() . " ({$response_time} мс)\n";
    }
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>
