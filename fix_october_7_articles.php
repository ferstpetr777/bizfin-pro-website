<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 7 октября 2025 года
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$fixed_count = 0;
$already_correct_count = 0;
$not_found_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ СТАТЕЙ ОТ 7 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $current_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);

    // Проверяем, есть ли уже правильное главное изображение
    if ($current_thumbnail_id) {
        $image_path = get_attached_file($current_thumbnail_id);
        if ($image_path && file_exists($image_path) && strpos($image_path, 'favicon') === false) {
            echo "✓ Статья ID $post_id ($post_date): '$post_title' - уже имеет правильное изображение.\n";
            $already_correct_count++;
            continue;
        }
    }

    // Ищем изображение по названию (точное совпадение)
    $matching_images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'numberposts' => 1,
        'title' => $post_title,
        'fields' => 'ids'
    ));

    if (!empty($matching_images)) {
        $image_id = $matching_images[0];
        set_post_thumbnail($post_id, $image_id);
        echo "✔ Исправлена миниатюра для статьи ID $post_id ($post_date): '$post_title' (Изображение ID: $image_id)\n";
        $fixed_count++;
    } else {
        echo "✗ Не найдено подходящее изображение для статьи ID $post_id ($post_date): '$post_title'\n";
        $not_found_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено миниатюр: $fixed_count\n";
echo "Уже имели правильные изображения: $already_correct_count\n";
echo "Не найдено подходящих изображений: $not_found_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш после всех операций
wp_cache_flush();
echo "Кэш очищен.\n";
