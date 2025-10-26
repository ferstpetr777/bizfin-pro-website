<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ПРОВЕРКА FEATURED IMAGE ===\n\n";

$post_id = 3049; // ID статьи "Банки, выдающие банковские гарантии на возврат аванса"
$post = get_post($post_id);

if (!$post) {
    echo "Статья не найдена!\n";
    exit;
}

echo "Статья: " . $post->post_title . "\n";
echo "ID: " . $post_id . "\n\n";

$thumbnail_id = get_post_thumbnail_id($post_id);
echo "Featured Image ID: " . $thumbnail_id . "\n";

if ($thumbnail_id) {
    $attachment = get_post($thumbnail_id);
    echo "Attachment ID: " . $attachment->ID . "\n";
    echo "Attachment Title: " . $attachment->post_title . "\n";
    echo "Attachment Status: " . $attachment->post_status . "\n";
    echo "MIME Type: " . $attachment->post_mime_type . "\n";
    
    $file_path = get_attached_file($thumbnail_id);
    echo "File Path: " . $file_path . "\n";
    echo "File Exists: " . (file_exists($file_path) ? "YES" : "NO") . "\n";
    
    if (file_exists($file_path)) {
        $file_size = filesize($file_path);
        echo "File Size: " . $file_size . " bytes\n";
    }
    
    $image_url = wp_get_attachment_url($thumbnail_id);
    echo "Image URL: " . $image_url . "\n";
    
    $image_src = wp_get_attachment_image_src($thumbnail_id, 'full');
    if ($image_src) {
        echo "Image SRC: " . $image_src[0] . "\n";
        echo "Width: " . $image_src[1] . "\n";
        echo "Height: " . $image_src[2] . "\n";
    }
    
    // Проверим метаданные
    $metadata = wp_get_attachment_metadata($thumbnail_id);
    echo "\nMetadata:\n";
    print_r($metadata);
    
} else {
    echo "Featured image не установлена!\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>

