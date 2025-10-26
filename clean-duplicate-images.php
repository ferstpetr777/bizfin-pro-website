<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ОЧИСТКА ДУБЛИРУЮЩИХСЯ ИЗОБРАЖЕНИЙ ===\n\n";

// Получаем все статьи
$articles = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
));

$cleaned_articles = 0;
$total_duplicates_removed = 0;

foreach ($articles as $article) {
    $post_id = $article->ID;
    $content = $article->post_content;
    $original_content = $content;
    $duplicates_removed = 0;
    
    // Ищем все изображения в контенте
    preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);
    
    if (count($matches[1]) > 1) {
        echo "Очищаем: " . $article->post_title . " (ID: " . $post_id . ")\n";
        echo "  Найдено изображений: " . count($matches[1]) . "\n";
        
        // Группируем изображения по базовому имени файла
        $image_groups = array();
        foreach ($matches[1] as $src) {
            $filename = basename($src);
            $base_name = preg_replace('/\.(png|jpg|jpeg|webp)$/i', '', $filename);
            
            if (!isset($image_groups[$base_name])) {
                $image_groups[$base_name] = array();
            }
            $image_groups[$base_name][] = $src;
        }
        
        // Для каждой группы оставляем только одно рабочее изображение
        $final_images = array();
        foreach ($image_groups as $base_name => $urls) {
            $best_url = null;
            $best_priority = 0;
            
            foreach ($urls as $url) {
                $priority = 0;
                
                // Приоритет: WebP > PNG > JPG > placeholder
                if (strpos($url, '.webp') !== false) {
                    $priority = 4;
                } elseif (strpos($url, '.png') !== false) {
                    $priority = 3;
                } elseif (strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false) {
                    $priority = 2;
                } elseif (strpos($url, 'placeholder') !== false) {
                    $priority = 1;
                }
                
                // Проверяем, существует ли файл
                $parsed_url = parse_url($url);
                $path = isset($parsed_url['path']) ? $parsed_url['path'] : $url;
                $full_path = ABSPATH . ltrim($path, '/');
                
                if (file_exists($full_path) && $priority > $best_priority) {
                    $best_priority = $priority;
                    $best_url = $url;
                }
            }
            
            if ($best_url) {
                $final_images[] = $best_url;
                echo "  ✅ Оставлено: " . basename($best_url) . "\n";
            }
        }
        
        // Удаляем все изображения из контента
        $content = preg_replace('/<img[^>]+>/', '', $content);
        
        // Добавляем только лучшие изображения
        foreach ($final_images as $img_url) {
            $content = '<img src="' . $img_url . '" alt="' . $article->post_title . '" />' . "\n" . $content;
        }
        
        $duplicates_removed = count($matches[1]) - count($final_images);
        
        if ($content !== $original_content) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $content,
            ));
            
            $cleaned_articles++;
            $total_duplicates_removed += $duplicates_removed;
            
            echo "  🧹 Удалено дубликатов: " . $duplicates_removed . "\n";
            echo "  📝 Оставлено изображений: " . count($final_images) . "\n\n";
        }
    }
}

echo "=== РЕЗУЛЬТАТЫ ОЧИСТКИ ===\n";
echo "Очищено статей: " . $cleaned_articles . "\n";
echo "Удалено дубликатов: " . $total_duplicates_removed . "\n";

// Дополнительно исправляем featured images
echo "\n=== ИСПРАВЛЕНИЕ FEATURED IMAGES ===\n";

$fixed_featured = 0;
foreach ($articles as $article) {
    $post_id = $article->ID;
    $thumbnail_id = get_post_thumbnail_id($post_id);
    
    if ($thumbnail_id) {
        $file_path = get_attached_file($thumbnail_id);
        
        if (!file_exists($file_path)) {
            // Ищем WebP версию
            $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $file_path);
            
            if (file_exists($webp_path)) {
                $webp_relative_path = str_replace(ABSPATH, '', $webp_path);
                
                // Обновляем метаданные
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
                
                $fixed_featured++;
                echo "✅ Исправлена featured image для: " . $article->post_title . "\n";
            }
        }
    }
}

echo "\nИсправлено featured images: " . $fixed_featured . "\n";
echo "\n=== ОЧИСТКА ЗАВЕРШЕНА ===\n";
?>

