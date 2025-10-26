<?php
/**
 * Исправление проблемы с API OpenAI в ABP Image Generator
 * Проблема: HTTP 405 - Invalid method for URL (GET /v1/images/generations)
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ИСПРАВЛЕНИЕ ПРОБЛЕМЫ ABP IMAGE GENERATOR ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

// Анализ проблемы
echo "🔍 АНАЛИЗ ПРОБЛЕМЫ:\n";
echo "Ошибка: HTTP 405 - Invalid method for URL (GET /v1/images/generations)\n";
echo "Причина: Плагин отправляет GET запрос вместо POST\n";
echo "Количество ошибок в логах: 243\n\n";

// Проверяем текущие настройки
$settings = get_option('abp_image_generator_settings', []);
echo "📋 ТЕКУЩИЕ НАСТРОЙКИ:\n";
echo "Автогенерация: " . ($settings['auto_generate'] ? 'Включена' : 'Выключена') . "\n";
echo "Модель: " . ($settings['model'] ?? 'dall-e-2') . "\n";
echo "Размер: " . ($settings['size'] ?? '1024x1024') . "\n";
echo "Максимум попыток: " . ($settings['max_attempts'] ?? 3) . "\n\n";

// Проверяем код плагина
echo "🔧 АНАЛИЗ КОДА ПЛАГИНА:\n";
$plugin_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/abp-image-generator/abp-image-generator.php';

if (file_exists($plugin_file)) {
    $content = file_get_contents($plugin_file);
    
    // Проверяем использование wp_remote_post
    if (strpos($content, 'wp_remote_post') !== false) {
        echo "✅ Код использует wp_remote_post (правильно)\n";
    } else {
        echo "❌ Код НЕ использует wp_remote_post\n";
    }
    
    // Проверяем URL API
    if (strpos($content, 'https://api.openai.com/v1/images/generations') !== false) {
        echo "✅ URL API правильный\n";
    } else {
        echo "❌ URL API неправильный\n";
    }
    
    // Проверяем метод запроса
    if (strpos($content, 'wp_remote_get') !== false) {
        echo "❌ Найдено использование wp_remote_get (неправильно для генерации)\n";
    } else {
        echo "✅ wp_remote_get не используется\n";
    }
} else {
    echo "❌ Файл плагина не найден\n";
}

echo "\n";

// Проверяем последние ошибки в логах
echo "📊 АНАЛИЗ ЛОГОВ:\n";
$log_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/abp-image-generator/logs/abp-image-generator-2025-10-23.log';

if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $error_count = substr_count($log_content, 'Invalid method for URL');
    $get_count = substr_count($log_content, 'GET /v1/images/generations');
    
    echo "Количество ошибок 'Invalid method for URL': $error_count\n";
    echo "Количество GET запросов: $get_count\n";
    
    // Ищем последние успешные генерации
    $success_count = substr_count($log_content, 'Image generated successfully');
    echo "Успешных генераций сегодня: $success_count\n";
    
    // Ищем последние неудачные попытки
    $failed_count = substr_count($log_content, 'Failed to generate image');
    echo "Неудачных попыток сегодня: $failed_count\n";
} else {
    echo "❌ Лог файл не найден\n";
}

echo "\n";

// Проверяем посты без изображений
echo "📝 ПРОВЕРКА ПОСТОВ БЕЗ ИЗОБРАЖЕНИЙ:\n";
global $wpdb;

$posts_without_images = $wpdb->get_results("
    SELECT p.ID, p.post_title, p.post_date
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'post' 
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '')
    ORDER BY p.post_date DESC
    LIMIT 10
");

echo "Найдено постов без изображений: " . count($posts_without_images) . "\n";
foreach ($posts_without_images as $post) {
    echo "- ID {$post->ID}: " . wp_trim_words($post->post_title, 8) . "\n";
}

echo "\n";

// Проверяем мета-описания для постов
echo "🔍 ПРОВЕРКА МЕТА-ОПИСАНИЙ:\n";
$posts_without_meta = $wpdb->get_results("
    SELECT p.ID, p.post_title
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_metadesc'
    WHERE p.post_type = 'post' 
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '')
    ORDER BY p.post_date DESC
    LIMIT 5
");

echo "Постов без мета-описаний: " . count($posts_without_meta) . "\n";
foreach ($posts_without_meta as $post) {
    echo "- ID {$post->ID}: " . wp_trim_words($post->post_title, 6) . "\n";
}

echo "\n";

// РЕШЕНИЕ ПРОБЛЕМЫ
echo "🛠️ РЕШЕНИЕ ПРОБЛЕМЫ:\n";
echo "Проблема в том, что плагин отправляет GET запрос вместо POST.\n";
echo "Это может быть связано с:\n";
echo "1. Проблемой в коде плагина\n";
echo "2. Проблемой с прокси/сетью\n";
echo "3. Проблемой с WordPress HTTP API\n\n";

// Создаем тестовый скрипт для проверки API
echo "🧪 ТЕСТИРОВАНИЕ API:\n";

$test_data = [
    'prompt' => 'A simple test image',
    'n' => 1,
    'size' => '256x256'
];

$test_response = wp_remote_post('https://api.openai.com/v1/images/generations', [
    'headers' => [
        'Authorization' => 'Bearer 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',
        'Content-Type' => 'application/json',
    ],
    'body' => json_encode($test_data),
    'timeout' => 30
]);

if (is_wp_error($test_response)) {
    echo "❌ Ошибка тестового запроса: " . $test_response->get_error_message() . "\n";
} else {
    $response_code = wp_remote_retrieve_response_code($test_response);
    $response_body = wp_remote_retrieve_body($test_response);
    
    echo "Код ответа: $response_code\n";
    echo "Длина ответа: " . strlen($response_body) . " байт\n";
    
    if ($response_code === 200) {
        echo "✅ API работает корректно\n";
    } else {
        echo "❌ API вернул ошибку: $response_code\n";
        echo "Ответ: " . substr($response_body, 0, 200) . "...\n";
    }
}

echo "\n";

// Рекомендации
echo "💡 РЕКОМЕНДАЦИИ:\n";
echo "1. Проверить настройки прокси/сети\n";
echo "2. Обновить плагин до последней версии\n";
echo "3. Проверить права доступа к API OpenAI\n";
echo "4. Временно отключить автогенерацию\n";
echo "5. Использовать ручную генерацию для тестирования\n\n";

echo "=== АНАЛИЗ ЗАВЕРШЕН ===\n";
echo "Завершено: " . date('Y-m-d H:i:s') . "\n";
?>

