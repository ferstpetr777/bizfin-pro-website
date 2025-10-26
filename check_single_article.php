<?php
require_once('wp-config.php');

// Проверяем конкретную статью
$post_id = 2863; // ID статьи "Условия получения банковской гарантии"
$post_title = get_the_title($post_id);
$post_content = get_post_field('post_content', $post_id);
$post_excerpt = get_post_field('post_excerpt', $post_id);
$featured_image_id = get_post_thumbnail_id($post_id);

echo "=== ПРОВЕРКА СТАТЬИ ID $post_id ===\n\n";
echo "Заголовок: $post_title\n";
echo "Featured Image ID: $featured_image_id\n\n";

echo "=== КОНТЕНТ (первые 1000 символов) ===\n";
echo substr($post_content, 0, 1000) . "...\n\n";

echo "=== EXCERPT ===\n";
echo $post_excerpt . "\n\n";

echo "=== ПРОВЕРКА НА ПУСТЫЕ БЛОКИ ===\n";
$empty_paragraphs = substr_count($post_content, '<p></p>');
$empty_paragraphs_spaced = substr_count($post_content, '<p> </p>');
echo "Пустых блоков <p></p>: $empty_paragraphs\n";
echo "Пустых блоков <p> </p>: $empty_paragraphs_spaced\n\n";

echo "=== ПРОВЕРКА НА CSS КОД ===\n";
$has_css = false;
if (strpos($post_content, ':root') !== false) {
    echo "❌ Найден :root в контенте\n";
    $has_css = true;
}
if (strpos($post_content, '.container {') !== false) {
    echo "❌ Найден .container { в контенте\n";
    $has_css = true;
}
if (strpos($post_content, 'font-size:') !== false) {
    echo "❌ Найден font-size: в контенте\n";
    $has_css = true;
}
if (strpos($post_content, '.intro {') !== false) {
    echo "❌ Найден .intro { в контенте\n";
    $has_css = true;
}
if (strpos($post_content, '.toc {') !== false) {
    echo "❌ Найден .toc { в контенте\n";
    $has_css = true;
}

if (!$has_css) {
    echo "✅ CSS код не найден в контенте\n";
}

echo "\n=== ПРОВЕРКА НА ИЗОБРАЖЕНИЯ ===\n";
if ($featured_image_id) {
    $image_url = wp_get_attachment_url($featured_image_id);
    $image_file = get_attached_file($featured_image_id);
    echo "Featured Image URL: $image_url\n";
    echo "Featured Image File: $image_file\n";
    echo "Файл существует: " . (file_exists($image_file) ? "✅ Да" : "❌ Нет") . "\n";
} else {
    echo "❌ Featured Image не установлено\n";
}
