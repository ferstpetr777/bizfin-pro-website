<?php
require_once('wp-config.php');

// Проверяем конкретную статью
$post_id = 2863; // ID статьи "Условия получения банковской гарантии"
$post_title = get_the_title($post_id);
$post_content = get_post_field('post_content', $post_id);
$post_excerpt = get_post_field('post_excerpt', $post_id);

echo "=== ПРОВЕРКА СОДЕРЖИМОГО СТАТЬИ ===\n\n";
echo "ID статьи: $post_id\n";
echo "Заголовок: $post_title\n\n";

echo "=== КОНТЕНТ СТАТЬИ (первые 500 символов) ===\n";
echo substr($post_content, 0, 500) . "...\n\n";

echo "=== EXCERPT СТАТЬИ ===\n";
echo $post_excerpt . "\n\n";

echo "=== ПРОВЕРКА НА CSS КОД ===\n";
$has_css = false;
if (strpos($post_content, ':root') !== false) {
    echo "❌ Найден :root в контенте\n";
    $has_css = true;
}
if (strpos($post_content, 'body {') !== false) {
    echo "❌ Найден body { в контенте\n";
    $has_css = true;
}
if (strpos($post_content, 'font-family:') !== false) {
    echo "❌ Найден font-family: в контенте\n";
    $has_css = true;
}
if (strpos($post_excerpt, ':root') !== false) {
    echo "❌ Найден :root в excerpt\n";
    $has_css = true;
}
if (strpos($post_excerpt, 'body {') !== false) {
    echo "❌ Найден body { в excerpt\n";
    $has_css = true;
}
if (strpos($post_excerpt, 'font-family:') !== false) {
    echo "❌ Найден font-family: в excerpt\n";
    $has_css = true;
}

if (!$has_css) {
    echo "✅ CSS код не найден в статье\n";
}

echo "\n=== ПРОВЕРКА НА ПУСТЫЕ БЛОКИ ===\n";
$empty_blocks = substr_count($post_content, '<p></p>');
$empty_paragraphs = substr_count($post_content, '<p> </p>');
echo "Пустых блоков <p></p>: $empty_blocks\n";
echo "Пустых параграфов <p> </p>: $empty_paragraphs\n";
