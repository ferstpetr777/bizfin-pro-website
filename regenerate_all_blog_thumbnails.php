<?php
require_once('wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$total_posts = 0;
$regenerated_count = 0;
$already_existed_count = 0;
$error_count = 0;
$no_image_count = 0;

echo "=== РЕГЕНЕРАЦИЯ ВСЕХ МИНИАТЮР ДЛЯ РУБРИКАТОРА БЛОГА ===\n\n";

foreach ($posts as $post_id) {
    $total_posts++;
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);

    if (!$thumbnail_id) {
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        $no_image_count++;
        continue;
    }

    $image_path = get_attached_file($thumbnail_id);
    if (!$image_path || !file_exists($image_path)) {
        echo "✗ Статья ID $post_id: '$post_title' - файл изображения отсутствует (ID: $thumbnail_id)\n";
        $error_count++;
        continue;
    }

    // Проверяем, существуют ли все размеры миниатюр
    $image_meta = wp_get_attachment_metadata($thumbnail_id);
    $standard_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
    $missing_sizes = array();

    foreach ($standard_sizes as $size) {
        if (!isset($image_meta['sizes'][$size])) {
            $missing_sizes[] = $size;
        }
    }

    if (!empty($missing_sizes)) {
        echo "Генерация миниатюр для статьи ID $post_id: '$post_title'\n";
        echo "  Отсутствующие размеры: " . implode(', ', $missing_sizes) . "\n";
        
        $attach_data = wp_generate_attachment_metadata($thumbnail_id, $image_path);
        $result = wp_update_attachment_metadata($thumbnail_id, $attach_data);

        if (is_wp_error($result)) {
            echo "  ✗ Ошибка при генерации миниатюр: " . $result->get_error_message() . "\n";
            $error_count++;
        } else {
            echo "  ✓ Миниатюры сгенерированы успешно\n";
            $regenerated_count++;
        }
    } else {
        // Принудительно регенерируем все миниатюры для обновления
        echo "Принудительная регенерация миниатюр для статьи ID $post_id: '$post_title'\n";
        
        $attach_data = wp_generate_attachment_metadata($thumbnail_id, $image_path);
        $result = wp_update_attachment_metadata($thumbnail_id, $attach_data);

        if (is_wp_error($result)) {
            echo "  ✗ Ошибка при регенерации миниатюр: " . $result->get_error_message() . "\n";
            $error_count++;
        } else {
            echo "  ✓ Миниатюры регенерированы успешно\n";
            $regenerated_count++;
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ РЕГЕНЕРАЦИИ ===\n";
echo "Всего статей обработано: $total_posts\n";
echo "Миниатюры регенерированы: $regenerated_count\n";
echo "Статей без главного изображения: $no_image_count\n";
echo "Ошибок: $error_count\n";
?>
