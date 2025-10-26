<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 19 и 7 октября 2025 года
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$favicon_articles = array();
$total_processed = 0;

echo "=== ПОИСК СТАТЕЙ С ФАВИКОНОМ В КАЧЕСТВЕ ГЛАВНОГО ИЗОБРАЖЕНИЯ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if ($thumbnail_id) {
        // Получаем информацию об изображении
        $image_title = get_the_title($thumbnail_id);
        $image_guid = get_post_field('guid', $thumbnail_id);
        $image_file = get_attached_file($thumbnail_id);
        
        // Проверяем, является ли это фавиконом
        $is_favicon = false;
        
        // Проверяем по названию
        if (stripos($image_title, 'favicon') !== false || 
            stripos($image_title, 'иконка') !== false ||
            stripos($image_title, 'icon') !== false) {
            $is_favicon = true;
        }
        
        // Проверяем по GUID (путь к файлу)
        if (stripos($image_guid, 'favicon') !== false || 
            stripos($image_guid, 'icon') !== false) {
            $is_favicon = true;
        }
        
        // Проверяем по пути к файлу
        if ($image_file && (stripos($image_file, 'favicon') !== false || 
            stripos($image_file, 'icon') !== false)) {
            $is_favicon = true;
        }
        
        // Проверяем размер изображения (фавиконы обычно маленькие)
        if ($image_file && file_exists($image_file)) {
            $image_size = getimagesize($image_file);
            if ($image_size && ($image_size[0] <= 64 || $image_size[1] <= 64)) {
                $is_favicon = true;
            }
        }
        
        if ($is_favicon) {
            $favicon_articles[] = array(
                'post_id' => $post_id,
                'post_title' => $post_title,
                'post_date' => $post_date,
                'thumbnail_id' => $thumbnail_id,
                'image_title' => $image_title,
                'image_guid' => $image_guid,
                'image_file' => $image_file
            );
            
            echo "✓ Найден фавикон в статье ID $post_id ($post_date): '$post_title'\n";
            echo "  - Изображение ID: $thumbnail_id\n";
            echo "  - Название изображения: '$image_title'\n";
            echo "  - GUID: $image_guid\n";
            echo "  - Файл: $image_file\n\n";
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПОИСКА ===\n";
echo "Всего статей обработано: $total_processed\n";
echo "Статей с фавиконом в качестве главного изображения: " . count($favicon_articles) . "\n\n";

if (!empty($favicon_articles)) {
    echo "=== ДЕТАЛЬНЫЙ СПИСОК СТАТЕЙ С ФАВИКОНОМ ===\n\n";
    
    foreach ($favicon_articles as $article) {
        echo "ID статьи: {$article['post_id']}\n";
        echo "Название: {$article['post_title']}\n";
        echo "Дата создания: {$article['post_date']}\n";
        echo "ID изображения: {$article['thumbnail_id']}\n";
        echo "Название изображения: {$article['image_title']}\n";
        echo "GUID изображения: {$article['image_guid']}\n";
        echo "Файл изображения: {$article['image_file']}\n";
        echo "---\n";
    }
}
?>
