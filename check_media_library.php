<?php
require_once('wp-config.php');

// Получаем все изображения из медиатеки
$all_images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА МЕДИАТЕКИ ===\n\n";
echo "Всего изображений в медиатеке: " . count($all_images) . "\n\n";

// Проверяем несколько примеров статей
$test_articles = array(
    2523 => 'Аннуитетные платежи по кредиту',
    2522 => 'Аллонж векселя',
    2521 => 'Аффинитикарты полное',
    2520 => 'Тендерная гарантия',
    2519 => 'Сущность кредита и его свойства'
);

foreach ($test_articles as $post_id => $search_term) {
    echo "--- Поиск изображений для статьи ID $post_id: '$search_term' ---\n";
    
    // Ищем изображения по частичному совпадению
    $matching_images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'numberposts' => 5,
        's' => $search_term,
        'fields' => 'ids'
    ));
    
    if (!empty($matching_images)) {
        echo "Найдено изображений: " . count($matching_images) . "\n";
        foreach ($matching_images as $image_id) {
            $image_title = get_the_title($image_id);
            $image_file = get_attached_file($image_id);
            $image_url = wp_get_attachment_url($image_id);
            echo "  - ID: $image_id, Название: '$image_title'\n";
            echo "    Файл: $image_file\n";
            echo "    URL: $image_url\n";
            echo "    Существует: " . (file_exists($image_file) ? 'Да' : 'Нет') . "\n";
        }
    } else {
        echo "Изображения не найдены\n";
    }
    echo "\n";
}

// Проверяем все изображения, которые НЕ являются фавиконами
echo "=== ИЗОБРАЖЕНИЯ БЕЗ ФАВИКОНОВ ===\n";
$non_favicon_count = 0;
$favicon_count = 0;

foreach ($all_images as $image_id) {
    $image_file = get_attached_file($image_id);
    $image_title = get_the_title($image_id);
    
    if ($image_file && stripos($image_file, 'cropped-Бизнес-Финанс-—-фавикон') === false) {
        $non_favicon_count++;
        if ($non_favicon_count <= 10) { // Показываем только первые 10
            echo "ID: $image_id, Название: '$image_title', Файл: $image_file\n";
        }
    } else {
        $favicon_count++;
    }
}

echo "\nСтатистика:\n";
echo "Изображения без фавиконов: $non_favicon_count\n";
echo "Фавиконы: $favicon_count\n";
echo "Всего: " . count($all_images) . "\n";
?>
