<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 23 октября 2025
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 23
        )
    ),
    'fields' => 'ids'
));

echo "=== СТАТИСТИКА СТАТЕЙ ЗА 23 ОКТЯБРЯ 2025 ===\n\n";
echo "Всего статей создано 23 октября: " . count($posts) . "\n\n";

echo "Список статей:\n";
echo "ID\tНазвание статьи\tВремя создания\n";
echo "==========================================\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('H:i:s', $post_id);
    echo "$post_id\t$post_title\t$post_date\n";
}
