<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 23 октября 2025
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 23
        )
    ),
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА ГЛАВНЫХ ИЗОБРАЖЕНИЙ ДЛЯ СТАТЕЙ ОТ 23 ОКТЯБРЯ ===\n\n";

$with_images = 0;
$without_images = 0;
$broken_images = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if ($thumbnail_id) {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($thumbnail_id);
        if ($image_path && file_exists($image_path)) {
            $with_images++;
            echo "✓ Статья ID $post_id: '$post_title' - имеет главное изображение (ID: $thumbnail_id)\n";
        } else {
            $broken_images++;
            echo "✗ Статья ID $post_id: '$post_title' - главное изображение отсутствует (ID: $thumbnail_id, файл: $image_path)\n";
        }
    } else {
        $without_images++;
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей от 23 октября: " . count($posts) . "\n";
echo "Статей с главными изображениями: $with_images\n";
echo "Статей без главных изображений: $without_images\n";
echo "Статей с отсутствующими файлами изображений: $broken_images\n";
echo "Процент статей с изображениями: " . round((($with_images + $broken_images) / count($posts)) * 100, 2) . "%\n";
