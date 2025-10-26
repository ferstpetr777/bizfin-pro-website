<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА СООТВЕТСТВИЯ ЗАГОЛОВКОВ СТАТЕЙ И НАЗВАНИЙ ИЗОБРАЖЕНИЙ ===\n\n";

$exact_matches = 0;
$no_thumbnail = 0;
$title_mismatches = 0;
$broken_images = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_thumbnail++;
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($thumbnail_id);
    if (!$image_path || !file_exists($image_path)) {
        $broken_images++;
        echo "✗ Статья ID $post_id: '$post_title' - файл изображения отсутствует (ID: $thumbnail_id)\n";
        continue;
    }
    
    // Получаем название изображения
    $image_title = get_the_title($thumbnail_id);
    
    // Сравниваем заголовок статьи и название изображения
    if ($post_title === $image_title) {
        $exact_matches++;
        echo "✓ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - ТОЧНОЕ СООТВЕТСТВИЕ\n";
    } else {
        $title_mismatches++;
        echo "✗ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - НЕ СООТВЕТСТВУЕТ\n";
    }
}

echo "\n=== ИТОГОВЫЕ РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Точных соответствий заголовков: $exact_matches\n";
echo "Несоответствий заголовков: $title_mismatches\n";
echo "Статей без главного изображения: $no_thumbnail\n";
echo "Статей с отсутствующими файлами изображений: $broken_images\n";
echo "Процент точных соответствий: " . round(($exact_matches / count($posts)) * 100, 2) . "%\n";
