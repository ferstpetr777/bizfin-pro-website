<?php
require_once('wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$image_id = 2665;
$image_title = get_the_title($image_id);
$image_path = get_attached_file($image_id);

echo "=== ГЕНЕРАЦИЯ МИНИАТЮР ДЛЯ ИЗОБРАЖЕНИЯ ID $image_id ===\n\n";

if (!$image_path || !file_exists($image_path)) {
    echo "✗ Файл изображения не найден: $image_path\n";
    exit;
}

echo "Файл найден: $image_path\n";
echo "Название: $image_title\n\n";

// Генерируем метаданные и миниатюры
$attach_data = wp_generate_attachment_metadata($image_id, $image_path);
$result = wp_update_attachment_metadata($image_id, $attach_data);

if (is_wp_error($result)) {
    echo "✗ Ошибка при генерации миниатюр: " . $result->get_error_message() . "\n";
} else {
    echo "✓ Миниатюры сгенерированы успешно\n";
}

// Проверяем созданные размеры
$image_meta = wp_get_attachment_metadata($image_id);
if (isset($image_meta['sizes'])) {
    echo "\nСозданные размеры:\n";
    foreach ($image_meta['sizes'] as $size => $size_data) {
        echo "- $size: {$size_data['width']}x{$size_data['height']}\n";
    }
}
?>
