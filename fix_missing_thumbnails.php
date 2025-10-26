<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ИСПРАВЛЕНИЕ ОТСУТСТВУЮЩИХ МИНИАТЮР ===\n\n";

$fixed_count = 0;
$no_match_count = 0;
$already_good_count = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        echo "Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        
        // Ищем подходящее изображение
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
            set_post_thumbnail($post_id, $image_id);
            echo "  ✔ Привязано изображение ID $image_id: '" . get_the_title($image_id) . "'\n";
            $fixed_count++;
        } else {
            echo "  ✗ Не найдено подходящее изображение\n";
            $no_match_count++;
        }
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($thumbnail_id);
    if (!$image_path || !file_exists($image_path)) {
        echo "Статья ID $post_id: '$post_title' - файл изображения отсутствует (ID: $thumbnail_id)\n";
        
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
            set_post_thumbnail($post_id, $image_id);
            echo "  ✔ Заменено на изображение ID $image_id: '" . get_the_title($image_id) . "'\n";
            $fixed_count++;
        } else {
            echo "  ✗ Не найдено подходящее изображение\n";
            $no_match_count++;
        }
    } else {
        $already_good_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено миниатюр: $fixed_count\n";
echo "Не найдено подходящих изображений: $no_match_count\n";
echo "Уже имели корректные миниатюры: $already_good_count\n";
echo "Всего статей обработано: " . count($posts) . "\n";
