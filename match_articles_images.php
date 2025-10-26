<?php
require_once('wp-config.php');

// Получаем все статьи без главных изображений
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$missing_images = array();
$total_posts = 0;

// Собираем статьи без изображений
foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $total_posts++;
    
    if (!$thumbnail_id) {
        $missing_images[] = array(
            'id' => $post_id,
            'title' => $post_title
        );
    } else {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        if (!$image_path || !file_exists($image_path)) {
            $missing_images[] = array(
                'id' => $post_id,
                'title' => $post_title
            );
        }
    }
}

// Получаем все изображения из медиатеки
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

$matched_count = 0;
$unmatched_articles = array();
$matched_articles = array();

echo "=== ПОИСК СООТВЕТСТВУЮЩИХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($missing_images as $missing) {
    $post_id = $missing['id'];
    $post_title = $missing['title'];
    
    $found_match = false;
    $best_match_image_id = '';
    $best_match_image_title = '';
    
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        
        // Точное совпадение названий
        if ($post_title == $image_title) {
            $matched_count++;
            $matched_articles[] = array(
                'post_id' => $post_id,
                'post_title' => $post_title,
                'image_id' => $image_id,
                'image_title' => $image_title
            );
            echo "✓ Найдено точное совпадение:\n";
            echo "  Статья ID $post_id: '$post_title'\n";
            echo "  -> Изображение ID $image_id: '$image_title'\n\n";
            $found_match = true;
            break;
        }
    }
    
    if (!$found_match) {
        $unmatched_articles[] = $missing;
    }
}

echo "=== РЕЗУЛЬТАТЫ ПОИСКА ===\n";
echo "Найдено точных совпадений: $matched_count\n";
echo "Статей без совпадений: " . count($unmatched_articles) . "\n\n";

if (!empty($matched_articles)) {
    echo "=== НАЙДЕННЫЕ СОВПАДЕНИЯ ===\n";
    foreach ($matched_articles as $match) {
        echo "Статья ID {$match['post_id']}: {$match['post_title']}\n";
        echo "  -> Изображение ID {$match['image_id']}: {$match['image_title']}\n\n";
    }
}

if (!empty($unmatched_articles)) {
    echo "=== СТАТЬИ БЕЗ СОВПАДЕНИЙ ===\n";
    foreach ($unmatched_articles as $unmatched) {
        echo "ID {$unmatched['id']}: {$unmatched['title']}\n";
    }
}
