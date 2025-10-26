<?php
require_once('wp-config.php');

// Находим статьи от 19 октября и 7 октября
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

echo "=== СТАТЬИ ОТ 19 ОКТЯБРЯ ===\n";
echo "Найдено статей: " . count($october_19_posts) . "\n";
foreach ($october_19_posts as $post_id) {
    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    echo "ID: $post_id | $title\n";
    echo "URL: $url\n\n";
}

echo "=== СТАТЬИ ОТ 7 ОКТЯБРЯ ===\n";
echo "Найдено статей: " . count($october_7_posts) . "\n";
foreach ($october_7_posts as $post_id) {
    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    echo "ID: $post_id | $title\n";
    echo "URL: $url\n\n";
}

echo "=== ОБЩИЙ ИТОГ ===\n";
echo "Всего статей от 19 октября: " . count($october_19_posts) . "\n";
echo "Всего статей от 7 октября: " . count($october_7_posts) . "\n";
echo "Общее количество: " . (count($october_19_posts) + count($october_7_posts)) . "\n";
