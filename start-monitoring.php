<?php
/**
 * Запуск системы мониторинга
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ЗАПУСК СИСТЕМЫ МОНИТОРИНГА ===\n\n";

// 1. Планируем задачу мониторинга прокси
echo "1. Планирование мониторинга прокси...\n";

// Удаляем существующую задачу если есть
$existing = wp_next_scheduled('proxy_monitor_check');
if ($existing) {
    wp_unschedule_event($existing, 'proxy_monitor_check');
    echo "   ✅ Существующая задача удалена\n";
}

// Планируем новую задачу
$scheduled = wp_schedule_event(time(), 'every_5_minutes', 'proxy_monitor_check');
if ($scheduled) {
    echo "   ✅ Задача мониторинга запланирована\n";
    $next_run = wp_next_scheduled('proxy_monitor_check');
    echo "   📅 Следующий запуск: " . date('Y-m-d H:i:s', $next_run) . "\n";
} else {
    echo "   ❌ Ошибка планирования задачи\n";
}

// 2. Запускаем первичную проверку
echo "\n2. Первичная проверка прокси...\n";

if (class_exists('Proxy_Monitor')) {
    $monitor = new Proxy_Monitor();
    $monitor->perform_monitoring();
    echo "   ✅ Первичная проверка выполнена\n";
} else {
    echo "   ❌ Proxy Monitor не найден\n";
}

// 3. Проверяем статус системы
echo "\n3. Проверка статуса системы...\n";

// Проверяем API менеджер
if (class_exists('OpenAI_API_Manager')) {
    $api_manager = OpenAI_API_Manager::get_instance();
    $api_key = $api_manager->get_api_key();
    echo "   ✅ OpenAI API Manager: активен\n";
    echo "   🔑 API ключ: " . (strlen($api_key) > 0 ? 'настроен' : 'не настроен') . "\n";
} else {
    echo "   ❌ OpenAI API Manager: не найден\n";
}

// Проверяем директории
$upload_dir = wp_upload_dir();
$directories = [
    'proxy-monitor' => 'Мониторинг прокси',
    'openai-logs' => 'Логи OpenAI'
];

foreach ($directories as $dir => $name) {
    $path = $upload_dir['basedir'] . '/' . $dir;
    if (file_exists($path) && is_writable($path)) {
        echo "   ✅ $name: директория доступна\n";
    } else {
        echo "   ❌ $name: директория недоступна\n";
    }
}

// 4. Тестируем API через прокси
echo "\n4. Тестирование API через прокси...\n";

$response = wp_remote_get('https://api.openai.com/v1/models', [
    'proxy' => 'http://89.110.80.198:8889',
    'timeout' => 10,
    'headers' => [
        'Authorization' => 'Bearer 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'
    ]
]);

if (is_wp_error($response)) {
    echo "   ❌ Ошибка API: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo "   ✅ API тест: HTTP $code\n";
}

// 5. Создаем файл статуса
echo "\n5. Создание файла статуса...\n";

$status_file = $upload_dir['basedir'] . '/proxy-monitor/system-status.json';
$status = [
    'last_check' => current_time('mysql'),
    'system_status' => 'active',
    'monitoring_enabled' => true,
    'next_check' => date('Y-m-d H:i:s', wp_next_scheduled('proxy_monitor_check')),
    'api_manager_active' => class_exists('OpenAI_API_Manager'),
    'proxy_monitor_active' => class_exists('Proxy_Monitor'),
    'proxy_url' => 'http://89.110.80.198:8889'
];

file_put_contents($status_file, json_encode($status, JSON_PRETTY_PRINT));
echo "   ✅ Файл статуса создан\n";

// 6. Информация о доступе
echo "\n6. Информация о доступе:\n";
echo "   🌐 OpenAI API Manager: " . admin_url('admin.php?page=openai-api-manager') . "\n";
echo "   📊 Использование API: " . admin_url('admin.php?page=openai-api-usage') . "\n";
echo "   📝 Логи API: " . admin_url('admin.php?page=openai-api-logs') . "\n";
echo "   📁 Статус системы: " . $upload_dir['baseurl'] . '/proxy-monitor/system-status.json' . "\n";

echo "\n=== СИСТЕМА МОНИТОРИНГА ЗАПУЩЕНА ===\n";
echo "Мониторинг будет выполняться каждые 5 минут автоматически.\n";
?>
