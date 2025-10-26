<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ПРАВИЛЬНОЕ ИСПРАВЛЕНИЕ FEATURED IMAGES ===\n\n";

// Получаем все статьи
$articles = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
));

$fixed_count = 0;
$not_found_count = 0;

foreach ($articles as $article) {
    $post_id = $article->ID;
    $post_title = $article->post_title;
    $thumbnail_id = get_post_thumbnail_id($post_id);
    
    if ($thumbnail_id) {
        $file_path = get_attached_file($thumbnail_id);
        
        if (!file_exists($file_path)) {
            echo "Исправляем: " . $post_title . " (ID: " . $post_id . ")\n";
            echo "  Битый файл: " . $file_path . "\n";
            
            // Ищем WebP версию в правильной папке
            $filename = basename($file_path);
            $base_name = preg_replace('/\.(png|jpg|jpeg)$/i', '', $filename);
            $webp_filename = $base_name . '.webp';
            
            // Ищем в папке 2025/10/
            $search_paths = array(
                ABSPATH . 'wp-content/uploads/2025/10/' . $webp_filename,
                ABSPATH . 'wp-content/uploads/2025/10/' . $base_name . '-768x768.webp',
                ABSPATH . 'wp-content/uploads/2025/10/' . $base_name . '-1024x1024.webp',
            );
            
            $found_file = null;
            foreach ($search_paths as $search_path) {
                if (file_exists($search_path)) {
                    $found_file = $search_path;
                    break;
                }
            }
            
            if ($found_file) {
                $webp_relative_path = str_replace(ABSPATH, '', $found_file);
                echo "  ✅ Найден WebP: " . $webp_relative_path . "\n";
                
                // Обновляем _wp_attached_file
                update_post_meta($thumbnail_id, '_wp_attached_file', $webp_relative_path);
                
                // Обновляем MIME тип
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'post_mime_type' => 'image/webp'
                ));
                
                // Обновляем GUID
                $webp_url = home_url($webp_relative_path);
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'guid' => $webp_url
                ));
                
                // Обновляем метаданные
                $metadata = wp_get_attachment_metadata($thumbnail_id);
                if ($metadata) {
                    $metadata['file'] = $webp_relative_path;
                    $metadata['sizes']['full']['file'] = basename($webp_relative_path);
                    $metadata['sizes']['full']['mime-type'] = 'image/webp';
                    
                    // Обновляем размеры файла
                    $file_size = filesize($found_file);
                    if ($file_size) {
                        $metadata['filesize'] = $file_size;
                    }
                    
                    wp_update_attachment_metadata($thumbnail_id, $metadata);
                }
                
                $fixed_count++;
                echo "  ✅ Featured image исправлена!\n\n";
            } else {
                echo "  ❌ WebP файл не найден: " . $webp_filename . "\n";
                $not_found_count++;
                
                // Попробуем найти любое изображение с похожим названием
                $uploads_dir = wp_upload_dir();
                $search_pattern = $uploads_dir['basedir'] . '/2025/10/' . $base_name . '*.webp';
                $found_files = glob($search_pattern);
                
                if (!empty($found_files)) {
                    $found_file = $found_files[0];
                    $found_relative = str_replace(ABSPATH, '', $found_file);
                    echo "  🔍 Найден альтернативный файл: " . $found_relative . "\n";
                    
                    // Обновляем на найденный файл
                    update_post_meta($thumbnail_id, '_wp_attached_file', $found_relative);
                    
                    $found_url = home_url($found_relative);
                    wp_update_post(array(
                        'ID' => $thumbnail_id,
                        'guid' => $found_url,
                        'post_mime_type' => 'image/webp'
                    ));
                    
                    echo "  ✅ Обновлено на альтернативный файл\n";
                    $fixed_count++;
                } else {
                    echo "  ❌ Альтернативные файлы не найдены\n";
                }
                echo "\n";
            }
        }
    }
}

echo "=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено featured images: " . $fixed_count . "\n";
echo "Не найдено файлов: " . $not_found_count . "\n";

// Проверяем несколько статей после исправления
echo "\n=== ПРОВЕРКА РЕЗУЛЬТАТА ===\n";
$test_articles = array_slice($articles, 0, 5);
foreach ($test_articles as $article) {
    $post_id = $article->ID;
    $thumbnail_id = get_post_thumbnail_id($post_id);
    
    if ($thumbnail_id) {
        $file_path = get_attached_file($thumbnail_id);
        $status = file_exists($file_path) ? "✅ Работает" : "❌ Битое";
        echo $article->post_title . ": " . $status . "\n";
    }
}

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

