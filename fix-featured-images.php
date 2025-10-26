<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ FEATURED IMAGES ===\n\n";

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
        echo "Проверяем featured image: " . $post_title . " (ID: " . $post_id . ")\n";
        
        $file_path = get_attached_file($thumbnail_id);
        echo "  Текущий файл: " . $file_path . "\n";
        
        if (!file_exists($file_path)) {
            echo "  ❌ Featured image файл не найден\n";
            
            // Ищем WebP версию
            $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $file_path);
            echo "  🔍 Ищем WebP: " . str_replace(ABSPATH, '', $webp_path) . "\n";
            
            if (file_exists($webp_path)) {
                echo "  ✅ WebP файл найден!\n";
                
                $webp_relative_path = str_replace(ABSPATH, '', $webp_path);
                
                // Обновляем _wp_attached_file
                update_post_meta($thumbnail_id, '_wp_attached_file', $webp_relative_path);
                echo "  🔧 Обновлен _wp_attached_file: " . $webp_relative_path . "\n";
                
                // Обновляем MIME тип
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'post_mime_type' => 'image/webp'
                ));
                echo "  🔧 Обновлен MIME тип на image/webp\n";
                
                // Обновляем GUID
                $webp_url = home_url($webp_relative_path);
                wp_update_post(array(
                    'ID' => $thumbnail_id,
                    'guid' => $webp_url
                ));
                echo "  🔧 Обновлен GUID: " . $webp_url . "\n";
                
                // Обновляем метаданные изображения
                $metadata = wp_get_attachment_metadata($thumbnail_id);
                if ($metadata) {
                    $metadata['file'] = $webp_relative_path;
                    $metadata['sizes']['full']['file'] = basename($webp_relative_path);
                    $metadata['sizes']['full']['mime-type'] = 'image/webp';
                    
                    // Обновляем размеры файла
                    $file_size = filesize($webp_path);
                    if ($file_size) {
                        $metadata['filesize'] = $file_size;
                    }
                    
                    wp_update_attachment_metadata($thumbnail_id, $metadata);
                    echo "  🔧 Обновлены метаданные изображения\n";
                }
                
                $fixed_count++;
                echo "  ✅ Featured image исправлена!\n\n";
            } else {
                echo "  ❌ WebP файл тоже не найден\n";
                $not_found_count++;
                
                // Попробуем найти любое изображение с похожим названием
                $base_name = preg_replace('/\.(png|jpg|jpeg|webp)$/i', '', basename($file_path));
                $uploads_dir = wp_upload_dir();
                $search_pattern = $uploads_dir['basedir'] . '/**/' . $base_name . '.*';
                
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
        } else {
            echo "  ✅ Featured image файл существует\n\n";
        }
    } else {
        echo "Статья без featured image: " . $post_title . " (ID: " . $post_id . ")\n\n";
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

