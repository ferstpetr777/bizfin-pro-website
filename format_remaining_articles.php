<?php
require_once('wp-config.php');

// Список статей, которые не удалось отформатировать
$problematic_posts = array(
    2514, 2511, 2507, 2500, 2491, 2488, 2483, 2480, 2479, 2475, 2472, 2471, 2469, 2467, 2464, 2462, 2459, 2458, 2457, 2453, 2451, 2447, 2446, 2443, 2438, 2431, 2427
);

$fixed_count = 0;
$total_processed = 0;

echo "=== ФОРМАТИРОВАНИЕ ПРОБЛЕМНЫХ СТАТЕЙ ===\n\n";

foreach ($problematic_posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    echo "Обрабатываем статью ID $post_id: '$post_title'\n";
    
    // 1. Извлекаем блок содержания
    $table_of_contents = '';
    if (preg_match('/(<div[^>]*>.*?<\/div>)/s', $content, $matches)) {
        $table_of_contents = $matches[1] . "\n\n";
    }
    
    // 2. Создаем блок изображения (если есть главное изображение)
    $image_block = '';
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if ($thumbnail_id) {
        $image_url = wp_get_attachment_url($thumbnail_id);
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        if ($image_url) {
            $image_block = '<!-- wp:image {"id":' . $thumbnail_id . ',"sizeSlug":"large","linkDestination":"none","className":"ios-style-image alignwide"} -->
<figure class="wp-block-image size-large ios-style-image alignwide"><img decoding="async" width="1024" height="1024" src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="wp-image-' . $thumbnail_id . '"/></figure>
<!-- /wp:image -->';
        }
    }
    
    // 3. Ищем основной текст разными способами
    $main_text = '';
    
    // Способ 1: Ищем после блока содержания и изображения
    $search_patterns = array(
        $post_title,
        substr($post_title, 0, 50),
        substr($post_title, 0, 30),
        substr($post_title, 0, 20)
    );
    
    foreach ($search_patterns as $pattern) {
        $text_start = strpos($content, $pattern);
        if ($text_start !== false) {
            $main_text = substr($content, $text_start);
            $main_text = trim($main_text);
            
            // Удаляем дублирующийся заголовок
            $main_text = preg_replace('/^' . preg_quote($pattern, '/') . '\s*/', '', $main_text);
            break;
        }
    }
    
    // Способ 2: Если не нашли, берем весь контент после блоков
    if (empty($main_text)) {
        $content_parts = preg_split('/(<div[^>]*>.*?<\/div>|<!-- wp:image.*?<!-- \/wp:image -->)/s', $content);
        if (count($content_parts) > 1) {
            $main_text = end($content_parts);
            $main_text = trim($main_text);
        }
    }
    
    // Способ 3: Если все еще пусто, берем весь контент
    if (empty($main_text)) {
        $main_text = $content;
    }
    
    if (!empty($main_text)) {
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
        
        echo "✓ Отформатирована статья ID $post_id ($post_date): '$post_title'\n";
        $fixed_count++;
    } else {
        echo "✗ Не удалось найти основной текст для статьи ID $post_id: '$post_title'\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ФОРМАТИРОВАНИЯ ===\n";
echo "Отформатировано статей: $fixed_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
