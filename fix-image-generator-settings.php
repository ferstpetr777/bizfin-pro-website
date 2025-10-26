<?php
require_once('wp-load.php');

echo "=== ИСПРАВЛЕНИЕ НАСТРОЕК IMAGE GENERATOR ===\n\n";

// 1. Исправляем настройки в централизованном API менеджере
echo "1. Исправление настроек в OpenAI API Manager...\n";

$api_manager_settings = get_option('openai_api_manager', []);
if (is_string($api_manager_settings)) {
    $api_manager_settings = json_decode($api_manager_settings, true);
}

if (!is_array($api_manager_settings)) {
    $api_manager_settings = [];
}

// Устанавливаем правильные настройки для Image Generator
$api_manager_settings['plugins']['abp_image_generator'] = [
    'enabled' => '1',
    'model' => 'dall-e-2',  // Правильная модель для генерации изображений
    'size' => '1024x1024',
    'quality' => 'standard'
];

$result = update_option('openai_api_manager', $api_manager_settings);
if ($result) {
    echo "   ✅ Настройки OpenAI API Manager обновлены\n";
    echo "   📝 Модель Image Generator: dall-e-2\n";
} else {
    echo "   ❌ Ошибка обновления OpenAI API Manager\n";
}

// 2. Проверяем и обновляем настройки самого плагина ABP Image Generator
echo "\n2. Проверка настроек ABP Image Generator...\n";

$abp_settings = get_option('abp_image_generator_settings', []);
if (!is_array($abp_settings)) {
    $abp_settings = [];
}

// Устанавливаем правильные настройки по умолчанию
$abp_settings = array_merge([
    'auto_generate' => true,
    'model' => 'dall-e-2',
    'size' => '1024x1024',
    'quality' => 'standard',
    'style' => 'natural',
    'max_attempts' => 3,
    'retry_delay' => 5,
    'log_level' => 'info',
    'enable_seo_optimization' => true,
    'auto_alt_text' => true,
    'auto_description' => true
], $abp_settings);

$result = update_option('abp_image_generator_settings', $abp_settings);
if ($result) {
    echo "   ✅ Настройки ABP Image Generator обновлены\n";
    echo "   📝 Модель: " . $abp_settings['model'] . "\n";
    echo "   📝 Размер: " . $abp_settings['size'] . "\n";
    echo "   📝 Качество: " . $abp_settings['quality'] . "\n";
} else {
    echo "   ❌ Ошибка обновления ABP Image Generator\n";
}

// 3. Проверяем доступность DALL-E API
echo "\n3. Тестирование DALL-E API...\n";

$api_key = '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';

$test_data = [
    'model' => 'dall-e-2',
    'prompt' => 'A simple test image of a red circle',
    'n' => 1,
    'size' => '256x256'
];

$response = wp_remote_post('https://api.openai.com/v1/images/generations', [
    'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode($test_data),
    'timeout' => 30,
    'proxy' => 'http://89.110.80.198:8889'
]);

if (is_wp_error($response)) {
    echo "   ❌ Ошибка подключения к DALL-E API: " . $response->get_error_message() . "\n";
} else {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['data'][0]['url'])) {
        echo "   ✅ DALL-E API работает корректно\n";
        echo "   🖼️ Тестовое изображение создано: " . $data['data'][0]['url'] . "\n";
    } else {
        echo "   ❌ Ошибка DALL-E API: " . ($data['error']['message'] ?? 'Неизвестная ошибка') . "\n";
    }
}

// 4. Проверяем интеграцию с плагином
echo "\n4. Проверка интеграции с плагином...\n";

if (class_exists('ABP_Image_Generator')) {
    echo "   ✅ Класс ABP_Image_Generator найден\n";
    
    $generator = new ABP_Image_Generator();
    if (method_exists($generator, 'get_settings')) {
        $settings = $generator->get_settings();
        echo "   📝 Текущая модель в плагине: " . ($settings['model'] ?? 'не установлена') . "\n";
    }
} else {
    echo "   ❌ Класс ABP_Image_Generator не найден\n";
}

// 5. Проверяем доступные модели DALL-E
echo "\n5. Доступные модели DALL-E:\n";
echo "   🎨 dall-e-2 - Базовая модель для генерации изображений\n";
echo "   🎨 dall-e-3 - Улучшенная модель с лучшим качеством\n";
echo "   📏 Размеры: 256x256, 512x512, 1024x1024 (dall-e-2)\n";
echo "   📏 Размеры: 1024x1024, 1792x1024, 1024x1792 (dall-e-3)\n";

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
echo "Теперь Image Generator настроен на правильные модели DALL-E для генерации изображений.\n";
?>
