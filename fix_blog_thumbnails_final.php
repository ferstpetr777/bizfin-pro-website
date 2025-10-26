<?php
require_once('wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

echo "=== ИСПРАВЛЕНИЕ МИНИАТЮР В БЛОГЕ ===\n\n";

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed_count = 0;
$already_good_count = 0;
$no_match_count = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        // Ищем подходящее изображение по названию
        $matching_images = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'title' => $post_title,
            'fields' => 'ids'
        ));
        
        if (!empty($matching_images)) {
            $image_id = $matching_images[0];
            $image_path = get_attached_file($image_id);
            
            if ($image_path && file_exists($image_path)) {
                set_post_thumbnail($post_id, $image_id);
                
                // Регенерируем миниатюры
                $attach_data = wp_generate_attachment_metadata($image_id, $image_path);
                wp_update_attachment_metadata($image_id, $attach_data);
                
                echo "✔ Статья ID $post_id: '$post_title' - установлено изображение ID $image_id\n";
                $fixed_count++;
            }
        } else {
            echo "✗ Статья ID $post_id: '$post_title' - не найдено подходящее изображение\n";
            $no_match_count++;
        }
    } else {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        
        if (!$image_path || !file_exists($image_path)) {
            // Ищем новое подходящее изображение
            $matching_images = get_posts(array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'numberposts' => 1,
                'title' => $post_title,
                'fields' => 'ids'
            ));
            
            if (!empty($matching_images)) {
                $image_id = $matching_images[0];
                $new_image_path = get_attached_file($image_id);
                
                if ($new_image_path && file_exists($new_image_path)) {
                    set_post_thumbnail($post_id, $image_id);
                    
                    // Регенерируем миниатюры
                    $attach_data = wp_generate_attachment_metadata($image_id, $new_image_path);
                    wp_update_attachment_metadata($image_id, $attach_data);
                    
                    echo "✔ Статья ID $post_id: '$post_title' - заменено изображение на ID $image_id\n";
                    $fixed_count++;
                }
            } else {
                echo "✗ Статья ID $post_id: '$post_title' - файл отсутствует, не найдено замены\n";
                $no_match_count++;
            }
        } else {
            // Регенерируем миниатюры для существующего изображения
            $attach_data = wp_generate_attachment_metadata($thumbnail_id, $image_path);
            wp_update_attachment_metadata($thumbnail_id, $attach_data);
            $already_good_count++;
        }
    }
}

// Сбрасываем кэш
wp_cache_flush();

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено миниатюр: $fixed_count\n";
echo "Уже имели корректные миниатюры: $already_good_count\n";
echo "Не найдено подходящих изображений: $no_match_count\n";
echo "Всего статей обработано: " . count($posts) . "\n";
echo "\nКэш очищен!\n";
