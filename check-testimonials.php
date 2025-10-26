<?php
/**
 * Скрипт для проверки загруженных благодарственных писем
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "Проверяем загруженные благодарственные письма...\n\n";

// Находим все вложения с благодарственными письмами
$attachments = get_posts([
    'post_type' => 'attachment',
    'meta_query' => [
        [
            'key' => '_wp_attached_file',
            'value' => '.png',
            'compare' => 'LIKE'
        ]
    ],
    'posts_per_page' => -1
]);

$testimonial_attachments = [];

foreach ($attachments as $attachment) {
    $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
    if (strpos($alt_text, 'Благодарственное письмо') !== false) {
        $testimonial_attachments[] = $attachment;
    }
}

echo "Найдено благодарственных писем: " . count($testimonial_attachments) . "\n\n";

foreach ($testimonial_attachments as $attachment) {
    $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
    $file_url = wp_get_attachment_url($attachment->ID);
    $file_path = get_attached_file($attachment->ID);
    
    echo "ID: {$attachment->ID}\n";
    echo "Название: {$attachment->post_title}\n";
    echo "Alt-текст: {$alt_text}\n";
    echo "URL: {$file_url}\n";
    echo "Описание: " . substr($attachment->post_content, 0, 100) . "...\n";
    echo "Размер файла: " . (file_exists($file_path) ? round(filesize($file_path) / 1024, 2) . " KB" : "Файл не найден") . "\n";
    echo "---\n\n";
}

echo "Проверка завершена!\n";
?>
