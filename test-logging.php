<?php
/**
 * Тест логирования ABP плагинов
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ТЕСТ ЛОГИРОВАНИЯ ABP ПЛАГИНОВ ===\n\n";

// Проверяем настройки логирования
echo "1. Настройки WordPress:\n";
echo "WP_DEBUG: " . (defined('WP_DEBUG') ? (WP_DEBUG ? 'true' : 'false') : 'не определен') . "\n";
echo "WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'true' : 'false') : 'не определен') . "\n";
echo "WP_DEBUG_DISPLAY: " . (defined('WP_DEBUG_DISPLAY') ? (WP_DEBUG_DISPLAY ? 'true' : 'false') : 'не определен') . "\n";
echo "WP_DEBUG_LOG_PATH: " . (defined('WP_DEBUG_LOG_PATH') ? WP_DEBUG_LOG_PATH : 'не определен') . "\n\n";

// Проверяем файл логов
$log_file = WP_CONTENT_DIR . '/debug.log';
echo "2. Файл логов: $log_file\n";
echo "Существует: " . (file_exists($log_file) ? 'да' : 'нет') . "\n";
echo "Размер: " . (file_exists($log_file) ? filesize($log_file) . ' байт' : 'N/A') . "\n";
echo "Доступен для записи: " . (is_writable($log_file) ? 'да' : 'нет') . "\n\n";

// Проверяем активность плагинов
echo "3. Статус ABP плагинов:\n";
$plugins = [
    'abp-article-quality-monitor/abp-article-quality-monitor.php',
    'abp-ai-categorization/abp-ai-categorization.php',
    'abp-image-generator/abp-image-generator.php'
];

foreach ($plugins as $plugin) {
    $is_active = is_plugin_active($plugin);
    echo "- $plugin: " . ($is_active ? 'активен' : 'неактивен') . "\n";
}
echo "\n";

// Проверяем классы
echo "4. Доступность классов:\n";
$classes = [
    'ABP_Article_Quality_Monitor',
    'ABP_AI_Categorization',
    'ABP_Image_Generator'
];

foreach ($classes as $class) {
    $exists = class_exists($class);
    echo "- $class: " . ($exists ? 'существует' : 'не существует') . "\n";
}
echo "\n";

// Тестируем логирование
echo "5. Тест логирования:\n";
error_log("=== ТЕСТ ЛОГИРОВАНИЯ: " . date('Y-m-d H:i:s') . " ===");
error_log("ABP Test: Проверка работы error_log()");
error_log("ABP Test: Кириллица - тест русского текста");

echo "Тестовые сообщения отправлены в лог\n\n";

// Проверяем последние записи в логе
echo "6. Последние 5 записей в логе:\n";
if (file_exists($log_file)) {
    $lines = file($log_file);
    $last_lines = array_slice($lines, -5);
    foreach ($last_lines as $line) {
        echo trim($line) . "\n";
    }
} else {
    echo "Файл логов не найден\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>

