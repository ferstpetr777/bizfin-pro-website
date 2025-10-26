<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ ИЗОБРАЖЕНИЙ БЛОГА (ПРАВИЛЬНАЯ ВЕРСИЯ) ===\n\n";

// 1. Исправим логотип сайта
$custom_logo_id = get_theme_mod('custom_logo');
if ($custom_logo_id) {
    echo "Найден логотип сайта (ID: " . $custom_logo_id . ")\n";
    
    // Обновим на WebP версию
    $webp_file = '2025/09/Бизнес-Финанс.webp';
    $full_path = ABSPATH . 'wp-content/uploads/' . $webp_file;
    
    if (file_exists($full_path)) {
        update_post_meta($custom_logo_id, '_wp_attached_file', $webp_file);
        update_post_meta($custom_logo_id, 'post_mime_type', 'image/webp');
        
        $new_guid = home_url('/wp-content/uploads/' . $webp_file);
        wp_update_post(array(
            'ID' => $custom_logo_id,
            'guid' => $new_guid
        ));
        
        echo "✅ Логотип обновлен на WebP\n";
    }
}

echo "\n=== ИСПРАВЛЕНИЕ FEATURED IMAGES ===\n";

// 2. Найдем все статьи с битыми изображениями
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1
));

$fixed_count = 0;

foreach ($posts as $post) {
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    
    if ($thumbnail_id) {
        $attachment = get_post($thumbnail_id);
        $file_path = get_post_meta($thumbnail_id, '_wp_attached_file', true);
        $full_path = ABSPATH . 'wp-content/uploads/' . $file_path;
        
        // Проверим, существует ли файл
        if (!file_exists($full_path)) {
            echo "Битая ссылка для статьи '" . $post->post_title . "' (ID: " . $post->ID . ")\n";
            echo "Файл: " . $file_path . "\n";
            
            // Попробуем найти WebP версию
            $base_name = pathinfo($file_path, PATHINFO_FILENAME);
            $webp_path = dirname($file_path) . '/' . $base_name . '.webp';
            $webp_full_path = ABSPATH . 'wp-content/uploads/' . $webp_path;
            
            if (file_exists($webp_full_path)) {
                echo "Найден WebP файл: " . $webp_path . "\n";
                
                // Обновим метаданные
                update_post_meta($thumbnail_id, '_wp_attached_file', $webp_path);
                update_post_meta($thumbnail_id, 'post_mime_type', 'image/webp');
                
                // Обновим GUID
                $new_guid = home_url('/wp-content/uploads/' . $webp_path);
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'guid' => $new_guid
                ));
                
                echo "✅ Изображение обновлено на WebP\n";
                $fixed_count++;
            } else {
                // Попробуем найти PNG версию
                $png_path = dirname($file_path) . '/' . $base_name . '.png';
                $png_full_path = ABSPATH . 'wp-content/uploads/' . $png_path;
                
                if (file_exists($png_full_path)) {
                    echo "Найден PNG файл: " . $png_path . "\n";
                    
                    // Обновим метаданные
                    update_post_meta($thumbnail_id, '_wp_attached_file', $png_path);
                    update_post_meta($thumbnail_id, 'post_mime_type', 'image/png');
                    
                    // Обновим GUID
                    $new_guid = home_url('/wp-content/uploads/' . $png_path);
                    wp_update_post(array(
                        'ID' => $thumbnail_id,
                        'guid' => $new_guid
                    ));
                    
                    echo "✅ Изображение обновлено на PNG\n";
                    $fixed_count++;
                } else {
                    echo "❌ Ни WebP, ни PNG файл не найден\n";
                }
            }
        }
    }
}

echo "\n=== СВОДКА ===\n";
echo "Исправлено изображений: " . $fixed_count . "\n";

echo "\n=== ПРОВЕРКА CSS ПРОБЛЕМ ===\n";

// 3. Проверим настройки темы для блога
$astra_settings = get_option('astra-settings', array());

// Настройки для блога
$blog_settings = array(
    'blog-layout' => 'blog-layout-1',
    'blog-post-structure' => array('ast-dynamic-blog-layout-1-image', 'ast-dynamic-blog-layout-1-title', 'ast-dynamic-blog-layout-1-meta', 'ast-dynamic-blog-layout-1-content'),
    'blog-image-size' => 'large',
    'blog-image-ratio-type' => 'default'
);

foreach ($blog_settings as $key => $value) {
    if (!isset($astra_settings[$key])) {
        $astra_settings[$key] = $value;
        echo "Добавлена настройка: " . $key . " = " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
    }
}

// Сохраним настройки
update_option('astra-settings', $astra_settings);

echo "✅ Настройки блога обновлены\n";

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

