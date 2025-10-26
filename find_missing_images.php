<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$missing_images = array();
$total_posts = 0;

echo "=== ПОИСК СТАТЕЙ БЕЗ ГЛАВНЫХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $total_posts++;
    
    if (!$thumbnail_id) {
        $missing_images[] = array(
            'id' => $post_id,
            'title' => $post_title
        );
        echo "Статья без главного изображения: ID $post_id - '$post_title'\n";
    } else {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        if (!$image_path || !file_exists($image_path)) {
            $missing_images[] = array(
                'id' => $post_id,
                'title' => $post_title
            );
            echo "Статья с битым изображением: ID $post_id - '$post_title'\n";
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей: $total_posts\n";
echo "Статей без главных изображений: " . count($missing_images) . "\n";

if (!empty($missing_images)) {
    echo "\n=== СПИСОК СТАТЕЙ БЕЗ ИЗОБРАЖЕНИЙ ===\n";
    foreach ($missing_images as $missing) {
        echo "ID {$missing['id']}: {$missing['title']}\n";
    }
}
