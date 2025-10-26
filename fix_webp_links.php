<?php
require_once('wp-config.php');

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed = 0;
$not_fixed = 0;
$errors = array();

echo "=== ИСПРАВЛЕНИЕ ССЫЛОК ПОСЛЕ МИГРАЦИИ В WEBP ===\n\n";

foreach ($images as $image_id) {
    $image_title = get_the_title($image_id);
    $current_guid = get_post_field('guid', $image_id);
    $current_file = get_attached_file($image_id);
    
    // Проверяем, существует ли файл
    if ($current_file && file_exists($current_file)) {
        echo "✓ Файл существует: $image_title -> $current_file\n";
        continue;
    }
    
    // Ищем WebP версию файла
    $webp_file = null;
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'];
    
    // Ищем файл по названию в разных форматах
    $possible_names = array(
        $image_title . '.webp',
        $image_title . '.png',
        $image_title . '.jpg',
        $image_title . '.jpeg'
    );
    
    foreach ($possible_names as $filename) {
        $file_path = $base_dir . '/' . $filename;
        if (file_exists($file_path)) {
            $webp_file = $file_path;
            break;
        }
    }
    
    // Если не нашли по названию, ищем в папках
    if (!$webp_file) {
        $search_dirs = array(
            $base_dir . '/2025/09/',
            $base_dir . '/2025/08/',
            $base_dir . '/2025/07/',
            $base_dir . '/2025/06/'
        );
        
        foreach ($search_dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*');
                foreach ($files as $file) {
                    if (is_file($file) && (strpos($file, '.webp') !== false || strpos($file, '.png') !== false || strpos($file, '.jpg') !== false)) {
                        // Проверяем, содержит ли имя файла часть названия изображения
                        $basename = basename($file);
                        $title_words = explode(' ', $image_title);
                        $match_count = 0;
                        
                        foreach ($title_words as $word) {
                            if (strpos($basename, $word) !== false) {
                                $match_count++;
                            }
                        }
                        
                        if ($match_count >= 2) { // Если совпадает минимум 2 слова
                            $webp_file = $file;
                            break 2;
                        }
                    }
                }
            }
        }
    }
    
    if ($webp_file) {
        // Обновляем метаданные файла
        $relative_path = str_replace($base_dir . '/', '', $webp_file);
        $file_url = $upload_dir['baseurl'] . '/' . $relative_path;
        
        // Обновляем guid
        wp_update_post(array(
            'ID' => $image_id,
            'guid' => $file_url
        ));
        
        // Обновляем метаданные файла
        update_post_meta($image_id, '_wp_attached_file', $relative_path);
        
        // Обновляем метаданные изображения
        $file_info = pathinfo($webp_file);
        update_post_meta($image_id, '_wp_attachment_metadata', array(
            'width' => 800,
            'height' => 600,
            'file' => $relative_path
        ));
        
        echo "✓ Исправлено: $image_title -> $webp_file\n";
        $fixed++;
    } else {
        echo "✗ Не найдено: $image_title\n";
        $not_fixed++;
        $errors[] = $image_title;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено файлов: $fixed\n";
echo "Не найдено файлов: $not_fixed\n";

if (!empty($errors)) {
    echo "\n=== ФАЙЛЫ, КОТОРЫЕ НЕ УДАЛОСЬ НАЙТИ ===\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
