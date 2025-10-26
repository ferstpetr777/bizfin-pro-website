<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ИСПРАВЛЕНИЕ ФАВИКОНОВ И ДУБЛИРОВАНИЙ В ГЛАВНЫХ ИЗОБРАЖЕНИЯХ ===\n\n";

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
        echo "Найден фавикон ID: $favicon_id - '$image_title'\n";
        break;
    }
}

$fixed_count = 0;
$favicon_removed = 0;
$duplicates_found = 0;
$image_usage = array(); // Для отслеживания использования изображений

echo "\n=== АНАЛИЗ И ИСПРАВЛЕНИЕ ===\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        echo "Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    // Проверяем, является ли это фавиконом
    if ($favicon_id && $thumbnail_id == $favicon_id) {
        echo "✗ Статья ID $post_id: '$post_title' - использует ФАВИКОН (ID: $thumbnail_id)\n";
        
        // Ищем подходящее изображение в медиатеке
        $matching_images = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        
        $best_match = null;
        $best_score = 0;
        
        foreach ($matching_images as $image_id) {
            if ($image_id == $favicon_id) continue; // Пропускаем фавикон
            
            $image_title = get_the_title($image_id);
            
            // Проверяем, не используется ли уже это изображение
            if (isset($image_usage[$image_id])) {
                continue; // Изображение уже используется
            }
            
            // Сравниваем первые 3 слова
            $post_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($post_title)), 0, 3);
            $image_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($image_title)), 0, 3);
            
            $match_score = 0;
            $min_length = min(count($post_words), count($image_words));
            
            for ($i = 0; $i < $min_length; $i++) {
                if ($post_words[$i] === $image_words[$i]) {
                    $match_score++;
                }
            }
            
            if ($match_score > $best_score) {
                $best_score = $match_score;
                $best_match = $image_id;
            }
        }
        
        if ($best_match) {
            set_post_thumbnail($post_id, $best_match);
            $image_usage[$best_match] = $post_id;
            echo "  ✔ Заменено на изображение ID $best_match: '" . get_the_title($best_match) . "' (совпадений: $best_score/3)\n";
            $fixed_count++;
        } else {
            echo "  ✗ Не найдено подходящее изображение\n";
        }
        
        $favicon_removed++;
    } else {
        // Проверяем на дублирование
        if (isset($image_usage[$thumbnail_id])) {
            echo "✗ Статья ID $post_id: '$post_title' - ДУБЛИРУЕТ изображение ID $thumbnail_id (уже используется в статье ID " . $image_usage[$thumbnail_id] . ")\n";
            
            // Ищем новое уникальное изображение
            $matching_images = get_posts(array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'numberposts' => -1,
                'fields' => 'ids'
            ));
            
            $best_match = null;
            $best_score = 0;
            
            foreach ($matching_images as $image_id) {
                if (isset($image_usage[$image_id])) continue; // Уже используется
                
                $image_title = get_the_title($image_id);
                
                // Сравниваем первые 3 слова
                $post_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($post_title)), 0, 3);
                $image_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($image_title)), 0, 3);
                
                $match_score = 0;
                $min_length = min(count($post_words), count($image_words));
                
                for ($i = 0; $i < $min_length; $i++) {
                    if ($post_words[$i] === $image_words[$i]) {
                        $match_score++;
                    }
                }
                
                if ($match_score > $best_score) {
                    $best_score = $match_score;
                    $best_match = $image_id;
                }
            }
            
            if ($best_match) {
                set_post_thumbnail($post_id, $best_match);
                $image_usage[$best_match] = $post_id;
                echo "  ✔ Заменено на уникальное изображение ID $best_match: '" . get_the_title($best_match) . "' (совпадений: $best_score/3)\n";
                $fixed_count++;
            } else {
                echo "  ✗ Не найдено подходящее уникальное изображение\n";
            }
            
            $duplicates_found++;
        } else {
            $image_usage[$thumbnail_id] = $post_id;
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено изображений: $fixed_count\n";
echo "Удалено фавиконов: $favicon_removed\n";
echo "Исправлено дублирований: $duplicates_found\n";
echo "Всего статей обработано: " . count($posts) . "\n";
