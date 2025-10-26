<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ФИНАЛЬНАЯ ПРОВЕРКА УНИКАЛЬНОСТИ И СООТВЕТСТВИЯ ИЗОБРАЖЕНИЙ ===\n\n";

$image_usage = array();
$favicon_usage = 0;
$duplicates = 0;
$no_images = 0;
$good_matches = 0;

// Найдем ID фавикона
$favicon_id = null;
$favicon_posts = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

foreach ($favicon_posts as $image_id) {
    $image_title = get_the_title($image_id);
    if (strpos(strtolower($image_title), 'фавикон') !== false || 
        strpos(strtolower($image_title), 'favicon') !== false) {
        $favicon_id = $image_id;
        break;
    }
}

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_images++;
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    // Проверяем фавикон
    if ($favicon_id && $thumbnail_id == $favicon_id) {
        $favicon_usage++;
        echo "✗ Статья ID $post_id: '$post_title' - использует ФАВИКОН (ID: $thumbnail_id)\n";
    }
    
    // Проверяем дублирование
    if (isset($image_usage[$thumbnail_id])) {
        $duplicates++;
        echo "✗ Статья ID $post_id: '$post_title' - ДУБЛИРУЕТ изображение ID $thumbnail_id (уже используется в статье ID " . $image_usage[$thumbnail_id] . ")\n";
    } else {
        $image_usage[$thumbnail_id] = $post_id;
        
        // Проверяем соответствие названий
        $image_title = get_the_title($thumbnail_id);
        $post_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($post_title)), 0, 3);
        $image_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($image_title)), 0, 3);
        
        $match_score = 0;
        $min_length = min(count($post_words), count($image_words));
        
        for ($i = 0; $i < $min_length; $i++) {
            if ($post_words[$i] === $image_words[$i]) {
                $match_score++;
            }
        }
        
        if ($match_score >= 2) {
            $good_matches++;
            echo "✓ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - ХОРОШЕЕ СООТВЕТСТВИЕ ($match_score/3)\n";
        } else {
            echo "~ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - СЛАБОЕ СООТВЕТСТВИЕ ($match_score/3)\n";
        }
    }
}

echo "\n=== ИТОГОВЫЕ РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Статей с хорошими соответствиями: $good_matches\n";
echo "Статей без главного изображения: $no_images\n";
echo "Статей с фавиконом: $favicon_usage\n";
echo "Дублирований изображений: $duplicates\n";
echo "Уникальных изображений: " . count($image_usage) . "\n";
echo "Процент хороших соответствий: " . round(($good_matches / count($posts)) * 100, 2) . "%\n";
