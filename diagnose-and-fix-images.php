<?php
/**
 * Комплексная диагностика и восстановление изображений после сжатия
 * Smart Image Compressor Recovery Script
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ДИАГНОСТИКА И ВОССТАНОВЛЕНИЕ ИЗОБРАЖЕНИЙ ===\n\n";

// 1. Проверяем состояние медиабиблиотеки
echo "1. ПРОВЕРКА МЕДИАБИБЛИОТЕКИ\n";
echo "================================\n";

$total_attachments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'");
$image_attachments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'");
$webp_attachments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type = 'image/webp'");

echo "Всего файлов в медиабиблиотеке: {$total_attachments}\n";
echo "Изображений: {$image_attachments}\n";
echo "WebP изображений: {$webp_attachments}\n\n";

// 2. Проверяем сломанные ссылки на изображения
echo "2. ПРОВЕРКА СЛОМАННЫХ ССЫЛОК\n";
echo "================================\n";

$broken_images = array();
$posts_with_images = $wpdb->get_results("
    SELECT ID, post_title, post_content 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND (post_content LIKE '%<img%' OR post_content LIKE '%src=%')
    ORDER BY ID DESC
    LIMIT 100
");

echo "Проверяем статьи с изображениями: " . count($posts_with_images) . "\n\n";

foreach ($posts_with_images as $post) {
    $content = $post->post_content;
    $post_id = $post->ID;
    $post_title = $post->post_title;
    
    // Ищем все img теги
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
    
    if (!empty($matches[1])) {
        $broken_in_post = array();
        
        foreach ($matches[1] as $img_src) {
            // Проверяем, существует ли файл
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
            
            if ($file_path && !file_exists($file_path)) {
                $broken_in_post[] = $img_src;
            }
        }
        
        if (!empty($broken_in_post)) {
            $broken_images[$post_id] = array(
                'title' => $post_title,
                'broken_links' => $broken_in_post
            );
        }
    }
}

echo "Найдено статей со сломанными изображениями: " . count($broken_images) . "\n\n";

// 3. Анализируем проблемы
echo "3. АНАЛИЗ ПРОБЛЕМ\n";
echo "================================\n";

$webp_conversion_issues = 0;
$path_issues = 0;
$missing_files = 0;

foreach ($broken_images as $post_id => $data) {
    echo "Статья: {$data['title']} (ID: {$post_id})\n";
    
    foreach ($data['broken_links'] as $broken_link) {
        echo "  Сломанная ссылка: {$broken_link}\n";
        
        // Анализируем тип проблемы
        if (strpos($broken_link, '.webp') !== false) {
            $webp_conversion_issues++;
            echo "    → Проблема: WebP конвертация\n";
        } elseif (strpos($broken_link, 'wp-content/uploads') === false) {
            $path_issues++;
            echo "    → Проблема: Неправильный путь\n";
        } else {
            $missing_files++;
            echo "    → Проблема: Файл не найден\n";
        }
    }
    echo "\n";
}

echo "Статистика проблем:\n";
echo "- WebP конвертация: {$webp_conversion_issues}\n";
echo "- Неправильные пути: {$path_issues}\n";
echo "- Отсутствующие файлы: {$missing_files}\n\n";

// 4. Восстановление изображений
echo "4. ВОССТАНОВЛЕНИЕ ИЗОБРАЖЕНИЙ\n";
echo "================================\n";

$restored_count = 0;
$errors_count = 0;

foreach ($broken_images as $post_id => $data) {
    echo "Восстанавливаем статью: {$data['title']}\n";
    
    $post = get_post($post_id);
    $content = $post->post_content;
    $original_content = $content;
    
    foreach ($data['broken_links'] as $broken_link) {
        // Пытаемся найти альтернативный файл
        $fixed_link = $this->find_alternative_image($broken_link);
        
        if ($fixed_link) {
            $content = str_replace($broken_link, $fixed_link, $content);
            echo "  ✓ Исправлено: {$broken_link} → {$fixed_link}\n";
            $restored_count++;
        } else {
            echo "  ✗ Не удалось найти альтернативу для: {$broken_link}\n";
            $errors_count++;
        }
    }
    
    // Обновляем контент если были изменения
    if ($content !== $original_content) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content
        ));
        echo "  ✓ Статья обновлена\n";
    }
    echo "\n";
}

echo "Результаты восстановления:\n";
echo "- Исправлено ссылок: {$restored_count}\n";
echo "- Ошибок: {$errors_count}\n\n";

// 5. Проверка WebP поддержки
echo "5. ПРОВЕРКА WEBP ПОДДЕРЖКИ\n";
echo "================================\n";

if (function_exists('imagewebp')) {
    echo "✓ WebP поддерживается сервером\n";
} else {
    echo "⚠️  WebP не поддерживается - отключаем WebP конвертацию\n";
    
    // Отключаем WebP в настройках плагина
    $options = get_option('sic_options', array());
    $options['format'] = 'jpeg';
    update_option('sic_options', $options);
    echo "✓ Настройки обновлены: формат изменен на JPEG\n";
}

// 6. Создание резервных копий оригиналов
echo "\n6. СОЗДАНИЕ РЕЗЕРВНЫХ КОПИЙ\n";
echo "================================\n";

$backup_dir = wp_upload_dir()['basedir'] . '/backup-originals';
if (!file_exists($backup_dir)) {
    wp_mkdir_p($backup_dir);
    echo "✓ Создана папка для резервных копий: {$backup_dir}\n";
}

// 7. Финальная проверка
echo "\n7. ФИНАЛЬНАЯ ПРОВЕРКА\n";
echo "================================\n";

$final_check = $wpdb->get_results("
    SELECT COUNT(*) as total_posts
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND post_content LIKE '%<img%'
");

echo "Статей с изображениями: {$final_check[0]->total_posts}\n";

// Проверяем доступность сайта
$site_url = get_site_url();
echo "URL сайта: {$site_url}\n";

echo "\n=== ВОССТАНОВЛЕНИЕ ЗАВЕРШЕНО ===\n";
echo "Проверьте сайт по адресу: {$site_url}\n";
echo "Если проблемы остались, запустите скрипт повторно\n";

/**
 * Поиск альтернативного изображения
 */
function find_alternative_image($broken_link) {
    global $wpdb;
    
    // Извлекаем имя файла
    $filename = basename($broken_link);
    $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
    
    // Ищем в медиабиблиотеке
    $attachments = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_title, guid 
        FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' 
        AND post_mime_type LIKE 'image/%'
        AND (post_title LIKE %s OR post_name LIKE %s)
        LIMIT 5
    ", '%' . $filename_without_ext . '%', '%' . $filename_without_ext . '%'));
    
    if (!empty($attachments)) {
        return $attachments[0]->guid;
    }
    
    // Ищем по похожему имени файла
    $similar_files = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_title, guid 
        FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' 
        AND post_mime_type LIKE 'image/%'
        AND post_title LIKE %s
        LIMIT 5
    ", '%' . substr($filename_without_ext, 0, 10) . '%'));
    
    if (!empty($similar_files)) {
        return $similar_files[0]->guid;
    }
    
    return false;
}
?>

