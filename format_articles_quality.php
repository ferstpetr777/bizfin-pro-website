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
$total_processed = 0;

echo "=== КАЧЕСТВЕННОЕ ФОРМАТИРОВАНИЕ СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    // Пропускаем статьи, которые уже имеют правильное форматирование
    if (strpos($content, '<h2>') !== false || strpos($content, '<!-- wp:heading') !== false) {
        continue;
    }
    
    // Извлекаем основной текст (после блока содержания и изображения)
    $text_start = strpos($content, $post_title);
    if ($text_start === false) {
        continue;
    }
    
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
            // Заголовок раздела (например: "1. По размеру:")
            $formatted_content .= "<!-- wp:heading {\"level\":2} -->\n<h2 id=\"" . sanitize_title($matches[1]) . "\">" . $matches[1] . "</h2>\n<!-- /wp:heading -->\n\n";
            
            // Остальной текст раздела
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
    
    // Объединяем с существующим контентом (блок содержания + изображение + отформатированный текст)
    $table_of_contents = '';
    $image_block = '';
    
    // Извлекаем блок содержания
    if (preg_match('/(<div[^>]*>.*?<\/div>)/s', $content, $matches)) {
        $table_of_contents = $matches[1] . "\n\n";
    }
    
    // Извлекаем блок изображения
    if (preg_match('/(<!-- wp:image.*?<!-- \/wp:image -->)/s', $content, $matches)) {
        $image_block = $matches[1] . "\n\n";
    }
    
    $new_content = $table_of_contents . $image_block . $formatted_content;
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "✓ Отформатирована статья ID $post_id ($post_date): '$post_title'\n";
    $fixed_count++;
}

echo "\n=== РЕЗУЛЬТАТЫ ФОРМАТИРОВАНИЯ ===\n";
echo "Отформатировано статей: $fixed_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
