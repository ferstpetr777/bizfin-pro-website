<?php
require_once('wp-config.php');

// Получаем все изображения из медиатеки
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'fields' => 'ids'
));

$generated_count = 0;
$already_exist_count = 0;
$errors = array();

echo "=== ГЕНЕРАЦИЯ МИНИАТЮР ДЛЯ ВСЕХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($images as $image_id) {
    $image_title = get_the_title($image_id);
    $image_path = get_attached_file($image_id);
    
    if (!$image_path || !file_exists($image_path)) {
        echo "✗ Файл изображения не найден: ID $image_id - '$image_title'\n";
        $errors[] = "Файл не найден для изображения ID $image_id";
        continue;
    }
    
    // Проверяем, существуют ли уже миниатюры
    $thumbnail_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
    $missing_sizes = array();
    
    foreach ($thumbnail_sizes as $size) {
        $image_data = wp_get_attachment_image_src($image_id, $size);
        if (!$image_data || !file_exists($image_data[0])) {
            $missing_sizes[] = $size;
        }
    }
    
    if (empty($missing_sizes)) {
        $already_exist_count++;
        continue;
    }
    
    echo "Генерация миниатюр для: ID $image_id - '$image_title'\n";
    echo "  Отсутствующие размеры: " . implode(', ', $missing_sizes) . "\n";
    
    // Генерируем миниатюры
    $metadata = wp_generate_attachment_metadata($image_id, $image_path);
    
    if ($metadata && !is_wp_error($metadata)) {
        wp_update_attachment_metadata($image_id, $metadata);
        $generated_count++;
        echo "  ✓ Миниатюры сгенерированы успешно\n";
    } else {
        echo "  ✗ Ошибка генерации миниатюр\n";
        $errors[] = "Ошибка генерации для изображения ID $image_id";
    }
    
    echo "\n";
}

echo "=== РЕЗУЛЬТАТЫ ГЕНЕРАЦИИ ===\n";
echo "Сгенерировано миниатюр: $generated_count\n";
echo "Уже существовали: $already_exist_count\n";
echo "Ошибок: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n=== ОШИБКИ ===\n";
    foreach ($errors as $error) {
        echo "$error\n";
    }
}
