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

echo "=== ВОССТАНОВЛЕНИЕ ОРИГИНАЛЬНОГО КОНТЕНТА ===\n";
echo "Всего статей для обработки: " . count($all_posts) . "\n\n";

// Создаем резервную копию оригинального контента
$backup_content = array();

foreach ($all_posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    
    echo "=== СТАТЬЯ ID $post_id ===\n";
    echo "Заголовок: $post_title\n";
    
    // Сохраняем оригинальный контент в резервную копию
    $backup_content[$post_id] = array(
        'title' => $post_title,
        'content' => $post_content,
        'excerpt' => get_post_field('post_excerpt', $post_id)
    );
    
    echo "Оригинальный контент сохранен в резервную копию\n";
    echo "Длина контента: " . strlen($post_content) . " символов\n";
    echo "Первые 200 символов: " . substr($post_content, 0, 200) . "...\n";
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Сохраняем резервную копию в файл
file_put_contents('original_content_backup.json', json_encode($backup_content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Резервная копия сохранена в original_content_backup.json\n";
echo "Всего статей в резервной копии: " . count($backup_content) . "\n";
