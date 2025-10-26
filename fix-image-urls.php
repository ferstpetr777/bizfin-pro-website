<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ URL ИЗОБРАЖЕНИЙ ===\n\n";

$post_id = 3049;
$post = get_post($post_id);

if (!$post) {
    echo "Статья не найдена!\n";
    exit;
}

echo "Статья: " . $post->post_title . "\n";
echo "ID: " . $post_id . "\n\n";

$content = $post->post_content;

// Исправляем неправильные пути к изображениям
$content = str_replace('src="wp-content/uploads/', 'src="https://bizfin-pro.ru/wp-content/uploads/', $content);

// Удаляем дублирующиеся изображения (оставляем только WebP версию)
$content = preg_replace('/<img[^>]*src="[^"]*\.png"[^>]*>/', '', $content);

// Удаляем пустые строки и лишние пробелы
$content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

wp_update_post(array(
    'ID' => $post_id,
    'post_content' => $content,
));

echo "✅ URL изображений исправлены!\n";

// Проверим результат
$updated_post = get_post($post_id);
$updated_content = $updated_post->post_content;

echo "\nПроверка результата:\n";
if (strpos($updated_content, 'https://bizfin-pro.ru/wp-content/uploads/2025/10/Banki-vydayuschie-bankovskie-garantii-na-vozvrat-a.webp') !== false) {
    echo "✅ WebP изображение найдено в контенте\n";
} else {
    echo "❌ WebP изображение НЕ найдено в контенте\n";
}

if (strpos($updated_content, '.png') !== false) {
    echo "⚠️ PNG изображения все еще присутствуют\n";
} else {
    echo "✅ PNG изображения удалены\n";
}

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

