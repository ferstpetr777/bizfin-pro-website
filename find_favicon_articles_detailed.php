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
        $reasons = array();
        
        // Проверяем по названию файла
        if (stripos($image_file, 'cropped-Бизнес-Финанс-—-фавикон') !== false) {
            $is_favicon = true;
            $reasons[] = "файл содержит 'cropped-Бизнес-Финанс-—-фавикон'";
        }
        
        // Проверяем по названию изображения
        if (stripos($image_title, 'фавикон') !== false || 
            stripos($image_title, 'favicon') !== false) {
            $is_favicon = true;
            $reasons[] = "название содержит 'фавикон' или 'favicon'";
        }
        
        // Проверяем по GUID
        if (stripos($image_guid, 'favicon') !== false || 
            stripos($image_guid, 'фавикон') !== false) {
            $is_favicon = true;
            $reasons[] = "GUID содержит 'favicon' или 'фавикон'";
        }
        
        // Проверяем размер изображения (фавиконы обычно маленькие)
        if ($image_file && file_exists($image_file)) {
            $image_size = getimagesize($image_file);
            if ($image_size && ($image_size[0] <= 150 || $image_size[1] <= 150)) {
                // Дополнительная проверка - если размер маленький И название содержит подозрительные слова
                if (stripos($image_file, 'cropped') !== false || 
                    stripos($image_title, 'иконка') !== false ||
                    stripos($image_title, 'icon') !== false) {
                    $is_favicon = true;
                    $reasons[] = "маленький размер {$image_size[0]}x{$image_size[1]} + подозрительное название";
                }
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
                'image_file' => $image_file,
                'reasons' => $reasons
            );
            
            echo "✓ НАЙДЕН ФАВИКОН в статье ID $post_id ($post_date): '$post_title'\n";
            echo "  - Изображение ID: $thumbnail_id\n";
            echo "  - Название изображения: '$image_title'\n";
            echo "  - GUID: $image_guid\n";
            echo "  - Файл: $image_file\n";
            echo "  - Причины: " . implode(', ', $reasons) . "\n\n";
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
        echo "Причины определения как фавикон: " . implode(', ', $article['reasons']) . "\n";
        echo "---\n";
    }
    
    echo "\n=== СВОДНАЯ ТАБЛИЦА ===\n";
    echo "| ID статьи | Название статьи | Дата | ID изображения | Файл изображения |\n";
    echo "|-----------|-----------------|------|-----------------|------------------|\n";
    
    foreach ($favicon_articles as $article) {
        $short_title = mb_substr($article['post_title'], 0, 50) . (mb_strlen($article['post_title']) > 50 ? '...' : '');
        $short_file = basename($article['image_file']);
        echo "| {$article['post_id']} | $short_title | {$article['post_date']} | {$article['thumbnail_id']} | $short_file |\n";
    }
}
?>
