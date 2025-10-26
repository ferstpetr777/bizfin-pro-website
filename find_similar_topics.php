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

echo "=== АНАЛИЗ ТЕМАТИК СТАТЕЙ ===\n";
echo "Всего статей: " . count($all_posts) . "\n\n";

// Группируем статьи по темам
$topics = array();

foreach ($all_posts as $post_id) {
    $title = get_the_title($post_id);
    
    // Извлекаем основную тему
    $topic = '';
    if (strpos($title, 'банковск') !== false) $topic = 'Банковские услуги';
    elseif (strpos($title, 'кредит') !== false) $topic = 'Кредиты';
    elseif (strpos($title, 'вклад') !== false) $topic = 'Вклады';
    elseif (strpos($title, 'карт') !== false) $topic = 'Банковские карты';
    elseif (strpos($title, 'инвест') !== false) $topic = 'Инвестиции';
    elseif (strpos($title, 'гарант') !== false) $topic = 'Банковские гарантии';
    elseif (strpos($title, 'страхов') !== false) $topic = 'Страхование';
    elseif (strpos($title, 'налог') !== false) $topic = 'Налоги';
    elseif (strpos($title, 'ипотек') !== false) $topic = 'Ипотека';
    elseif (strpos($title, 'договор') !== false) $topic = 'Договоры';
    else $topic = 'Другое';
    
    if (!isset($topics[$topic])) {
        $topics[$topic] = array();
    }
    $topics[$topic][] = array('id' => $post_id, 'title' => $title);
}

// Выводим статистику по темам
foreach ($topics as $topic => $articles) {
    echo "=== $topic ===\n";
    echo "Количество статей: " . count($articles) . "\n";
    echo "Примеры:\n";
    foreach (array_slice($articles, 0, 3) as $article) {
        echo "- ID {$article['id']}: {$article['title']}\n";
    }
    echo "\n";
}

// Теперь ищем оригинальные статьи по темам
echo "=== ПОИСК ОРИГИНАЛЬНЫХ СТАТЕЙ ПО ТЕМАМ ===\n";

global $wpdb;

$topic_keywords = array(
    'Банковские услуги' => array('банк', 'банковск', 'услуг'),
    'Кредиты' => array('кредит', 'займ', 'заем'),
    'Вклады' => array('вклад', 'депозит', 'сбережен'),
    'Банковские карты' => array('карт', 'платеж', 'пластик'),
    'Инвестиции' => array('инвест', 'портфель', 'акци'),
    'Банковские гарантии' => array('гарант', 'обеспечен'),
    'Страхование' => array('страхов', 'КАСКО', 'ОСАГО'),
    'Налоги' => array('налог', 'налогообложен'),
    'Ипотека' => array('ипотек', 'недвижимост'),
    'Договоры' => array('договор', 'соглашен', 'контракт')
);

foreach ($topic_keywords as $topic => $keywords) {
    $search_term = implode(' ', $keywords);
    
    $original_articles = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_title, post_date, LENGTH(post_content) as content_length
        FROM {$wpdb->posts} 
        WHERE post_type = 'post' 
        AND post_status = 'publish'
        AND post_date NOT LIKE '2025-10-19%'
        AND post_date NOT LIKE '2025-10-07%'
        AND LENGTH(post_content) > 10000
        AND post_content NOT LIKE '%Подробное описание темы статьи%'
        AND (post_title LIKE %s OR post_content LIKE %s)
        ORDER BY post_date DESC
        LIMIT 10
    ", '%' . $search_term . '%', '%' . $search_term . '%'));
    
    if (!empty($original_articles)) {
        echo "=== $topic ===\n";
        echo "Найдено оригинальных статей: " . count($original_articles) . "\n";
        foreach ($original_articles as $article) {
            echo "- ID {$article->ID}: '{$article->post_title}' ({$article->content_length} символов)\n";
        }
        echo "\n";
    }
}
