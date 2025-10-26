<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== МАССОВОЕ ВОССТАНОВЛЕНИЕ ИЗОБРАЖЕНИЙ ===\n\n";

// 1. НАЙТИ ВСЕ СТАТЬИ С ИЗОБРАЖЕНИЯМИ
echo "1. ПОИСК СТАТЕЙ С ИЗОБРАЖЕНИЯМИ\n";
echo "================================\n";

$articles = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
));

$articles_with_images = array();
$total_articles = count($articles);

echo "Всего статей: " . $total_articles . "\n\n";

foreach ($articles as $article) {
    $content = $article->post_content;
    $post_title = $article->post_title;
    $post_id = $article->ID;
    
    // Проверяем наличие изображений в контенте
    preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);
    
    if (!empty($matches[1])) {
        $articles_with_images[$post_id] = array(
            'title' => $post_title,
            'images' => $matches[1]
        );
    }
    
    // Проверяем featured image
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        if (!isset($articles_with_images[$post_id])) {
            $articles_with_images[$post_id] = array(
                'title' => $post_title,
                'images' => array()
            );
        }
        $articles_with_images[$post_id]['featured_image'] = $thumbnail_id;
    }
}

echo "Статей с изображениями: " . count($articles_with_images) . "\n\n";

// 2. АНАЛИЗ СЛОМАННЫХ ССЫЛОК
echo "2. АНАЛИЗ СЛОМАННЫХ ССЫЛОК\n";
echo "================================\n";

$broken_articles = array();
$fixed_count = 0;

foreach ($articles_with_images as $post_id => $data) {
    echo "Проверяем: " . $data['title'] . " (ID: " . $post_id . ")\n";
    
    $needs_fix = false;
    $fixed_images = 0;
    
    // Проверяем изображения в контенте
    if (isset($data['images'])) {
        foreach ($data['images'] as $src) {
            $parsed_url = parse_url($src);
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : $src;
            $filename = basename($path);
            $full_path = ABSPATH . ltrim($path, '/');
            
            if (!file_exists($full_path)) {
                echo "  Сломанная ссылка: " . $src . "\n";
                
                // Ищем WebP версию
                $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $full_path);
                if (file_exists($webp_path)) {
                    $webp_url = str_replace(ABSPATH, '', $webp_path);
                    echo "  ✓ Найден WebP файл: " . $webp_url . "\n";
                    
                    // Обновляем контент статьи
                    $post = get_post($post_id);
                    $new_content = str_replace($src, $webp_url, $post->post_content);
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $new_content
                    ));
                    
                    $fixed_images++;
                    $needs_fix = true;
                } else {
                    echo "  ✗ WebP файл не найден\n";
                }
            }
        }
    }
    
    // Проверяем featured image
    if (isset($data['featured_image'])) {
        $thumbnail_id = $data['featured_image'];
        $file_path = get_attached_file($thumbnail_id);
        
        if (!file_exists($file_path)) {
            echo "  Сломанная featured image: " . $file_path . "\n";
            
            // Ищем WebP версию
            $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $file_path);
            if (file_exists($webp_path)) {
                echo "  ✓ Найден WebP файл для featured image\n";
                
                // Обновляем метаданные изображения
                $webp_relative_path = str_replace(ABSPATH, '', $webp_path);
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
                
                $fixed_images++;
                $needs_fix = true;
            } else {
                echo "  ✗ WebP файл для featured image не найден\n";
            }
        }
    }
    
    if ($needs_fix) {
        $broken_articles[$post_id] = array(
            'title' => $data['title'],
            'fixed_images' => $fixed_images
        );
        $fixed_count++;
        echo "  ✓ Исправлено изображений: " . $fixed_images . "\n";
    } else {
        echo "  ✓ Все изображения в порядке\n";
    }
    
    echo "\n";
}

// 3. СТАТИСТИКА
echo "3. СТАТИСТИКА ВОССТАНОВЛЕНИЯ\n";
echo "================================\n";
echo "Всего статей проверено: " . $total_articles . "\n";
echo "Статей с изображениями: " . count($articles_with_images) . "\n";
echo "Статей с проблемами: " . count($broken_articles) . "\n";
echo "Статей исправлено: " . $fixed_count . "\n\n";

if (!empty($broken_articles)) {
    echo "ИСПРАВЛЕННЫЕ СТАТЬИ:\n";
    foreach ($broken_articles as $post_id => $data) {
        echo "- " . $data['title'] . " (ID: " . $post_id . ") - исправлено: " . $data['fixed_images'] . " изображений\n";
    }
}

echo "\n=== ВОССТАНОВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

