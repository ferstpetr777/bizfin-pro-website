<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ МИНИАТЮР БЛОГА ===\n\n";

// Получаем все опубликованные статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed_count = 0;
$not_found_count = 0;

foreach ($posts as $post_id) {
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if (!$thumbnail_id) {
        continue;
    }
    
    $attachment_meta = get_post_meta($thumbnail_id, '_wp_attachment_metadata', true);
    $file_path = get_post_meta($thumbnail_id, '_wp_attached_file', true);
    
    if (!$file_path) {
        continue;
    }
    
    $full_path = ABSPATH . 'wp-content/uploads/' . $file_path;
    
    // Проверяем, существует ли файл
    if (!file_exists($full_path)) {
        echo "Битая ссылка для статьи '" . get_the_title($post_id) . "' (ID: " . $post_id . ")\n";
        echo "Файл: " . $file_path . "\n";
        
        // Пробуем найти WebP версию
        $webp_path = str_replace('.png', '.webp', $file_path);
        $full_webp_path = ABSPATH . 'wp-content/uploads/' . $webp_path;
        
        if (file_exists($full_webp_path)) {
            // Обновляем метаданные на WebP
            update_post_meta($thumbnail_id, '_wp_attached_file', $webp_path);
            update_post_meta($thumbnail_id, 'post_mime_type', 'image/webp');
            
            // Обновляем guid
            $old_guid = get_post($thumbnail_id)->guid;
            $new_guid = str_replace('.png', '.webp', $old_guid);
            wp_update_post(array('ID' => $thumbnail_id, 'guid' => $new_guid));
            
            // Обновляем _wp_attachment_metadata
            if (is_array($attachment_meta)) {
                $attachment_meta['file'] = $webp_path;
                if (isset($attachment_meta['sizes']) && is_array($attachment_meta['sizes'])) {
                    foreach ($attachment_meta['sizes'] as $size_name => $size_data) {
                        if (isset($size_data['file']) && strpos($size_data['file'], '.png') !== false) {
                            $attachment_meta['sizes'][$size_name]['file'] = str_replace('.png', '.webp', $size_data['file']);
                            $attachment_meta['sizes'][$size_name]['mime-type'] = 'image/webp';
                        }
                    }
                }
                update_post_meta($thumbnail_id, '_wp_attachment_metadata', $attachment_meta);
            }
            
            echo "✅ Обновлено на WebP: " . $webp_path . "\n";
            $fixed_count++;
        } else {
            echo "❌ WebP файл не найден: " . $webp_path . "\n";
            $not_found_count++;
        }
        echo "\n";
    }
}

echo "=== СВОДКА ===\n";
echo "Исправлено миниатюр: " . $fixed_count . "\n";
echo "Не найдено файлов: " . $not_found_count . "\n\n";

// Очищаем кэш
wp_cache_flush();
echo "✅ Кэш очищен\n\n";

echo "=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

