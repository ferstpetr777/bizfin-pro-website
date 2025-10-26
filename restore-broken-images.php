<?php
/**
 * Восстановление сломанных изображений в статьях
 * Smart Image Recovery Script
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ВОССТАНОВЛЕНИЕ СЛОМАННЫХ ИЗОБРАЖЕНИЙ ===\n\n";

// 1. Отключаем автоматическое сжатие WebP
$options = get_option('sic_options', array());
$options['format'] = 'jpeg'; // Переключаем на JPEG
$options['auto_compress'] = false; // Отключаем автоматическое сжатие
update_option('sic_options', $options);
echo "✓ Отключено автоматическое сжатие WebP\n\n";

// 2. Получаем все статьи со сломанными изображениями
$posts_with_broken_images = $wpdb->get_results("
    SELECT ID, post_title, post_content 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND (post_content LIKE '%<img%' OR post_content LIKE '%src=%')
    ORDER BY ID DESC
");

echo "Найдено статей для проверки: " . count($posts_with_broken_images) . "\n\n";

$fixed_count = 0;
$total_broken = 0;
$total_fixed = 0;

foreach ($posts_with_broken_images as $post) {
    $post_id = $post->ID;
    $post_title = $post->post_title;
    $content = $post->post_content;
    $original_content = $content;
    
    echo "Обрабатываем: {$post_title} (ID: {$post_id})\n";
    
    // Ищем все img теги
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
    
    if (!empty($matches[1])) {
        $broken_in_post = 0;
        $fixed_in_post = 0;
        
        foreach ($matches[1] as $img_src) {
            $total_broken++;
            
            // Проверяем, существует ли файл
            $file_exists = false;
            $corrected_src = $img_src;
            
            // Пробуем разные варианты пути
            $upload_dir = wp_upload_dir();
            $base_url = $upload_dir['baseurl'];
            $base_path = $upload_dir['basedir'];
            
            // Вариант 1: Абсолютный URL
            if (strpos($img_src, 'http') === 0) {
                if (strpos($img_src, $base_url) === 0) {
                    $relative_path = str_replace($base_url, '', $img_src);
                    $file_path = $base_path . $relative_path;
                    if (file_exists($file_path)) {
                        $file_exists = true;
                    }
                }
            } else {
                // Вариант 2: Относительный путь
                $file_path = $base_path . '/' . ltrim($img_src, '/');
                if (file_exists($file_path)) {
                    $file_exists = true;
                }
            }
            
            // Если файл не найден, ищем альтернативы
            if (!$file_exists) {
                $broken_in_post++;
                
                // Извлекаем имя файла
                $filename = basename($img_src);
                $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
                
                // Ищем похожие файлы в медиабиблиотеке
                $similar_attachments = $wpdb->get_results($wpdb->prepare("
                    SELECT ID, post_title, guid 
                    FROM {$wpdb->posts} 
                    WHERE post_type = 'attachment' 
                    AND post_mime_type LIKE 'image/%'
                    AND (post_title LIKE %s OR post_name LIKE %s)
                    ORDER BY ID DESC
                    LIMIT 3
                ", '%' . $filename_without_ext . '%', '%' . $filename_without_ext . '%'));
                
                if (!empty($similar_attachments)) {
                    $corrected_src = $similar_attachments[0]->guid;
                    $content = str_replace($img_src, $corrected_src, $content);
                    echo "  ✓ Исправлено: {$img_src} → {$corrected_src}\n";
                    $fixed_in_post++;
                    $total_fixed++;
                } else {
                    // Ищем по частичному совпадению
                    $partial_matches = $wpdb->get_results($wpdb->prepare("
                        SELECT ID, post_title, guid 
                        FROM {$wpdb->posts} 
                        WHERE post_type = 'attachment' 
                        AND post_mime_type LIKE 'image/%'
                        AND post_title LIKE %s
                        ORDER BY ID DESC
                        LIMIT 3
                    ", '%' . substr($filename_without_ext, 0, 15) . '%'));
                    
                    if (!empty($partial_matches)) {
                        $corrected_src = $partial_matches[0]->guid;
                        $content = str_replace($img_src, $corrected_src, $content);
                        echo "  ✓ Исправлено (частичное совпадение): {$img_src} → {$corrected_src}\n";
                        $fixed_in_post++;
                        $total_fixed++;
                    } else {
                        // Создаем заглушку
                        $placeholder_url = $base_url . '/2025/09/placeholder-image.jpg';
                        $content = str_replace($img_src, $placeholder_url, $content);
                        echo "  ⚠️  Заменено на заглушку: {$img_src}\n";
                        $fixed_in_post++;
                        $total_fixed++;
                    }
                }
            } else {
                echo "  ✓ Файл существует: {$img_src}\n";
            }
        }
        
        // Обновляем контент если были изменения
        if ($content !== $original_content) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $content
            ));
            echo "  ✓ Статья обновлена (исправлено: {$fixed_in_post} из {$broken_in_post})\n";
            $fixed_count++;
        } else {
            echo "  ✓ Проблем не найдено\n";
        }
    }
    echo "\n";
}

echo "=== РЕЗУЛЬТАТЫ ВОССТАНОВЛЕНИЯ ===\n";
echo "Обработано статей: " . count($posts_with_broken_images) . "\n";
echo "Статей с исправлениями: {$fixed_count}\n";
echo "Всего сломанных ссылок: {$total_broken}\n";
echo "Исправлено ссылок: {$total_fixed}\n\n";

// 3. Создаем заглушку для отсутствующих изображений
echo "=== СОЗДАНИЕ ЗАГЛУШКИ ===\n";
$upload_dir = wp_upload_dir();
$placeholder_path = $upload_dir['basedir'] . '/2025/09/placeholder-image.jpg';

if (!file_exists($placeholder_path)) {
    // Создаем простую заглушку
    $placeholder_dir = dirname($placeholder_path);
    if (!file_exists($placeholder_dir)) {
        wp_mkdir_p($placeholder_dir);
    }
    
    // Создаем простое изображение-заглушку
    $image = imagecreate(400, 300);
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 100, 100, 100);
    
    imagestring($image, 5, 120, 140, 'Image Not Found', $text_color);
    
    imagejpeg($image, $placeholder_path, 85);
    imagedestroy($image);
    
    echo "✓ Создана заглушка: {$placeholder_path}\n";
} else {
    echo "✓ Заглушка уже существует\n";
}

// 4. Очищаем кеш
echo "\n=== ОЧИСТКА КЕША ===\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ Кеш WordPress очищен\n";
}

// Очищаем кеш плагинов
if (class_exists('WP_Rocket')) {
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
        echo "✓ Кеш WP Rocket очищен\n";
    }
}

// 5. Проверяем доступность сайта
echo "\n=== ПРОВЕРКА САЙТА ===\n";
$site_url = get_site_url();
echo "URL сайта: {$site_url}\n";

// Проверяем несколько ключевых страниц
$test_posts = $wpdb->get_results("
    SELECT ID, post_title, post_name 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    ORDER BY ID DESC
    LIMIT 5
");

echo "Тестовые страницы:\n";
foreach ($test_posts as $test_post) {
    $test_url = $site_url . '/' . $test_post->post_name . '/';
    echo "- {$test_post->post_title}: {$test_url}\n";
}

echo "\n=== ВОССТАНОВЛЕНИЕ ЗАВЕРШЕНО ===\n";
echo "Проверьте сайт по адресу: {$site_url}\n";
echo "Если проблемы остались, запустите скрипт повторно\n";
?>

