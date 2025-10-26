<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 19 и 7 октября 2025 года
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
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
$no_image_found_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ МИНИАТЮР В РУБРИКАТОРЕ ДЛЯ СТАТЕЙ ОТ 19 И 7 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $current_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$current_thumbnail_id) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - нет главного изображения.\n";
        $no_image_found_count++;
        continue;
    }
    
    // Получаем информацию о текущем изображении
    $current_image_title = get_the_title($current_thumbnail_id);
    $current_image_path = get_attached_file($current_thumbnail_id);
    
    // Проверяем, является ли текущее изображение фавиконом или проблемным
    $is_favicon = false;
    if ($current_image_path && file_exists($current_image_path)) {
        // Проверяем размер файла (фавиконы обычно маленькие)
        $file_size = filesize($current_image_path);
        if ($file_size < 10000) { // Меньше 10KB - вероятно фавикон
            $is_favicon = true;
        }
    }
    
    // Проверяем, совпадает ли название изображения с названием статьи
    $title_matches = (strtolower(trim($current_image_title)) === strtolower(trim($post_title)));
    
    if (!$is_favicon && $title_matches) {
        // Изображение уже правильное
        echo "✓ Статья ID $post_id ($post_date): '$post_title' - уже имеет правильное изображение.\n";
        $already_correct_count++;
        continue;
    }
    
    // Ищем правильное изображение по названию
    $matching_images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'numberposts' => 1,
        'title' => $post_title,
        'fields' => 'ids'
    ));
    
    if (!empty($matching_images)) {
        $correct_image_id = $matching_images[0];
        $correct_image_path = get_attached_file($correct_image_id);
        
        // Проверяем, существует ли файл изображения
        if ($correct_image_path && file_exists($correct_image_path)) {
            // Обновляем _thumbnail_id
            update_post_meta($post_id, '_thumbnail_id', $correct_image_id);
            
            // Проверяем и исправляем путь к файлу изображения
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'];
            $base_url = $upload_dir['baseurl'];
            
            // Получаем правильный путь к файлу
            $relative_path = str_replace($base_dir . '/', '', $correct_image_path);
            $correct_url = $base_url . '/' . $relative_path;
            
            // Обновляем _wp_attached_file
            update_post_meta($correct_image_id, '_wp_attached_file', $relative_path);
            
            // Обновляем GUID
            wp_update_post(array(
                'ID' => $correct_image_id,
                'guid' => $correct_url
            ));
            
            // Регенерируем миниатюры
            wp_generate_attachment_metadata($correct_image_id, $correct_image_path);
            
            echo "✔ Исправлена миниатюра для статьи ID $post_id ($post_date): '$post_title' (Изображение ID: $correct_image_id)\n";
            $fixed_count++;
        } else {
            echo "✗ Файл изображения не найден для статьи ID $post_id ($post_date): '$post_title' (Изображение ID: $correct_image_id)\n";
            $no_image_found_count++;
        }
    } else {
        echo "✗ Не найдено подходящее изображение для статьи ID $post_id ($post_date): '$post_title'\n";
        $no_image_found_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено миниатюр: $fixed_count\n";
echo "Уже имели правильные изображения: $already_correct_count\n";
echo "Не найдено подходящих изображений: $no_image_found_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "\nКэш очищен.\n";
?>
