<?php
require_once('wp-config.php');

echo "=== ПОИСК ОРАНЖЕВЫХ ИЗОБРАЖЕНИЙ С БУКВОЙ 'У' ===\n\n";

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

$orange_images = array();

foreach ($images as $image_id) {
    $image_title = get_the_title($image_id);
    $image_path = get_attached_file($image_id);
    
    // Ищем изображения с буквой "У" в названии или оранжевые
    if (strpos(strtolower($image_title), 'у') !== false || 
        strpos(strtolower($image_title), 'orange') !== false ||
        strpos(strtolower($image_title), 'оранжев') !== false) {
        
        $orange_images[] = array(
            'id' => $image_id,
            'title' => $image_title,
            'path' => $image_path
        );
        
        echo "Найдено оранжевое изображение: ID $image_id - '$image_title'\n";
        echo "  Путь: $image_path\n";
        
        // Проверяем, используется ли это изображение как главное
        $posts_with_this_image = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_thumbnail_id',
                    'value' => $image_id,
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        ));
        
        if (!empty($posts_with_this_image)) {
            echo "  Используется как главное изображение в статьях: " . implode(', ', $posts_with_this_image) . "\n";
        }
        echo "\n";
    }
}

echo "=== РЕЗУЛЬТАТЫ ПОИСКА ===\n";
echo "Найдено оранжевых изображений: " . count($orange_images) . "\n";
