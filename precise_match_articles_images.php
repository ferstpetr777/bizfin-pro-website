<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

$matched = 0;
$not_matched = 0;
$matches = array();

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    // Если уже есть главное изображение, пропускаем
    if ($thumbnail_id) {
        continue;
    }
    
    $found_image = false;
    
    // Ищем изображение с точно таким же названием
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        if ($image_title === $post_title) {
            $matches[] = array(
                'post_id' => $post_id,
                'post_title' => $post_title,
                'image_id' => $image_id,
                'image_title' => $image_title,
                'match_type' => 'exact'
            );
            $matched++;
            $found_image = true;
            break;
        }
    }
    
    if (!$found_image) {
        $not_matched++;
        echo "Не найдено точное совпадение для: $post_title (ID: $post_id)\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ СОПОСТАВЛЕНИЯ ===\n";
echo "Найдено точных совпадений: $matched\n";
echo "Не найдено изображений: $not_matched\n\n";

if (!empty($matches)) {
    echo "=== НАЙДЕННЫЕ ТОЧНЫЕ СОВПАДЕНИЯ ===\n";
    foreach ($matches as $match) {
        echo "Статья ID {$match['post_id']}: {$match['post_title']}\n";
        echo "  -> Изображение ID {$match['image_id']}: {$match['image_title']}\n";
        echo "  -> Тип совпадения: {$match['match_type']}\n\n";
    }
}
