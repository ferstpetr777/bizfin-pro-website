<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 19 октября 2025
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 19
        )
    ),
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ ПУТЕЙ К WEBP ФАЙЛАМ ДЛЯ СТАТЕЙ ОТ 19 ОКТЯБРЯ ===\n\n";

$working_images = 0;
$broken_images = 0;
$no_images = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_images++;
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    $image_path = get_attached_file($thumbnail_id);
    if ($image_path && file_exists($image_path)) {
        $working_images++;
        echo "✓ Статья ID $post_id: '$post_title' - изображение работает\n";
    } else {
        $broken_images++;
        echo "✗ Статья ID $post_id: '$post_title' - изображение не найдено (путь: $image_path)\n";
    }
}

echo "\n=== ИТОГОВЫЕ РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Статей с рабочими изображениями: $working_images\n";
echo "Статей с проблемными изображениями: $broken_images\n";
echo "Статей без главного изображения: $no_images\n";
echo "Процент успешности: " . round(($working_images / count($posts)) * 100, 2) . "%\n";
