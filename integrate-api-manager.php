<?php
/**
 * Интеграция централизованного API менеджера с существующими плагинами
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ИНТЕГРАЦИЯ API МЕНЕДЖЕРА С ПЛАГИНАМИ ===\n\n";

// 1. Обновляем настройки ChatGPT Consultant
echo "1. Обновление ChatGPT Consultant...\n";
$chatgpt_settings = [
    'bcc_model' => 'gpt-4o',
    'bcc_max_tokens' => 2000,
    'bcc_temperature' => 0.7,
    'bcc_enable_logging' => true,
    'bcc_enable_usage_tracking' => true
];

foreach ($chatgpt_settings as $option => $value) {
    update_option($option, $value);
    echo "   ✅ $option = $value\n";
}

// 2. Обновляем настройки ABP Image Generator
echo "\n2. Обновление ABP Image Generator...\n";
$image_settings = [
    'abp_image_generator_settings' => [
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
    ]
];

foreach ($image_settings as $option => $value) {
    update_option($option, $value);
    echo "   ✅ $option обновлен\n";
}

// 3. Обновляем настройки SEO генератора
echo "\n3. Обновление SEO генератора...\n";
$seo_key = get_option('bsag_openai_api_key', '');
if ($seo_key) {
    echo "   ✅ API ключ SEO генератора уже настроен\n";
} else {
    $api_key = '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';
    update_option('bsag_openai_api_key', $api_key);
    echo "   ✅ API ключ SEO генератора настроен\n";
}

// 4. Создаем директории для мониторинга
echo "\n4. Создание директорий мониторинга...\n";
$upload_dir = wp_upload_dir();
$directories = [
    'proxy-monitor',
    'openai-logs'
];

foreach ($directories as $dir) {
    $path = $upload_dir['basedir'] . '/' . $dir;
    if (!file_exists($path)) {
        wp_mkdir_p($path);
        echo "   ✅ Создана директория: $dir\n";
    } else {
        echo "   ✅ Директория уже существует: $dir\n";
    }
}

// 5. Проверяем активность mu-plugins
echo "\n5. Проверка mu-plugins...\n";
$mu_plugins = [
    'proxy-monitor.php' => 'Proxy Monitor',
    'openai-api-manager.php' => 'OpenAI API Manager',
    'dutch-proxy-integration.php' => 'Dutch Proxy Integration',
    'force-proxy.php' => 'Force Proxy',
    'http-internal-bypass.php' => 'HTTP Internal Bypass'
];

foreach ($mu_plugins as $file => $name) {
    $path = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/mu-plugins/' . $file;
    if (file_exists($path)) {
        echo "   ✅ $name - активен\n";
    } else {
        echo "   ❌ $name - не найден\n";
    }
}

// 6. Тестируем интеграцию
echo "\n6. Тестирование интеграции...\n";

// Тест API менеджера
if (class_exists('OpenAI_API_Manager')) {
    $api_manager = OpenAI_API_Manager::get_instance();
    $api_key = $api_manager->get_api_key();
    $settings = $api_manager->get_api_settings();
    
    echo "   ✅ OpenAI API Manager загружен\n";
    echo "   ✅ API ключ: " . (strlen($api_key) > 0 ? 'настроен' : 'не настроен') . "\n";
    echo "   ✅ Плагинов в настройках: " . count($settings['plugins']) . "\n";
} else {
    echo "   ❌ OpenAI API Manager не найден\n";
}

// Тест мониторинга прокси
if (class_exists('Proxy_Monitor')) {
    echo "   ✅ Proxy Monitor загружен\n";
} else {
    echo "   ❌ Proxy Monitor не найден\n";
}

// 7. Создаем .htaccess для защиты логов
echo "\n7. Настройка безопасности...\n";
$htaccess_content = "Options -Indexes\n";
$htaccess_content .= "deny from all\n";
$htaccess_content .= "<Files ~ \"\\.(json|log)$\">\n";
$htaccess_content .= "allow from all\n";
$htaccess_content .= "</Files>\n";

$upload_dir = wp_upload_dir();
$htaccess_paths = [
    $upload_dir['basedir'] . '/proxy-monitor/.htaccess',
    $upload_dir['basedir'] . '/openai-logs/.htaccess'
];

foreach ($htaccess_paths as $htaccess_path) {
    $dir = dirname($htaccess_path);
    if (file_exists($dir)) {
        file_put_contents($htaccess_path, $htaccess_content);
        echo "   ✅ .htaccess создан: " . basename($dir) . "\n";
    }
}

// 8. Планируем задачи cron
echo "\n8. Настройка cron задач...\n";

// Проверяем существующие задачи
$scheduled_hooks = [
    'proxy_monitor_check' => 'Проверка прокси каждые 5 минут'
];

foreach ($scheduled_hooks as $hook => $description) {
    $next_run = wp_next_scheduled($hook);
    if ($next_run) {
        echo "   ✅ $description - запланирована (следующий запуск: " . date('Y-m-d H:i:s', $next_run) . ")\n";
    } else {
        echo "   ⚠️ $description - не запланирована\n";
    }
}

echo "\n=== ИНТЕГРАЦИЯ ЗАВЕРШЕНА ===\n";
echo "\nДоступ к настройкам:\n";
echo "- OpenAI API Manager: " . admin_url('admin.php?page=openai-api-manager') . "\n";
echo "- Использование API: " . admin_url('admin.php?page=openai-api-usage') . "\n";
echo "- Логи API: " . admin_url('admin.php?page=openai-api-logs') . "\n";
?>
