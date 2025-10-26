<?php
require_once('wp-config.php');

// Получаем статью с ID 2523
$post_id = 2523;
$post_content = get_post_field('post_content', $post_id);
$thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);

if (!$thumbnail_id) {
    echo "У статьи нет главного изображения!\n";
    exit;
}

// Получаем информацию об изображении
$image_url = wp_get_attachment_url($thumbnail_id);
$image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
$image_title = get_the_title($thumbnail_id);

if (!$image_url) {
    echo "Не удалось получить URL изображения!\n";
    exit;
}

// Создаем блок изображения Gutenberg
$image_block = '<!-- wp:image {"sizeSlug":"large","align":"wide","className":"ios-style-image"} -->
<figure class="wp-block-image size-large ios-style-image alignwide"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt ?: $image_title) . '" class="wp-image-' . $thumbnail_id . '"/></figure>
<!-- /wp:image -->';

// Ищем блок содержания в контенте
$content_parts = explode('<div style="background: #f8f9fa;padding: 20px;border-radius: 8px;margin: 20px 0;border-left: 4px solid #007cba">', $post_content);

if (count($content_parts) > 1) {
    // Находим конец блока содержания
    $toc_end = strpos($content_parts[1], '</div>');
    if ($toc_end !== false) {
        $toc_end += 6; // +6 для длины '</div>'
        $toc_block = substr($content_parts[1], 0, $toc_end);
        $rest_content = substr($content_parts[1], $toc_end);
        
        // Собираем новый контент: блок содержания + блок изображения + остальной контент
        $new_content = $content_parts[0] . '<div style="background: #f8f9fa;padding: 20px;border-radius: 8px;margin: 20px 0;border-left: 4px solid #007cba">' . $toc_block . "\n\n" . $image_block . "\n\n" . $rest_content;
        
        // Обновляем статью
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content
        ));
        
        echo "Блок изображения добавлен в статью ID $post_id\n";
        echo "URL изображения: $image_url\n";
        echo "Alt текст: " . ($image_alt ?: $image_title) . "\n";
    } else {
        echo "Не удалось найти конец блока содержания!\n";
    }
} else {
    echo "Блок содержания не найден в контенте!\n";
}
?>
