<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ ЛОГИКИ FEATURED IMAGE ===\n\n";

$post_id = 3049;
$post = get_post($post_id);

if (!$post) {
    echo "Статья не найдена!\n";
    exit;
}

echo "Статья: " . $post->post_title . "\n";
echo "ID: " . $post_id . "\n\n";

// Проверим featured image
$thumbnail_id = get_post_thumbnail_id($post_id);
echo "Featured Image ID: " . $thumbnail_id . "\n";

if ($thumbnail_id) {
    $attachment = get_post($thumbnail_id);
    $file_path = get_post_meta($thumbnail_id, '_wp_attached_file', true);
    echo "Featured Image файл: " . $file_path . "\n";
    echo "Featured Image URL: " . wp_get_attachment_url($thumbnail_id) . "\n";
}

$content = $post->post_content;
$original_content = $content;

echo "\nИсходный размер контента: " . strlen($content) . " символов\n";

// 1. Удаляем ВСЕ изображения из контента (оставляем только featured image)
$content = preg_replace('/<img[^>]*src="[^"]*Banki-vydayuschie-bankovskie-garantii-na-vozvrat-a[^"]*"[^>]*>/', '', $content);

// 2. Удаляем пустые figure блоки
$content = preg_replace('/<!-- wp:image[^>]*-->\s*<figure[^>]*><\/figure>\s*<!-- \/wp:image -->/', '', $content);

// 3. Удаляем лишние пустые строки
$content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

echo "Новый размер контента: " . strlen($content) . " символов\n";

if ($content !== $original_content) {
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    echo "✅ Контент статьи очищен от дублирующих изображений!\n";
} else {
    echo "⚠️ Контент не изменился\n";
}

echo "\n=== ПРОВЕРКА FEATURED IMAGE ===\n";

// Проверим, что featured image настроен правильно
if ($thumbnail_id) {
    $attachment = get_post($thumbnail_id);
    if ($attachment) {
        echo "✅ Featured image найден: " . $attachment->post_title . "\n";
        
        $file_path = get_post_meta($thumbnail_id, '_wp_attached_file', true);
        $full_path = ABSPATH . 'wp-content/uploads/' . $file_path;
        
        if (file_exists($full_path)) {
            echo "✅ Файл featured image существует\n";
            echo "Размер файла: " . filesize($full_path) . " байт\n";
        } else {
            echo "❌ Файл featured image НЕ существует: " . $full_path . "\n";
        }
        
        $image_url = wp_get_attachment_url($thumbnail_id);
        echo "URL изображения: " . $image_url . "\n";
    } else {
        echo "❌ Featured image не найден\n";
    }
} else {
    echo "❌ Featured image не установлен\n";
}

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
echo "Теперь featured image должен отображаться автоматически темой после блока 'Содержание'\n";
?>

