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

$total_processed = 0;
$articles_with_images = array();
$articles_without_images = array();

echo "=== ПОЛНЫЙ СПИСОК СТАТЕЙ ОТ 19 И 7 ОКТЯБРЯ ===\n\n";

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
        $image_exists = $image_file && file_exists($image_file);
        
        $articles_with_images[] = array(
            'post_id' => $post_id,
            'post_title' => $post_title,
            'post_date' => $post_date,
            'thumbnail_id' => $thumbnail_id,
            'image_title' => $image_title,
            'image_guid' => $image_guid,
            'image_file' => $image_file,
            'image_exists' => $image_exists
        );
        
        echo "✓ Статья ID $post_id ($post_date): '$post_title'\n";
        echo "  - Изображение ID: $thumbnail_id\n";
        echo "  - Название изображения: '$image_title'\n";
        echo "  - GUID: $image_guid\n";
        echo "  - Файл: $image_file\n";
        echo "  - Файл существует: " . ($image_exists ? 'ДА' : 'НЕТ') . "\n\n";
    } else {
        $articles_without_images[] = array(
            'post_id' => $post_id,
            'post_title' => $post_title,
            'post_date' => $post_date
        );
        
        echo "✗ Статья ID $post_id ($post_date): '$post_title' - НЕТ ГЛАВНОГО ИЗОБРАЖЕНИЯ\n\n";
    }
}

echo "\n=== СВОДКА ===\n";
echo "Всего статей обработано: $total_processed\n";
echo "Статей с главными изображениями: " . count($articles_with_images) . "\n";
echo "Статей без главных изображений: " . count($articles_without_images) . "\n\n";

// Проверим, есть ли изображения с подозрительными названиями
echo "=== ПОИСК ПОДОЗРИТЕЛЬНЫХ ИЗОБРАЖЕНИЙ ===\n\n";
$suspicious_images = array();

foreach ($articles_with_images as $article) {
    $image_title = $article['image_title'];
    $image_guid = $article['image_guid'];
    $image_file = $article['image_file'];
    
    $is_suspicious = false;
    $reasons = array();
    
    // Проверяем по названию
    if (stripos($image_title, 'favicon') !== false) {
        $is_suspicious = true;
        $reasons[] = "название содержит 'favicon'";
    }
    if (stripos($image_title, 'иконка') !== false) {
        $is_suspicious = true;
        $reasons[] = "название содержит 'иконка'";
    }
    if (stripos($image_title, 'icon') !== false) {
        $is_suspicious = true;
        $reasons[] = "название содержит 'icon'";
    }
    if (stripos($image_title, 'logo') !== false) {
        $is_suspicious = true;
        $reasons[] = "название содержит 'logo'";
    }
    
    // Проверяем по GUID
    if (stripos($image_guid, 'favicon') !== false) {
        $is_suspicious = true;
        $reasons[] = "GUID содержит 'favicon'";
    }
    if (stripos($image_guid, 'icon') !== false) {
        $is_suspicious = true;
        $reasons[] = "GUID содержит 'icon'";
    }
    
    // Проверяем по файлу
    if ($image_file && stripos($image_file, 'favicon') !== false) {
        $is_suspicious = true;
        $reasons[] = "файл содержит 'favicon'";
    }
    if ($image_file && stripos($image_file, 'icon') !== false) {
        $is_suspicious = true;
        $reasons[] = "файл содержит 'icon'";
    }
    
    // Проверяем размер изображения
    if ($article['image_exists'] && $image_file) {
        $image_size = getimagesize($image_file);
        if ($image_size && ($image_size[0] <= 64 || $image_size[1] <= 64)) {
            $is_suspicious = true;
            $reasons[] = "размер изображения {$image_size[0]}x{$image_size[1]} (маленький)";
        }
    }
    
    if ($is_suspicious) {
        $suspicious_images[] = $article;
        echo "⚠️  ПОДОЗРИТЕЛЬНОЕ ИЗОБРАЖЕНИЕ в статье ID {$article['post_id']} ({$article['post_date']}): '{$article['post_title']}'\n";
        echo "  - Изображение ID: {$article['thumbnail_id']}\n";
        echo "  - Название изображения: '$image_title'\n";
        echo "  - GUID: $image_guid\n";
        echo "  - Файл: $image_file\n";
        echo "  - Причины: " . implode(', ', $reasons) . "\n\n";
    }
}

echo "\n=== ИТОГОВАЯ СВОДКА ===\n";
echo "Всего статей: $total_processed\n";
echo "С главными изображениями: " . count($articles_with_images) . "\n";
echo "Без главных изображений: " . count($articles_without_images) . "\n";
echo "С подозрительными изображениями: " . count($suspicious_images) . "\n";
?>
