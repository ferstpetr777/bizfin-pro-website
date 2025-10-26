<?php
require_once('wp-config.php');

$post_id = 2521; // Аффинити-карты
$post_title = get_the_title($post_id);
$content = get_post_field('post_content', $post_id);

echo "=== ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ СТАТЬИ АФФИНИТИ-КАРТЫ ===\n\n";

// 1. Извлекаем блок содержания
$table_of_contents = '';
if (preg_match('/(<div[^>]*>.*?<\/div>)/s', $content, $matches)) {
    $table_of_contents = $matches[1] . "\n\n";
}

// 2. Создаем правильный блок изображения
$image_id = 2688;
$image_url = wp_get_attachment_url($image_id);
$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

$image_block = '<!-- wp:image {"id":' . $image_id . ',"sizeSlug":"large","linkDestination":"none","className":"ios-style-image alignwide"} -->
<figure class="wp-block-image size-large ios-style-image alignwide"><img decoding="async" width="1024" height="1024" src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="wp-image-' . $image_id . '"/></figure>
<!-- /wp:image -->';

// 3. Извлекаем основной текст
$text_start = strpos($content, 'Аффинити-карты: полное руководство');
if ($text_start !== false) {
    $main_text = substr($content, $text_start);
    $main_text = trim($main_text);
    
    // Удаляем дублирующийся заголовок
    $main_text = preg_replace('/^' . preg_quote($post_title, '/') . '\s*/', '', $main_text);
    
    // Разбиваем текст на абзацы
    $paragraphs = preg_split('/\n\s*\n/', $main_text);
    $formatted_content = '';
    
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if (empty($paragraph)) continue;
        
        // Определяем тип контента и форматируем соответственно
        if (preg_match('/^(\d+\.\s*[А-Я][^:]*):/', $paragraph, $matches)) {
            // Заголовок раздела
            $formatted_content .= "<!-- wp:heading {\"level\":2} -->\n<h2 id=\"" . sanitize_title($matches[1]) . "\">" . $matches[1] . "</h2>\n<!-- /wp:heading -->\n\n";
            
            $section_text = trim(substr($paragraph, strlen($matches[0])));
            if (!empty($section_text)) {
                $formatted_content .= "<!-- wp:paragraph -->\n<p>" . $section_text . "</p>\n<!-- /wp:paragraph -->\n\n";
            }
        } elseif (preg_match('/^([А-Я][^:]*):/', $paragraph, $matches)) {
            // Обычный заголовок
            $formatted_content .= "<!-- wp:heading {\"level\":2} -->\n<h2 id=\"" . sanitize_title($matches[1]) . "\">" . $matches[1] . "</h2>\n<!-- /wp:heading -->\n\n";
            
            $section_text = trim(substr($paragraph, strlen($matches[0])));
            if (!empty($section_text)) {
                $formatted_content .= "<!-- wp:paragraph -->\n<p>" . $section_text . "</p>\n<!-- /wp:paragraph -->\n\n";
            }
        } elseif (preg_match('/^[-•]\s*(.+)/', $paragraph, $matches)) {
            // Элемент списка
            if (!isset($list_started)) {
                $formatted_content .= "<!-- wp:list -->\n<ul>\n";
                $list_started = true;
            }
            $formatted_content .= "<li>" . $matches[1] . "</li>\n";
        } else {
            // Обычный абзац
            if (isset($list_started)) {
                $formatted_content .= "</ul>\n<!-- /wp:list -->\n\n";
                unset($list_started);
            }
            $formatted_content .= "<!-- wp:paragraph -->\n<p>" . $paragraph . "</p>\n<!-- /wp:paragraph -->\n\n";
        }
    }
    
    // Закрываем список, если он был открыт
    if (isset($list_started)) {
        $formatted_content .= "</ul>\n<!-- /wp:list -->\n\n";
    }
    
    // Объединяем все части
    $new_content = $table_of_contents . $image_block . "\n\n" . $formatted_content;
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "✓ Статья ID $post_id отформатирована\n";
    echo "✓ Добавлены заголовки H2 и параграфы\n";
    echo "✓ Добавлен блок изображения\n";
} else {
    echo "✗ Не удалось найти основной текст статьи\n";
}

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
