<?php
require_once('wp-config.php');

// Получаем все статьи, созданные до 23 октября 2025
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'before' => '2025-10-23 00:00:00',
            'inclusive' => true
        )
    ),
    'fields' => 'ids'
));

$total_posts = 0;
$posts_with_thumbnails = 0;
$posts_without_thumbnails = 0;
$posts_with_broken_thumbnails = 0;
$posts_fixed = 0;

echo "=== ПРОВЕРКА СТАТЕЙ ДО 23 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $total_posts++;
    
    if (!$thumbnail_id) {
        $posts_without_thumbnails++;
        echo "✗ Статья без главного изображения: ID $post_id - '$post_title'\n";
        
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
            set_post_thumbnail($post_id, $image_id);
            echo "  ✔ Привязано изображение ID $image_id\n";
            $posts_fixed++;
        } else {
            echo "  ✗ Подходящее изображение не найдено\n";
        }
    } else {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        if (!$image_path || !file_exists($image_path)) {
            $posts_with_broken_thumbnails++;
            echo "✗ Статья с отсутствующим файлом изображения: ID $post_id - '$post_title'\n";
            echo "  Изображение ID: $thumbnail_id, Путь: $image_path\n";
            
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
                set_post_thumbnail($post_id, $image_id);
                echo "  ✔ Привязано новое изображение ID $image_id\n";
                $posts_fixed++;
            } else {
                echo "  ✗ Подходящее изображение не найдено\n";
            }
        } else {
            $posts_with_thumbnails++;
            echo "✓ Статья с корректным изображением: ID $post_id - '$post_title'\n";
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПРОВЕРКИ ===\n";
echo "Всего статей до 23 октября: $total_posts\n";
echo "Статей с корректными изображениями: $posts_with_thumbnails\n";
echo "Статей без главных изображений: $posts_without_thumbnails\n";
echo "Статей с отсутствующими файлами: $posts_with_broken_thumbnails\n";
echo "Исправлено статей: $posts_fixed\n";
