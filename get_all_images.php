<?php
require_once('wp-config.php');

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "ID\tНазвание изображения\n";
echo "======================\n";

foreach ($images as $image_id) {
    $image_title = get_the_title($image_id);
    echo "$image_id\t$image_title\n";
}
