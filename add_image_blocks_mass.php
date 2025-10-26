<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 19 и 7 октября 2025 года
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$updated_count = 0;
$skipped_count = 0;
$no_image_count = 0;
$total_processed = 0;

echo "=== ДОБАВЛЕНИЕ БЛОКОВ ИЗОБРАЖЕНИЙ В СТАТЬИ ОТ 19 И 7 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $post_content = get_post_field('post_content', $post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);

    // Проверяем, есть ли уже блок изображения
    if (strpos($post_content, '<!-- wp:image') !== false) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - уже имеет блок изображения.\n";
        $skipped_count++;
        continue;
    }

    // Проверяем, есть ли главное изображение
    if (!$thumbnail_id) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - нет главного изображения.\n";
        $no_image_count++;
        continue;
    }

    // Получаем информацию об изображении
    $image_url = wp_get_attachment_url($thumbnail_id);
    $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
    $image_title = get_the_title($thumbnail_id);

    if (!$image_url) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - не удалось получить URL изображения.\n";
        $no_image_count++;
        continue;
    }

    // Создаем блок изображения Gutenberg
    $image_block = '<!-- wp:image {"sizeSlug":"large","align":"wide","className":"ios-style-image"} -->
<figure class="wp-block-image size-large ios-style-image alignwide"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt ?: $image_title) . '" class="wp-image-' . $thumbnail_id . '"/></figure>
<!-- /wp:image -->';

    // Ищем блок содержания в контенте
    $content_parts = explode('<div style="background: #f8f9fa;padding: 20px;border-radius: 8px;margin: 20px 0;border-left: 4px solid #007cba">', $post_content);

    if (count($content_parts) > 1) {
        // Находим конец блока содержания
        $toc_end = strpos($content_parts[1], '</div>');
        if ($toc_end !== false) {
            $toc_end += 6; // +6 для длины '</div>'
            $toc_block = substr($content_parts[1], 0, $toc_end);
            $rest_content = substr($content_parts[1], $toc_end);
            
            // Собираем новый контент: блок содержания + блок изображения + остальной контент
            $new_content = $content_parts[0] . '<div style="background: #f8f9fa;padding: 20px;border-radius: 8px;margin: 20px 0;border-left: 4px solid #007cba">' . $toc_block . "\n\n" . $image_block . "\n\n" . $rest_content;
            
            // Обновляем статью
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $new_content
            ));
            
            echo "✔ Добавлен блок изображения в статью ID $post_id ($post_date): '$post_title'\n";
            $updated_count++;
        } else {
            echo "✗ Не удалось найти конец блока содержания в статье ID $post_id ($post_date): '$post_title'\n";
            $skipped_count++;
        }
    } else {
        echo "✗ Блок содержания не найден в статье ID $post_id ($post_date): '$post_title'\n";
        $skipped_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Обновлено статей: $updated_count\n";
echo "Пропущено статей (уже имеют блок изображения): $skipped_count\n";
echo "Пропущено статей (нет главного изображения): $no_image_count\n";
echo "Всего обработано статей: $total_processed\n";
?>
