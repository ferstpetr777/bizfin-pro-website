<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ КОНТЕНТА СТАТЬИ ===\n\n";

$post_id = 3049;
$post = get_post($post_id);

if (!$post) {
    echo "Статья не найдена!\n";
    exit;
}

echo "Статья: " . $post->post_title . "\n";
echo "ID: " . $post_id . "\n\n";

$content = $post->post_content;
$original_content = $content;

echo "Исходный размер контента: " . strlen($content) . " символов\n";

// 1. Удаляем битое изображение placeholder
$content = preg_replace('/<img[^>]*src="[^"]*placeholder-image\.jpg"[^>]*>/', '', $content);

// 2. Исправляем путь к WebP изображению (убираем двойной слеш)
$content = str_replace('wp-content/uploads//2025/10/', 'wp-content/uploads/2025/10/', $content);

// 3. Удаляем все пустые блоки изображений в конце
$content = preg_replace('/<!-- wp:image[^>]*-->\s*<figure[^>]*><\/figure>\s*<!-- \/wp:image -->/', '', $content);

// 4. Добавляем правильное изображение после блока "Содержание"
$image_html = '<!-- wp:image {"id":3052,"sizeSlug":"large","linkDestination":"none","className":"ios-style-image alignwide"} -->
<figure class="wp-block-image size-large ios-style-image alignwide"><img src="https://bizfin-pro.ru/wp-content/uploads/2025/10/Banki-vydayuschie-bankovskie-garantii-na-vozvrat-a.webp" alt="Банки, выдающие банковские гарантии на возврат аванса: полный справочник" class="wp-image-3052" /></figure>
<!-- /wp:image -->';

// Находим позицию после блока "Содержание"
$toc_end_pos = strpos($content, '</nav>');
if ($toc_end_pos !== false) {
    $insert_pos = $toc_end_pos + strlen('</nav>');
    $content = substr($content, 0, $insert_pos) . "\n\n" . $image_html . "\n\n" . substr($content, $insert_pos);
} else {
    // Если не найдем </nav>, ищем после "Содержание"
    $toc_pos = strpos($content, 'Содержание:');
    if ($toc_pos !== false) {
        $ul_end_pos = strpos($content, '</ul>', $toc_pos);
        if ($ul_end_pos !== false) {
            $insert_pos = $ul_end_pos + strlen('</ul>');
            $content = substr($content, 0, $insert_pos) . "\n\n" . $image_html . "\n\n" . substr($content, $insert_pos);
        }
    }
}

echo "Новый размер контента: " . strlen($content) . " символов\n";

if ($content !== $original_content) {
    wp_update_post(array(
    'ID' => $post_id,
        'post_content' => $content,
    ));
    echo "✅ Контент статьи обновлен!\n";
} else {
    echo "⚠️ Контент не изменился\n";
}

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>