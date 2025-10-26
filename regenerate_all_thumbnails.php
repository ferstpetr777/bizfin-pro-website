<?php
require_once('wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ПРИНУДИТЕЛЬНАЯ РЕГЕНЕРАЦИЯ ВСЕХ МИНИАТЮР ===\n\n";

$regenerated_count = 0;
$error_count = 0;

foreach ($images as $image_id) {
    $image_title = get_the_title($image_id);
    $image_path = get_attached_file($image_id);
    
    if (!$image_path || !file_exists($image_path)) {
        echo "✗ Файл не найден: ID $image_id - '$image_title'\n";
        $error_count++;
        continue;
    }
    
    echo "Регенерация миниатюр для: ID $image_id - '$image_title'\n";
    
    // Принудительно регенерируем все метаданные и миниатюры
    $attach_data = wp_generate_attachment_metadata($image_id, $image_path);
    $result = wp_update_attachment_metadata($image_id, $attach_data);
    
    if (is_wp_error($result)) {
        echo "  ✗ Ошибка: " . $result->get_error_message() . "\n";
        $error_count++;
    } else {
        echo "  ✓ Миниатюры сгенерированы\n";
        $regenerated_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ РЕГЕНЕРАЦИИ ===\n";
echo "Сгенерировано миниатюр: $regenerated_count\n";
echo "Ошибок: $error_count\n";
echo "Всего изображений обработано: " . count($images) . "\n";
