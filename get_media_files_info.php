<?php
/**
 * Скрипт для получения информации о загруженных файлах в медиатеке
 */

require_once('wp-load.php');

echo "Получение информации о загруженных файлах в медиатеке...\n\n";

// Получаем все изображения из медиатеки
$args = array(
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => -1,
    'post_status'    => 'inherit',
    'orderby'        => 'date',
    'order'          => 'DESC'
);

$attachments = get_posts($args);

echo "Найдено изображений в медиатеке: " . count($attachments) . "\n\n";

$media_info = [];

foreach ($attachments as $attachment) {
    $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
    $description = $attachment->post_content;
    $image_url = wp_get_attachment_url($attachment->ID);
    $file_path = get_attached_file($attachment->ID);
    
    $media_info[] = [
        'id' => $attachment->ID,
        'title' => $attachment->post_title,
        'alt' => $alt_text,
        'description' => $description,
        'url' => $image_url,
        'filename' => basename($file_path),
        'date' => $attachment->post_date
    ];
}

// Сортируем по дате (новые первыми)
usort($media_info, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Показываем информацию о файлах
foreach ($media_info as $index => $info) {
    echo "=== Файл " . ($index + 1) . " ===\n";
    echo "ID: " . $info['id'] . "\n";
    echo "Название: " . $info['title'] . "\n";
    echo "Файл: " . $info['filename'] . "\n";
    echo "URL: " . $info['url'] . "\n";
    echo "Alt-текст: " . $info['alt'] . "\n";
    echo "Описание: " . substr($info['description'], 0, 100) . "...\n";
    echo "Дата загрузки: " . $info['date'] . "\n";
    echo "---\n\n";
}

// Сохраняем информацию в файл
$info_file = __DIR__ . '/media_files_info.txt';
$content = "Информация о файлах в медиатеке\n";
$content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$content .= "Всего файлов: " . count($media_info) . "\n\n";

foreach ($media_info as $index => $info) {
    $content .= "=== Файл " . ($index + 1) . " ===\n";
    $content .= "ID: " . $info['id'] . "\n";
    $content .= "URL: " . $info['url'] . "\n";
    $content .= "Файл: " . $info['filename'] . "\n\n";
}

file_put_contents($info_file, $content);

echo "Информация сохранена в файл: media_files_info.txt\n";
?>
