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
    'numberposts' => -1,
    'fields' => 'ids'
));

$restored = 0;
$not_found = 0;
$already_have = 0;

echo "=== ВОССТАНОВЛЕНИЕ ГЛАВНЫХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    // Если уже есть главное изображение, пропускаем
    if ($thumbnail_id) {
        $already_have++;
        continue;
    }
    
    $found_image = false;
    
    // Ищем изображение с точно таким же названием
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        if ($image_title === $post_title) {
            // Устанавливаем главное изображение
            update_post_meta($post_id, '_thumbnail_id', $image_id);
            echo "✓ Восстановлено: $post_title (ID: $post_id) -> Изображение ID: $image_id\n";
            $restored++;
            $found_image = true;
            break;
        }
    }
    
    if (!$found_image) {
        $not_found++;
        echo "✗ Не найдено изображение для: $post_title (ID: $post_id)\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ВОССТАНОВЛЕНИЯ ===\n";
echo "Восстановлено главных изображений: $restored\n";
echo "Уже имели главные изображения: $already_have\n";
echo "Не найдено изображений: $not_found\n";
echo "Всего статей обработано: " . count($posts) . "\n";
