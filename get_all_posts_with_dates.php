<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "ID\tНазвание статьи\tДата создания\n";
echo "==========================================\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d H:i:s', $post_id);
    echo "$post_id\t$post_title\t$post_date\n";
}
