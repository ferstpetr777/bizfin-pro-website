<?php
require_once('wp-config.php');

// Анализируем несколько статей от 19 октября
$test_articles = [2524, 2430, 2431]; // Базисный пункт, Жилищный сертификат, Банкомат

echo "=== АНАЛИЗ СТАТЕЙ ОТ 19 ОКТЯБРЯ ===\n\n";

foreach ($test_articles as $post_id) {
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post_excerpt = get_post_field('post_excerpt', $post_id);
    $featured_image_id = get_post_thumbnail_id($post_id);
    
    echo "=== СТАТЬЯ ID $post_id ===\n";
    echo "Заголовок: $post_title\n";
    echo "Featured Image ID: $featured_image_id\n\n";
    
    // Проверяем структуру контента
    echo "=== АНАЛИЗ СТРУКТУРЫ ===\n";
    
    // Проверяем наличие Table of Contents
    $has_toc = strpos($post_content, '<!-- wp:table-of-contents') !== false;
    echo "Table of Contents: " . ($has_toc ? "✅ Есть" : "❌ Нет") . "\n";
    
    // Проверяем наличие изображений в контенте
    $image_blocks = substr_count($post_content, '<!-- wp:image');
    echo "Блоков изображений в контенте: $image_blocks\n";
    
    // Проверяем дублирующие изображения
    $duplicate_images = substr_count($post_content, 'wp-image-');
    echo "Всего wp-image- в контенте: $duplicate_images\n";
    
    // Проверяем на CSS код
    $has_css = strpos($post_content, '.intro {') !== false || 
               strpos($post_content, '.toc {') !== false ||
               strpos($post_content, 'border-radius:') !== false;
    echo "CSS код в контенте: " . ($has_css ? "❌ Есть" : "✅ Нет") . "\n";
    
    // Проверяем на сломанный HTML
    $has_broken_html = strpos($post_content, '>> class=') !== false ||
                       strpos($post_content, 'class=>>') !== false;
    echo "Сломанный HTML: " . ($has_broken_html ? "❌ Есть" : "✅ Нет") . "\n";
    
    // Проверяем excerpt
    echo "Excerpt: " . (empty($post_excerpt) ? "❌ Пустой" : "✅ Есть") . "\n";
    if (!empty($post_excerpt)) {
        echo "Excerpt (первые 100 символов): " . substr($post_excerpt, 0, 100) . "...\n";
    }
    
    // Проверяем featured image
    if ($featured_image_id) {
        $image_url = wp_get_attachment_url($featured_image_id);
        $image_file = get_attached_file($featured_image_id);
        echo "Featured Image URL: $image_url\n";
        echo "Featured Image File: $image_file\n";
        echo "Файл существует: " . (file_exists($image_file) ? "✅ Да" : "❌ Нет") . "\n";
    } else {
        echo "❌ Featured Image не установлено\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "=== РЕЗУЛЬТАТ АНАЛИЗА ===\n";
echo "Проанализировано статей: " . count($test_articles) . "\n";
