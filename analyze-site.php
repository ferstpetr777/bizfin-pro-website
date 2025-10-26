<?php
/**
 * Анализ структуры сайта для создания sitemap
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "=== АНАЛИЗ СТРУКТУРЫ САЙТА ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

$site_url = 'https://bizfin-pro.ru';

// 1. Анализ основных страниц
echo "📄 ОСНОВНЫЕ СТРАНИЦЫ:\n";
$main_pages = [
    '/' => 'Главная страница',
    '/kalkulyator-bankovskih-garantij/' => 'Калькулятор банковских гарантий',
    '/kejsy/' => 'Кейсы',
    '/blog/' => 'Блог',
    '/kontakty/' => 'Контакты'
];

foreach ($main_pages as $url => $title) {
    echo "✅ $url - $title\n";
}

// 2. Анализ статей блога
echo "\n📝 СТАТЬИ БЛОГА:\n";
$posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
]);

echo "Всего статей: " . count($posts) . "\n";
foreach ($posts as $post) {
    $post_url = get_permalink($post->ID);
    $relative_url = str_replace($site_url, '', $post_url);
    echo "✅ $relative_url - " . wp_trim_words($post->post_title, 5) . "\n";
}

// 3. Анализ изображений
echo "\n🖼️ ИЗОБРАЖЕНИЯ:\n";
$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1
]);

echo "Всего изображений: " . count($attachments) . "\n";
$image_count = 0;
foreach ($attachments as $attachment) {
    $image_url = wp_get_attachment_url($attachment->ID);
    if ($image_url) {
        $relative_url = str_replace($site_url, '', $image_url);
        echo "✅ $relative_url\n";
        $image_count++;
        if ($image_count >= 10) {
            echo "... и еще " . (count($attachments) - 10) . " изображений\n";
            break;
        }
    }
}

// 4. Статистика
echo "\n📊 СТАТИСТИКА:\n";
echo "Основных страниц: " . count($main_pages) . "\n";
echo "Статей блога: " . count($posts) . "\n";
echo "Изображений: " . count($attachments) . "\n";
echo "Всего URL: " . (count($main_pages) + count($posts) + count($attachments)) . "\n";

echo "\nЗавершено: " . date('Y-m-d H:i:s') . "\n";
?>

