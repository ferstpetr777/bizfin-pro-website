<?php
require_once('wp-config.php');

// Ищем оригинальный контент в ревизиях постов
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

echo "=== ПОИСК ОРИГИНАЛЬНОГО КОНТЕНТА ===\n";
echo "Всего статей для проверки: " . count($all_posts) . "\n\n";

$found_original = 0;

foreach ($all_posts as $post_id) {
    $post_title = get_the_title($post_id);
    
    echo "=== СТАТЬЯ ID $post_id ===\n";
    echo "Заголовок: $post_title\n";
    
    // Ищем ревизии поста
    $revisions = wp_get_post_revisions($post_id);
    
    if (!empty($revisions)) {
        echo "Найдено ревизий: " . count($revisions) . "\n";
        
        // Берем самую раннюю ревизию
        $latest_revision = array_values($revisions)[0];
        $revision_content = get_post_field('post_content', $latest_revision->ID);
        
        echo "Длина контента ревизии: " . strlen($revision_content) . " символов\n";
        echo "Первые 200 символов ревизии: " . substr($revision_content, 0, 200) . "...\n";
        
        // Проверяем, есть ли в ревизии реальный контент
        if (strlen($revision_content) > 1000 && strpos($revision_content, 'Подробное описание темы статьи') === false) {
            echo "✅ Найден оригинальный контент в ревизии!\n";
            $found_original++;
        } else {
            echo "❌ В ревизии тоже шаблонный контент\n";
        }
    } else {
        echo "❌ Ревизии не найдены\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "=== РЕЗУЛЬТАТ ===\n";
echo "Найдено статей с оригинальным контентом: $found_original\n";

// Также проверим, есть ли в базе данных другие версии постов
global $wpdb;

echo "\n=== ПОИСК В БАЗЕ ДАННЫХ ===\n";

// Ищем посты с похожими заголовками, но другими датами
$similar_posts = $wpdb->get_results($wpdb->prepare("
    SELECT ID, post_title, post_date, post_content 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    AND post_date NOT LIKE '2025-10-19%'
    AND post_date NOT LIKE '2025-10-07%'
    AND post_content NOT LIKE '%Подробное описание темы статьи%'
    AND post_content LIKE '%полное руководство%'
    ORDER BY post_date DESC
    LIMIT 10
"));

echo "Найдено похожих постов с оригинальным контентом: " . count($similar_posts) . "\n";

foreach ($similar_posts as $post) {
    echo "ID: {$post->ID}, Дата: {$post->post_date}, Заголовок: " . substr($post->post_title, 0, 50) . "...\n";
    echo "Длина контента: " . strlen($post->post_content) . " символов\n";
    echo "Первые 100 символов: " . substr($post->post_content, 0, 100) . "...\n\n";
}
