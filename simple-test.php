<?php
/**
 * Упрощенный тест плагинов
 */

echo "=== УПРОЩЕННЫЙ ТЕСТ ПЛАГИНОВ ===\n\n";

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "WordPress загружен успешно\n";

// 1. Проверяем доступность классов
$plugins = [
    'BizFin_ChatGPT_Consultant' => 'ChatGPT Consultant',
    'ABP_Image_Generator' => 'Image Generator', 
    'ABP_AI_Categorization' => 'AI Categorization',
    'YoastAlphabetIntegration' => 'Yoast Integration',
    'BizFin_SEO_Article_Generator' => 'SEO Generator'
];

foreach ($plugins as $class => $name) {
    if (class_exists($class)) {
        echo "✅ $name - класс найден\n";
    } else {
        echo "❌ $name - класс не найден\n";
    }
}

// 2. Тест простого HTTP запроса через прокси
echo "\nТест HTTP запроса через прокси...\n";
$response = wp_remote_get('https://api.openai.com/v1/models', [
    'proxy' => 'http://89.110.80.198:8889',
    'timeout' => 10,
    'headers' => [
        'Authorization' => 'Bearer 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'
    ]
]);

if (is_wp_error($response)) {
    echo "❌ Ошибка HTTP запроса: " . $response->get_error_message() . "\n";
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo "✅ HTTP запрос успешен, код: $code\n";
}

// 3. Проверяем настройки плагинов
echo "\nПроверка настроек плагинов...\n";

$settings = [
    'abp_image_generator_settings' => 'ABP Image Generator',
    'bcc_model' => 'ChatGPT Model',
    'bsag_openai_api_key' => 'SEO Generator API Key'
];

foreach ($settings as $option => $name) {
    $value = get_option($option);
    if ($value) {
        if ($option === 'bsag_openai_api_key') {
            echo "✅ $name - настроен (длина: " . strlen($value) . ")\n";
        } else {
            echo "✅ $name - настроен: $value\n";
        }
    } else {
        echo "❌ $name - не настроен\n";
    }
}

// 4. Проверяем mu-plugins
echo "\nПроверка mu-plugins...\n";
$mu_plugins = [
    'dutch-proxy-integration.php',
    'force-proxy.php',
    'http-internal-bypass.php'
];

foreach ($mu_plugins as $plugin) {
    $path = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/' . $plugin;
    if (file_exists($path)) {
        echo "✅ $plugin - активен\n";
    } else {
        echo "❌ $plugin - не найден\n";
    }
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>
