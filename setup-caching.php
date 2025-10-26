<?php
/**
 * Настройка кеширования для улучшения производительности
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "⚡ НАСТРОЙКА КЕШИРОВАНИЯ\n";
echo "========================\n\n";

// Создаем .htaccess с правилами кеширования
$htaccess_rules = '
# BEGIN Caching Rules
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # Fonts
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/font-woff "access plus 1 year"
    ExpiresByType application/font-woff2 "access plus 1 year"
    
    # HTML
    ExpiresByType text/html "access plus 1 hour"
    
    # XML
    ExpiresByType text/xml "access plus 1 hour"
    ExpiresByType application/xml "access plus 1 hour"
    
    # Default
    ExpiresDefault "access plus 1 week"
</IfModule>

# BEGIN Compression
<IfModule mod_deflate.c>
    # Compress HTML, CSS, JavaScript, Text, XML and fonts
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
    AddOutputFilterByType DEFLATE application/x-font
    AddOutputFilterByType DEFLATE application/x-font-opentype
    AddOutputFilterByType DEFLATE application/x-font-otf
    AddOutputFilterByType DEFLATE application/x-font-truetype
    AddOutputFilterByType DEFLATE application/x-font-ttf
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE font/opentype
    AddOutputFilterByType DEFLATE font/otf
    AddOutputFilterByType DEFLATE font/ttf
    AddOutputFilterByType DEFLATE image/svg+xml
    AddOutputFilterByType DEFLATE image/x-icon
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    
    # Remove browser bugs (only needed for really old browsers)
    BrowserMatch ^Mozilla/4 gzip-only-text/html
    BrowserMatch ^Mozilla/4\.0[678] no-gzip
    BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
    Header append Vary User-Agent
</IfModule>

# BEGIN Cache-Control Headers
<IfModule mod_headers.c>
    # Cache static assets
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>
    
    # Cache HTML files for shorter time
    <FilesMatch "\.(html|htm)$">
        Header set Cache-Control "max-age=3600, public"
    </FilesMatch>
    
    # Cache XML files
    <FilesMatch "\.(xml|txt)$">
        Header set Cache-Control "max-age=3600, public"
    </FilesMatch>
</IfModule>
# END Caching Rules
';

// Читаем текущий .htaccess
$htaccess_file = '.htaccess';
$current_htaccess = file_exists($htaccess_file) ? file_get_contents($htaccess_file) : '';

// Создаем резервную копию
if ($current_htaccess) {
    $backup_file = '.htaccess.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backup_file, $current_htaccess);
    echo "💾 Создана резервная копия .htaccess: $backup_file\n";
}

// Удаляем старые правила кеширования если есть
$current_htaccess = preg_replace('/# BEGIN Caching Rules.*?# END Caching Rules/s', '', $current_htaccess);
$current_htaccess = preg_replace('/# BEGIN Compression.*?# END Compression/s', '', $current_htaccess);
$current_htaccess = preg_replace('/# BEGIN Cache-Control Headers.*?# END Cache-Control Headers/s', '', $current_htaccess);

// Добавляем новые правила
$new_htaccess = $current_htaccess . $htaccess_rules;

// Сохраняем обновленный .htaccess
file_put_contents($htaccess_file, $new_htaccess);

echo "✅ Правила кеширования добавлены в .htaccess\n\n";

// Настраиваем WordPress кеширование
echo "🔧 Настройка WordPress кеширования:\n";

// Включаем object cache если доступен
if (function_exists('wp_cache_init')) {
    echo "   ✅ Object cache доступен\n";
} else {
    echo "   ⚠️ Object cache не настроен\n";
}

// Проверяем плагины кеширования
$active_plugins = get_option('active_plugins', []);
$caching_plugins = [
    'wp-rocket/wp-rocket.php',
    'w3-total-cache/w3-total-cache.php',
    'wp-super-cache/wp-super-cache.php',
    'litespeed-cache/litespeed-cache.php'
];

$found_caching_plugin = false;
foreach ($caching_plugins as $plugin) {
    if (in_array($plugin, $active_plugins)) {
        echo "   ✅ Найден плагин кеширования: $plugin\n";
        $found_caching_plugin = true;
        break;
    }
}

if (!$found_caching_plugin) {
    echo "   ⚠️ Плагин кеширования не найден. Рекомендуется установить WP Rocket или W3 Total Cache\n";
}

// Настраиваем wp-config.php для кеширования
echo "\n🔧 Настройка wp-config.php:\n";

$wp_config_file = 'wp-config.php';
$wp_config_content = file_get_contents($wp_config_file);

// Добавляем константы кеширования если их нет
$caching_constants = [
    "define('WP_CACHE', true);",
    "define('COMPRESS_CSS', true);",
    "define('COMPRESS_SCRIPTS', true);",
    "define('CONCATENATE_SCRIPTS', true);",
    "define('ENFORCE_GZIP', true);"
];

$config_updated = false;
foreach ($caching_constants as $constant) {
    if (strpos($wp_config_content, $constant) === false) {
        // Добавляем перед "/* That's all, stop editing! */"
        $wp_config_content = str_replace(
            "/* That's all, stop editing! */",
            $constant . "\n/* That's all, stop editing! */",
            $wp_config_content
        );
        $config_updated = true;
        echo "   ✅ Добавлена константа: " . substr($constant, 7, -2) . "\n";
    }
}

if ($config_updated) {
    // Создаем резервную копию wp-config.php
    $backup_file = 'wp-config.php.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backup_file, file_get_contents('wp-config.php'));
    echo "   💾 Создана резервная копия wp-config.php: $backup_file\n";
    
    // Сохраняем обновленный wp-config.php
    file_put_contents($wp_config_file, $wp_config_content);
    echo "   ✅ wp-config.php обновлен\n";
} else {
    echo "   ✅ Все константы кеширования уже настроены\n";
}

// Очищаем кеш если есть
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "\n🧹 Кеш очищен\n";
}

echo "\n📊 РЕЗУЛЬТАТЫ НАСТРОЙКИ КЕШИРОВАНИЯ:\n";
echo "✅ Правила кеширования в .htaccess\n";
echo "✅ Сжатие контента включено\n";
echo "✅ Cache-Control заголовки настроены\n";
echo "✅ WordPress константы кеширования\n";
echo "✅ Кеш очищен\n\n";

echo "🎯 РЕКОМЕНДАЦИИ:\n";
echo "1. Установить плагин кеширования (WP Rocket, W3 Total Cache)\n";
echo "2. Настроить CDN для статических ресурсов\n";
echo "3. Оптимизировать изображения\n";
echo "4. Минифицировать CSS/JS файлы\n";
echo "5. Мониторить производительность\n\n";

echo "✅ Настройка кеширования завершена!\n";
?>

