<?php
require_once('wp-config.php');

// Получаем все статьи от 19 и 7 октября
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$fixed_count = 0;
$skipped_count = 0;
$total_processed = 0;

echo "=== КАЧЕСТВЕННОЕ ФОРМАТИРОВАНИЕ ВСЕХ СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    // Пропускаем статьи, которые уже имеют правильное форматирование
    $has_h2 = strpos($content, '<h2>') !== false || strpos($content, '<!-- wp:heading') !== false;
    $has_paragraphs = strpos($content, '<p>') !== false || strpos($content, '<!-- wp:paragraph') !== false;
    
    if ($has_h2 && $has_paragraphs) {
        $skipped_count++;
        continue;
    }
    
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
    
    // 3. Извлекаем основной текст
    $text_start = strpos($content, $post_title);
    if ($text_start === false) {
        // Пробуем найти по части названия
        $title_parts = explode(':', $post_title);
        if (!empty($title_parts[0])) {
            $text_start = strpos($content, $title_parts[0]);
        }
    }
    
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
        
        echo "✓ Отформатирована статья ID $post_id ($post_date): '$post_title'\n";
        $fixed_count++;
    } else {
        echo "✗ Не удалось найти основной текст для статьи ID $post_id: '$post_title'\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ФОРМАТИРОВАНИЯ ===\n";
echo "Отформатировано статей: $fixed_count\n";
echo "Пропущено статей (уже отформатированы): $skipped_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
