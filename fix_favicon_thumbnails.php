<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed_count = 0;
$no_image_found_count = 0;
$already_correct_count = 0;

echo "=== ИСПРАВЛЕНИЕ ФАВИКОНОВ В МИНИАТЮРАХ СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        continue;
    }
    
    // Получаем информацию об изображении
    $image_title = get_the_title($thumbnail_id);
    $image_path = get_attached_file($thumbnail_id);
    $image_guid = get_post_field('guid', $thumbnail_id);
    
    // Проверяем, является ли это фавиконом
    $is_favicon = false;
    if (strpos($image_title, 'favicon') !== false || 
        strpos($image_title, 'фавикон') !== false ||
        strpos($image_path, 'favicon') !== false ||
        strpos($image_path, 'фавикон') !== false ||
        strpos($image_guid, 'favicon') !== false ||
        strpos($image_guid, 'фавикон') !== false ||
        strpos($image_path, 'cropped') !== false) {
        $is_favicon = true;
    }
    
    if ($is_favicon) {
        echo "✗ Найден фавикон в статье ID $post_id: '$post_title'\n";
        echo "  Изображение ID: $thumbnail_id, Название: '$image_title'\n";
        
        // Ищем подходящее изображение по названию статьи
        $matching_images = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'title' => $post_title,
            'fields' => 'ids'
        ));
        
        if (!empty($matching_images)) {
            $new_image_id = $matching_images[0];
            $new_image_title = get_the_title($new_image_id);
            
            // Проверяем, что новое изображение не является фавиконом
            if (strpos($new_image_title, 'favicon') === false && 
                strpos($new_image_title, 'фавикон') === false) {
                
                set_post_thumbnail($post_id, $new_image_id);
                echo "✔ Заменен фавикон на правильное изображение ID $new_image_id: '$new_image_title'\n";
                $fixed_count++;
            } else {
                echo "✗ Найденное изображение также является фавиконом\n";
                $no_image_found_count++;
            }
        } else {
            echo "✗ Не найдено подходящее изображение для статьи\n";
            $no_image_found_count++;
        }
        echo "\n";
    } else {
        $already_correct_count++;
    }
}

echo "=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено фавиконов: $fixed_count\n";
echo "Не найдено подходящих изображений: $no_image_found_count\n";
echo "Уже имели правильные изображения: $already_correct_count\n";
?>
