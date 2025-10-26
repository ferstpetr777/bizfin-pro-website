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
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== СОПОСТАВЛЕНИЕ СТАТЕЙ И ИЗОБРАЖЕНИЙ ===\n\n";

$matched = 0;
$unmatched = 0;
$matches = array();

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d H:i:s', $post_id);
    $found_match = false;
    
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        
        if ($post_title === $image_title) {
            $matches[] = array(
                'post_id' => $post_id,
                'image_id' => $image_id,
                'title' => $post_title,
                'date' => $post_date
            );
            echo "✓ СОВПАДЕНИЕ: Статья ID $post_id ($post_date) -> Изображение ID $image_id\n";
            echo "  Название: '$post_title'\n\n";
            $matched++;
            $found_match = true;
            break;
        }
    }
    
    if (!$found_match) {
        echo "✗ НЕТ СОВПАДЕНИЯ: Статья ID $post_id ($post_date)\n";
        echo "  Название: '$post_title'\n\n";
        $unmatched++;
    }
}

echo "=== РЕЗУЛЬТАТЫ ===\n";
echo "Найдено совпадений: $matched\n";
echo "Не найдено совпадений: $unmatched\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Всего изображений: " . count($images) . "\n";

echo "\n=== СПИСОК СОВПАДЕНИЙ ===\n";
foreach ($matches as $match) {
    echo "Статья ID {$match['post_id']} ({$match['date']}) -> Изображение ID {$match['image_id']}: '{$match['title']}'\n";
}

echo "\n=== СТАТИСТИКА ПО ДАТАМ ===\n";
$dates_stats = array();
foreach ($matches as $match) {
    $date = substr($match['date'], 0, 10); // Берем только дату без времени
    if (!isset($dates_stats[$date])) {
        $dates_stats[$date] = 0;
    }
    $dates_stats[$date]++;
}

foreach ($dates_stats as $date => $count) {
    echo "$date: $count совпадений\n";
}
