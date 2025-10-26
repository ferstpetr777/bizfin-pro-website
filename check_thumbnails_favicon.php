<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА МИНИАТЮР НА НАЛИЧИЕ ФАВИКОНОВ ===\n\n";

// Найдем ID фавикона
$favicon_id = null;
$favicon_posts = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

foreach ($favicon_posts as $image_id) {
    $image_title = get_the_title($image_id);
    if (strpos(strtolower($image_title), 'фавикон') !== false || 
        strpos(strtolower($image_title), 'favicon') !== false) {
        $favicon_id = $image_id;
        echo "Найден фавикон ID: $favicon_id - '$image_title'\n";
        break;
    }
}

$favicon_articles = array();
$no_thumbnail = array();
$broken_thumbnails = array();
$good_thumbnails = array();

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_thumbnail[] = array('id' => $post_id, 'title' => $post_title);
        continue;
    }
    
    // Проверяем, является ли это фавиконом
    if ($favicon_id && $thumbnail_id == $favicon_id) {
        $favicon_articles[] = array('id' => $post_id, 'title' => $post_title);
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($thumbnail_id);
    if (!$image_path || !file_exists($image_path)) {
        $broken_thumbnails[] = array('id' => $post_id, 'title' => $post_title, 'image_id' => $thumbnail_id);
        continue;
    }
    
    $good_thumbnails[] = array('id' => $post_id, 'title' => $post_title, 'image_id' => $thumbnail_id);
}

echo "\n=== РЕЗУЛЬТАТЫ ПРОВЕРКИ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Статей с фавиконом: " . count($favicon_articles) . "\n";
echo "Статей без миниатюры: " . count($no_thumbnail) . "\n";
echo "Статей с отсутствующими файлами: " . count($broken_thumbnails) . "\n";
echo "Статей с корректными миниатюрами: " . count($good_thumbnails) . "\n";

if (!empty($favicon_articles)) {
    echo "\n=== СТАТЬИ С ФАВИКОНОМ ===\n";
    foreach ($favicon_articles as $article) {
        echo "ID {$article['id']}: {$article['title']}\n";
    }
}

if (!empty($no_thumbnail)) {
    echo "\n=== СТАТЬИ БЕЗ МИНИАТЮРЫ ===\n";
    foreach ($no_thumbnail as $article) {
        echo "ID {$article['id']}: {$article['title']}\n";
    }
}

if (!empty($broken_thumbnails)) {
    echo "\n=== СТАТЬИ С ОТСУТСТВУЮЩИМИ ФАЙЛАМИ ===\n";
    foreach ($broken_thumbnails as $article) {
        echo "ID {$article['id']}: {$article['title']} (Изображение ID: {$article['image_id']})\n";
    }
}
