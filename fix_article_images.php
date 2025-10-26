<?php
require_once('wp-config.php');

// Получаем все статьи, созданные до 23 октября 2025
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'before' => '2025-10-23 00:00:00',
            'inclusive' => true
        )
    ),
    'fields' => 'ids'
));

$fixed = 0;
$already_had_image = 0;
$no_image_found = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ ГЛАВНЫХ ИЗОБРАЖЕНИЙ СТАТЕЙ ДО 23 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $total_processed++;
    
    // Проверяем, есть ли уже главное изображение
    $current_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if ($current_thumbnail_id) {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($current_thumbnail_id);
        if ($image_path && file_exists($image_path)) {
            echo "✓ Статья ID $post_id: '$post_title' уже имеет корректное главное изображение\n";
            $already_had_image++;
            continue;
        } else {
            echo "⚠ Статья ID $post_id: '$post_title' имеет главное изображение, но файл не найден\n";
        }
    }
    
    // Ищем подходящее изображение по названию
    $matching_image_id = null;
    
    // Получаем все изображения
    $images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'numberposts' => -1,
        'fields' => 'ids'
    ));
    
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        
        // Точное совпадение названия
        if ($post_title === $image_title) {
            $matching_image_id = $image_id;
            break;
        }
    }
    
    // Если точного совпадения нет, ищем частичное
    if (!$matching_image_id) {
        $post_words = explode(' ', $post_title);
        $best_match_id = null;
        $best_match_score = 0;
        
        foreach ($images as $image_id) {
            $image_title = get_the_title($image_id);
            $image_words = explode(' ', $image_title);
            
            $common_words = 0;
            foreach ($post_words as $post_word) {
                foreach ($image_words as $image_word) {
                    if (strtolower($post_word) === strtolower($image_word)) {
                        $common_words++;
                        break;
                    }
                }
            }
            
            if ($common_words > $best_match_score && $common_words >= 2) {
                $best_match_score = $common_words;
                $best_match_id = $image_id;
            }
        }
        
        if ($best_match_id) {
            $matching_image_id = $best_match_id;
        }
    }
    
    if ($matching_image_id) {
        // Проверяем, существует ли файл изображения
        $image_path = get_attached_file($matching_image_id);
        if ($image_path && file_exists($image_path)) {
            // Устанавливаем главное изображение
            update_post_meta($post_id, '_thumbnail_id', $matching_image_id);
            echo "✔ Исправлено: Статья ID $post_id: '$post_title' -> Изображение ID $matching_image_id\n";
            $fixed++;
        } else {
            echo "✗ Файл изображения не найден для: $post_title (ID: $post_id)\n";
            $no_image_found++;
        }
    } else {
        echo "✗ Не найдено подходящее изображение для: $post_title (ID: $post_id)\n";
        $no_image_found++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено главных изображений: $fixed\n";
echo "Уже имели корректные изображения: $already_had_image\n";
echo "Не найдено изображений: $no_image_found\n";
echo "Всего статей обработано: $total_processed\n";
