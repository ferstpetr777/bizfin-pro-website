<?php
/**
 * Идентификация изображений, привязанных к статьям
 * Article Images Identification Script
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ИДЕНТИФИКАЦИЯ ИЗОБРАЖЕНИЙ СТАТЕЙ ===\n\n";

// 1. Получаем все статьи с изображениями
$posts_with_images = $wpdb->get_results("
    SELECT ID, post_title, post_content 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND (post_content LIKE '%<img%' OR post_content LIKE '%src=%')
    ORDER BY ID DESC
");

echo "Найдено статей с изображениями: " . count($posts_with_images) . "\n\n";

$article_images = array();
$broken_links = array();
$working_links = array();

foreach ($posts_with_images as $post) {
    $post_id = $post->ID;
    $post_title = $post->post_title;
    $content = $post->post_content;
    
    echo "Анализируем статью: {$post_title} (ID: {$post_id})\n";
    
    // Ищем все img теги
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
    
    if (!empty($matches[1])) {
        $post_images = array();
        
        foreach ($matches[1] as $img_src) {
            echo "  Проверяем: {$img_src}\n";
            
            // Проверяем существование файла
            $file_exists = false;
            $file_path = '';
            
            if (strpos($img_src, 'http') === 0) {
                // Абсолютный URL
                $upload_dir = wp_upload_dir();
                $base_url = $upload_dir['baseurl'];
                $base_path = $upload_dir['basedir'];
                
                if (strpos($img_src, $base_url) === 0) {
                    $relative_path = str_replace($base_url, '', $img_src);
                    $file_path = $base_path . $relative_path;
                }
            } else {
                // Относительный путь
                $upload_dir = wp_upload_dir();
                $file_path = $upload_dir['basedir'] . '/' . ltrim($img_src, '/');
            }
            
            if ($file_path && file_exists($file_path)) {
                $file_exists = true;
                $working_links[] = $img_src;
                echo "    ✓ Файл существует: {$file_path}\n";
            } else {
                $broken_links[] = array(
                    'post_id' => $post_id,
                    'post_title' => $post_title,
                    'broken_src' => $img_src,
                    'expected_path' => $file_path
                );
                echo "    ✗ Файл не найден: {$file_path}\n";
            }
            
            $post_images[] = array(
                'src' => $img_src,
                'exists' => $file_exists,
                'path' => $file_path
            );
        }
        
        $article_images[$post_id] = array(
            'title' => $post_title,
            'images' => $post_images
        );
    }
    echo "\n";
}

echo "=== СТАТИСТИКА ===\n";
echo "Статей с изображениями: " . count($posts_with_images) . "\n";
echo "Рабочих ссылок: " . count($working_links) . "\n";
echo "Сломанных ссылок: " . count($broken_links) . "\n\n";

// 2. Анализируем сломанные ссылки
echo "=== АНАЛИЗ СЛОМАННЫХ ССЫЛОК ===\n";

$broken_by_pattern = array();
foreach ($broken_links as $broken) {
    $src = $broken['broken_src'];
    $filename = basename($src);
    $pattern = '';
    
    if (strpos($src, '2025/10/') !== false) {
        $pattern = '2025/10/';
    } elseif (strpos($src, '2024/') !== false) {
        $pattern = '2024/';
    } elseif (strpos($src, '2025/09/') !== false) {
        $pattern = '2025/09/';
    } else {
        $pattern = 'other';
    }
    
    if (!isset($broken_by_pattern[$pattern])) {
        $broken_by_pattern[$pattern] = array();
    }
    $broken_by_pattern[$pattern][] = $broken;
}

foreach ($broken_by_pattern as $pattern => $links) {
    echo "Паттерн '{$pattern}': " . count($links) . " сломанных ссылок\n";
}

echo "\n";

// 3. Ищем возможные замены в медиатеке
echo "=== ПОИСК ЗАМЕН В МЕДИАТЕКЕ ===\n";

foreach ($broken_links as $broken) {
    $post_title = $broken['post_title'];
    $broken_src = $broken['broken_src'];
    $filename = basename($broken_src);
    
    echo "Ищем замену для: {$post_title}\n";
    echo "  Сломанная ссылка: {$broken_src}\n";
    echo "  Имя файла: {$filename}\n";
    
    // Ищем в медиатеке по названию статьи
    $similar_attachments = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_title, guid, post_mime_type
        FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' 
        AND post_mime_type LIKE 'image/%'
        AND (
            post_title LIKE %s 
            OR post_name LIKE %s
            OR guid LIKE %s
        )
        ORDER BY ID DESC
        LIMIT 5
    ", '%' . $post_title . '%', '%' . sanitize_title($post_title) . '%', '%' . $filename . '%'));
    
    if (!empty($similar_attachments)) {
        echo "  ✓ Найдены возможные замены:\n";
        foreach ($similar_attachments as $attachment) {
            echo "    - ID: {$attachment->ID}, GUID: {$attachment->guid}\n";
            echo "      MIME: {$attachment->post_mime_type}\n";
            
            // Проверяем существование файла
            $attachment_file = get_attached_file($attachment->ID);
            if ($attachment_file && file_exists($attachment_file)) {
                echo "      ✓ Файл существует: {$attachment_file}\n";
            } else {
                echo "      ✗ Файл не найден: {$attachment_file}\n";
            }
        }
    } else {
        echo "  ✗ Замены не найдены\n";
    }
    echo "\n";
}

// 4. Проверяем медиатеку на наличие файлов
echo "=== ПРОВЕРКА МЕДИАТЕКИ ===\n";

$all_attachments = $wpdb->get_results("
    SELECT ID, post_title, guid, post_mime_type
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment' 
    AND post_mime_type LIKE 'image/%'
    ORDER BY ID DESC
    LIMIT 20
");

echo "Проверяем первые 20 изображений из медиатеки:\n";
foreach ($all_attachments as $attachment) {
    $attachment_file = get_attached_file($attachment->ID);
    $exists = $attachment_file && file_exists($attachment_file);
    
    echo "ID: {$attachment->ID}\n";
    echo "  Название: {$attachment->post_title}\n";
    echo "  GUID: {$attachment->guid}\n";
    echo "  MIME: {$attachment->post_mime_type}\n";
    echo "  Файл: {$attachment_file}\n";
    echo "  Существует: " . ($exists ? '✓' : '✗') . "\n\n";
}

echo "=== ИДЕНТИФИКАЦИЯ ЗАВЕРШЕНА ===\n";
echo "Теперь можно приступать к восстановлению ссылок\n";
?>

