<?php
require_once('wp-config.php');

// Получаем все опубликованные статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$with_featured = 0;
$without_featured = 0;
$missing_images = array();

foreach ($posts as $post_id) {
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $post_title = get_the_title($post_id);
    
    if ($thumbnail_id) {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        if ($image_path && file_exists($image_path)) {
            $with_featured++;
        } else {
            $without_featured++;
            $missing_images[] = array(
                'id' => $post_id,
                'title' => $post_title,
                'thumbnail_id' => $thumbnail_id,
                'reason' => 'Image file not found'
            );
        }
    } else {
        $without_featured++;
        $missing_images[] = array(
            'id' => $post_id,
            'title' => $post_title,
            'thumbnail_id' => null,
            'reason' => 'No featured image set'
        );
    }
}

echo "=== СТАТИСТИКА ГЛАВНЫХ ИЗОБРАЖЕНИЙ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "С главными изображениями: $with_featured\n";
echo "Без главных изображений: $without_featured\n\n";

if (!empty($missing_images)) {
    echo "=== СТАТЬИ БЕЗ ГЛАВНЫХ ИЗОБРАЖЕНИЙ ===\n";
    foreach ($missing_images as $missing) {
        echo "ID: {$missing['id']} | {$missing['title']} | {$missing['reason']}\n";
    }
}
