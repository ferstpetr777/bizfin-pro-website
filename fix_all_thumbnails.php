<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$total_posts = 0;
$posts_with_thumbnails = 0;
$posts_fixed = 0;
$posts_without_images = 0;

echo "=== ПОЛНАЯ ПРОВЕРКА И ИСПРАВЛЕНИЕ ВСЕХ МИНИАТЮР ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $total_posts++;
    
    if (!$thumbnail_id) {
        $posts_without_images++;
        echo "✗ Статья без главного изображения: ID $post_id - '$post_title'\n";
        continue;
    }
    
    // Проверяем, существуют ли миниатюры всех размеров
    $thumbnail_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
    $missing_sizes = array();
    
    foreach ($thumbnail_sizes as $size) {
        $image_data = wp_get_attachment_image_src($thumbnail_id, $size);
        if (!$image_data || !file_exists($image_data[0])) {
            $missing_sizes[] = $size;
        }
    }
    
    if (empty($missing_sizes)) {
        $posts_with_thumbnails++;
    } else {
        // Генерируем недостающие миниатюры
        $image_path = get_attached_file($thumbnail_id);
        if ($image_path && file_exists($image_path)) {
            echo "Генерация миниатюр для: ID $post_id - '$post_title'\n";
            echo "  Отсутствующие размеры: " . implode(', ', $missing_sizes) . "\n";
            
            $metadata = wp_generate_attachment_metadata($thumbnail_id, $image_path);
            if ($metadata && !is_wp_error($metadata)) {
                wp_update_attachment_metadata($thumbnail_id, $metadata);
                $posts_fixed++;
                echo "  ✓ Миниатюры сгенерированы успешно\n";
            } else {
                echo "  ✗ Ошибка генерации миниатюр\n";
            }
        } else {
            echo "✗ Файл изображения не найден для статьи ID $post_id - '$post_title'\n";
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Всего статей: $total_posts\n";
echo "Статей с полными миниатюрами: $posts_with_thumbnails\n";
echo "Исправлено миниатюр: $posts_fixed\n";
echo "Статей без главных изображений: $posts_without_images\n";
