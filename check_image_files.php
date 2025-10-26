<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$with_files = 0;
$without_files = 0;
$missing_files = array();

echo "=== ПРОВЕРКА ФАЙЛОВ ГЛАВНЫХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($thumbnail_id);
    $image_title = get_the_title($thumbnail_id);
    
    if ($image_path && file_exists($image_path)) {
        $with_files++;
        echo "✓ Файл найден: $post_title -> $image_path\n";
    } else {
        $without_files++;
        $missing_files[] = array(
            'post_id' => $post_id,
            'post_title' => $post_title,
            'thumbnail_id' => $thumbnail_id,
            'image_title' => $image_title,
            'image_path' => $image_path
        );
        echo "✗ Файл не найден: $post_title (ID: $post_id) -> $image_path\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПРОВЕРКИ ===\n";
echo "Файлы найдены: $with_files\n";
echo "Файлы не найдены: $without_files\n\n";

if (!empty($missing_files)) {
    echo "=== СТАТЬИ С ОТСУТСТВУЮЩИМИ ФАЙЛАМИ ===\n";
    foreach ($missing_files as $missing) {
        echo "Статья ID {$missing['post_id']}: {$missing['post_title']}\n";
        echo "  -> Изображение ID {$missing['thumbnail_id']}: {$missing['image_title']}\n";
        echo "  -> Путь: {$missing['image_path']}\n\n";
    }
}
