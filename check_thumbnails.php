<?php
require_once('wp-config.php');

// Получаем все статьи с главными изображениями
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$missing_thumbnails = array();
$total_posts = 0;
$posts_with_thumbnails = 0;

echo "=== ПРОВЕРКА МИНИАТЮР В РУБРИКАТОРЕ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    $total_posts++;
    
    if (!$thumbnail_id) {
        continue;
    }
    
    // Проверяем, существуют ли миниатюры разных размеров
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
        $missing_thumbnails[] = array(
            'post_id' => $post_id,
            'post_title' => $post_title,
            'thumbnail_id' => $thumbnail_id,
            'missing_sizes' => $missing_sizes
        );
        echo "✗ Отсутствуют миниатюры для статьи ID $post_id: '$post_title'\n";
        echo "  Отсутствующие размеры: " . implode(', ', $missing_sizes) . "\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПРОВЕРКИ ===\n";
echo "Всего статей: $total_posts\n";
echo "Статей с полными миниатюрами: $posts_with_thumbnails\n";
echo "Статей с отсутствующими миниатюрами: " . count($missing_thumbnails) . "\n";

if (!empty($missing_thumbnails)) {
    echo "\n=== СТАТЬИ С ОТСУТСТВУЮЩИМИ МИНИАТЮРАМИ ===\n";
    foreach ($missing_thumbnails as $missing) {
        echo "ID {$missing['post_id']}: {$missing['post_title']}\n";
        echo "  Отсутствующие размеры: " . implode(', ', $missing['missing_sizes']) . "\n";
    }
}
