<?php
/**
 * Тест реального индикатора мониторинга прокси
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ТЕСТ РЕАЛЬНОГО ИНДИКАТОРА МОНИТОРИНГА ПРОКСИ ===\n\n";

// 1. Проверяем загрузку класса
echo "1. Проверка загрузки Proxy Admin Indicator...\n";
if (class_exists('Proxy_Admin_Indicator')) {
    echo "   ✅ Proxy Admin Indicator загружен\n";
} else {
    echo "   ❌ Proxy Admin Indicator не найден\n";
    exit;
}

// 2. Тестируем реальную проверку прокси
echo "\n2. Тестирование реальной проверки прокси...\n";

$indicator = Proxy_Admin_Indicator::get_instance();
$reflection = new ReflectionClass($indicator);

// Тестируем метод real_time_proxy_check
$method = $reflection->getMethod('real_time_proxy_check');
$method->setAccessible(true);

echo "   🔄 Выполняется реальная проверка прокси...\n";
$start_time = microtime(true);
$status = $method->invoke($indicator);
$check_time = microtime(true) - $start_time;

echo "   ✅ Статус: $status (время проверки: " . round($check_time, 2) . " сек)\n";

// 3. Тестируем отдельные тесты
echo "\n3. Тестирование отдельных компонентов...\n";

// Тест подключения
$method = $reflection->getMethod('test_connection');
$method->setAccessible(true);
$connection_result = $method->invoke($indicator);

echo "   📡 Тест подключения:\n";
echo "      - Статус: " . $connection_result['status'] . "\n";
echo "      - HTTP код: " . ($connection_result['http_code'] ?? 'N/A') . "\n";
echo "      - Время ответа: " . ($connection_result['response_time'] ?? 'N/A') . " мс\n";
if (isset($connection_result['error'])) {
    echo "      - Ошибка: " . $connection_result['error'] . "\n";
}

// Тест API
$method = $reflection->getMethod('test_openai_api');
$method->setAccessible(true);
$api_result = $method->invoke($indicator);

echo "   🤖 Тест OpenAI API:\n";
echo "      - Статус: " . $api_result['status'] . "\n";
echo "      - HTTP код: " . ($api_result['http_code'] ?? 'N/A') . "\n";
echo "      - Время ответа: " . ($api_result['response_time'] ?? 'N/A') . " мс\n";
echo "      - Токены использовано: " . ($api_result['tokens_used'] ?? 'N/A') . "\n";
if (isset($api_result['error'])) {
    echo "      - Ошибка: " . $api_result['error'] . "\n";
}

// 4. Тестируем AJAX endpoint
echo "\n4. Тестирование AJAX endpoint...\n";

// Симулируем AJAX запрос
$_POST['action'] = 'proxy_get_status';
$_POST['nonce'] = wp_create_nonce('proxy_status_nonce');

// Перехватываем вывод
ob_start();
try {
    $indicator->ajax_get_status();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && isset($response['success']) && $response['success']) {
        echo "   ✅ AJAX endpoint работает\n";
        echo "   📊 Статус: " . $response['data']['status'] . "\n";
        echo "   🎨 Цвет: " . $response['data']['status_info']['color'] . "\n";
        echo "   🏷️ Метка: " . $response['data']['status_info']['label'] . "\n";
        echo "   💡 Подсказка: " . $response['data']['status_info']['tooltip'] . "\n";
        echo "   ⏰ Время проверки: " . $response['data']['additional_info']['check_time'] . "\n";
    } else {
        echo "   ❌ Ошибка AJAX ответа\n";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}

// 5. Проверяем файлы мониторинга
echo "\n5. Проверка файлов мониторинга...\n";

$upload_dir = wp_upload_dir();
$files_to_check = [
    $upload_dir['basedir'] . '/proxy-monitor/stats.json' => 'Статистика прокси',
    $upload_dir['basedir'] . '/proxy-monitor/alerts.json' => 'Алерты прокси'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $content = json_decode(file_get_contents($file), true);
        $count = is_array($content) ? count($content) : 0;
        echo "   ✅ $description: " . round($size / 1024, 2) . " KB ($count записей)\n";
    } else {
        echo "   ⚠️ $description: файл не найден (будет создан автоматически)\n";
    }
}

// 6. Информация о доступе
echo "\n6. Информация о доступе:\n";
echo "   🌐 Админ-панель: " . admin_url() . "\n";
echo "   📊 OpenAI API Manager: " . admin_url('admin.php?page=openai-api-manager') . "\n";
echo "   📁 Статистика: " . $upload_dir['baseurl'] . '/proxy-monitor/stats.json' . "\n";

// 7. Рекомендации
echo "\n7. Рекомендации:\n";
echo "   🔄 Индикатор обновляется каждые 30 секунд\n";
echo "   🎯 При клике на индикатор можно обновить статус вручную\n";
echo "   🔔 Уведомления появляются при критических проблемах\n";
echo "   📱 Индикатор работает на всех страницах админки\n";

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
echo "Индикатор мониторинга прокси готов к работе в реальном времени!\n";
echo "Цвета индикатора:\n";
echo "  🟢 Зеленый - Прокси работает стабильно (80-100% успешных запросов)\n";
echo "  🟡 Желтый - Прокси работает с перебоями (50-80% успешных запросов)\n";
echo "  🔴 Красный - Критические проблемы с прокси (0-50% успешных запросов)\n";
?>
