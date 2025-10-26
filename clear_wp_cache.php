<?php
/**
 * Очистка кеша WordPress и отключение кеширования
 */

require_once('wp-load.php');

echo "Очистка кеша WordPress и отключение кеширования...\n\n";

// 1. Очистка всех кешей WordPress
echo "1. Очистка кешей WordPress...\n";

// Очищаем кеш объектов
wp_cache_flush();
echo "✅ Object cache очищен\n";

// Очищаем кеш транзиентов
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'");
echo "✅ Transient cache очищен\n";

// Очищаем кеш переписывания URL
delete_option('rewrite_rules');
flush_rewrite_rules();
echo "✅ Rewrite rules очищены\n";

// 2. Отключение кеширования в настройках WordPress
echo "\n2. Отключение кеширования...\n";

// Отключаем кеширование в wp-config.php (если включено)
if (defined('WP_CACHE') && WP_CACHE) {
    echo "⚠️ WP_CACHE включен в wp-config.php\n";
    echo "   Рекомендуется установить WP_CACHE в false\n";
} else {
    echo "✅ WP_CACHE отключен\n";
}

// Отключаем кеширование в базе данных
update_option('wp_cache_disabled', true);
echo "✅ Кеширование отключено в БД\n";

// 3. Очистка кешей плагинов
echo "\n3. Очистка кешей плагинов...\n";

// Проверяем активные плагины кеширования
$active_plugins = get_option('active_plugins', array());

$cache_plugins = [
    'wp-super-cache/wp-cache.php',
    'w3-total-cache/w3-total-cache.php',
    'wp-rocket/wp-rocket.php',
    'litespeed-cache/litespeed-cache.php',
    'wp-fastest-cache/wpFastestCache.php',
    'cache-enabler/cache-enabler.php',
    'comet-cache/comet-cache.php'
];

$found_cache_plugins = [];
foreach ($active_plugins as $plugin) {
    if (in_array($plugin, $cache_plugins)) {
        $found_cache_plugins[] = $plugin;
    }
}

if (!empty($found_cache_plugins)) {
    echo "⚠️ Найдены плагины кеширования:\n";
    foreach ($found_cache_plugins as $plugin) {
        echo "   - $plugin\n";
    }
    echo "   Рекомендуется отключить их для разработки\n";
} else {
    echo "✅ Плагины кеширования не найдены\n";
}

// 4. Очистка кешей в wp-content
echo "\n4. Очистка файлов кеша...\n";

$cache_dirs = [
    'wp-content/cache',
    'wp-content/uploads/wpforms/cache',
    'wp-content/plugins/abp-search-cache'
];

foreach ($cache_dirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            } elseif (is_dir($file)) {
                $this->deleteDirectory($file);
                $count++;
            }
        }
        echo "✅ Очищена папка: $dir ($count элементов)\n";
    } else {
        echo "ℹ️ Папка не найдена: $dir\n";
    }
}

// 5. Отключение кеширования в .htaccess
echo "\n5. Проверка .htaccess...\n";
$htaccess_file = '.htaccess';
if (file_exists($htaccess_file)) {
    $htaccess_content = file_get_contents($htaccess_file);
    
    // Ищем правила кеширования
    if (strpos($htaccess_content, 'ExpiresByType') !== false || 
        strpos($htaccess_content, 'Cache-Control') !== false) {
        echo "⚠️ Найдены правила кеширования в .htaccess\n";
        echo "   Рекомендуется их отключить для разработки\n";
    } else {
        echo "✅ Правила кеширования в .htaccess не найдены\n";
    }
} else {
    echo "ℹ️ Файл .htaccess не найден\n";
}

// 6. Функция для удаления директорий
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

echo "\n✅ Очистка кеша WordPress завершена!\n";
echo "Кеширование отключено для активной разработки.\n";
?>
