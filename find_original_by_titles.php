<?php
require_once('wp-config.php');

// Получаем все статьи от 19 октября и 7 октября
$october_19_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 19,
        ),
    ),
    'fields' => 'ids'
));

$october_7_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 7,
        ),
    ),
    'fields' => 'ids'
));

$all_posts = array_merge($october_19_posts, $october_7_posts);

echo "=== ПОИСК ОРИГИНАЛЬНЫХ СТАТЕЙ ПО ЗАГОЛОВКАМ ===\n";
echo "Всего статей для поиска: " . count($all_posts) . "\n\n";

global $wpdb;

$found_matches = 0;
$no_matches = 0;

foreach ($all_posts as $post_id) {
    $current_title = get_the_title($post_id);
    
    // Извлекаем ключевые слова из заголовка
    $keywords = array();
    
    // Убираем номера и стандартные фразы
    $clean_title = preg_replace('/#\d+/', '', $current_title);
    $clean_title = preg_replace('/полное руководство по/', '', $clean_title);
    $clean_title = preg_replace('/в \d{4} году/', '', $clean_title);
    $clean_title = trim($clean_title);
    
    // Разбиваем на слова
    $words = preg_split('/[\s,:\-\(\)]+/', $clean_title);
    $words = array_filter($words, function($word) {
        return strlen($word) > 3; // Только слова длиннее 3 символов
    });
    
    if (empty($words)) {
        echo "❌ ID $post_id: Не удалось извлечь ключевые слова из '$current_title'\n";
        $no_matches++;
        continue;
    }
    
    // Ищем оригинальную статью по ключевым словам
    $search_terms = implode(' ', array_slice($words, 0, 3)); // Первые 3 ключевых слова
    
    $original_articles = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_title, post_date, LENGTH(post_content) as content_length
        FROM {$wpdb->posts} 
        WHERE post_type = 'post' 
        AND post_status = 'publish'
        AND post_date NOT LIKE '2025-10-19%'
        AND post_date NOT LIKE '2025-10-07%'
        AND LENGTH(post_content) > 10000
        AND post_content NOT LIKE '%Подробное описание темы статьи%'
        AND post_title LIKE %s
        ORDER BY post_date DESC
        LIMIT 5
    ", '%' . $search_terms . '%'));
    
    if (!empty($original_articles)) {
        echo "✅ ID $post_id: '$current_title'\n";
        echo "   Найдено совпадений: " . count($original_articles) . "\n";
        foreach ($original_articles as $article) {
            echo "   - ID {$article->ID}: '{$article->post_title}' ({$article->content_length} символов)\n";
        }
        $found_matches++;
    } else {
        echo "❌ ID $post_id: '$current_title' - совпадений не найдено\n";
        $no_matches++;
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "=== ИТОГИ ===\n";
echo "Найдено совпадений: $found_matches\n";
echo "Не найдено совпадений: $no_matches\n";
echo "Всего обработано: " . count($all_posts) . "\n";
