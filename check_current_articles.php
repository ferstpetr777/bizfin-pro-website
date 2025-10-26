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

echo "=== ТЕКУЩЕЕ СОСТОЯНИЕ СТАТЕЙ В БАЗЕ ДАННЫХ ===\n";
echo "Всего статей: " . count($all_posts) . "\n\n";

foreach ($all_posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $content_length = strlen($post_content);
    
    echo "ID: $post_id\n";
    echo "Заголовок: $post_title\n";
    echo "Размер контента: $content_length символов\n";
    echo "Первые 200 символов: " . substr($post_content, 0, 200) . "...\n";
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

// Теперь ищем оригинальные статьи по заголовкам
echo "\n=== ПОИСК ОРИГИНАЛЬНЫХ СТАТЕЙ ПО ЗАГОЛОВКАМ ===\n";

global $wpdb;

// Получаем список всех статей с похожими заголовками
$original_articles = $wpdb->get_results($wpdb->prepare("
    SELECT ID, post_title, post_date, LENGTH(post_content) as content_length
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND post_date NOT LIKE '2025-10-19%'
    AND post_date NOT LIKE '2025-10-07%'
    AND LENGTH(post_content) > 10000
    AND post_content NOT LIKE '%Подробное описание темы статьи%'
    ORDER BY post_date DESC
    LIMIT 50
"));

echo "Найдено потенциальных оригинальных статей: " . count($original_articles) . "\n\n";

foreach ($original_articles as $article) {
    echo "ID: {$article->ID}\n";
    echo "Заголовок: {$article->post_title}\n";
    echo "Дата: {$article->post_date}\n";
    echo "Размер: {$article->content_length} символов\n";
    echo "\n" . str_repeat("-", 60) . "\n\n";
}
