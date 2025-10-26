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

echo "=== ИСПРАВЛЕНИЕ ПУТЕЙ К WEBP ФАЙЛАМ ДЛЯ СТАТЕЙ ОТ 23 ОКТЯБРЯ ===\n\n";

$fixed = 0;
$already_correct = 0;
$no_thumbnail = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_thumbnail++;
        echo "Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    $current_file = get_attached_file($thumbnail_id);
    $current_guid = get_post_field('guid', $thumbnail_id);
    
    // Проверяем, есть ли дублирование в пути
    if (strpos($current_file, '/wp-content/uploads/wp-content/uploads/') !== false) {
        // Исправляем путь, убирая дублирование
        $corrected_file = str_replace('/wp-content/uploads/wp-content/uploads/', '/wp-content/uploads/', $current_file);
        $corrected_guid = str_replace('/wp-content/uploads/wp-content/uploads/', '/wp-content/uploads/', $current_guid);
        
        // Обновляем _wp_attached_file
        update_post_meta($thumbnail_id, '_wp_attached_file', str_replace('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/', '', $corrected_file));
        
        // Обновляем GUID
        wp_update_post(array(
            'ID' => $thumbnail_id,
            'guid' => $corrected_guid
        ));
        
        echo "✔ Исправлен путь для статьи ID $post_id: '$post_title'\n";
        echo "  Старый путь: $current_file\n";
        echo "  Новый путь: $corrected_file\n\n";
        $fixed++;
    } else {
        $already_correct++;
    }
}

echo "=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено путей: $fixed\n";
echo "Уже были корректными: $already_correct\n";
echo "Без главного изображения: $no_thumbnail\n";
echo "Всего статей обработано: " . count($posts) . "\n";
