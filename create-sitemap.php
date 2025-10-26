<?php
/**
 * Создание XML sitemap для сайта
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "=== СОЗДАНИЕ XML SITEMAP ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

$site_url = 'https://bizfin-pro.ru';

// 1. Создаем основной sitemap
$sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 2. Добавляем основные страницы
$main_pages = [
    '/' => ['priority' => '1.0', 'changefreq' => 'daily'],
    '/kalkulyator-bankovskih-garantij/' => ['priority' => '0.9', 'changefreq' => 'weekly'],
    '/kejsy/' => ['priority' => '0.8', 'changefreq' => 'weekly'],
    '/blog/' => ['priority' => '0.8', 'changefreq' => 'daily'],
    '/kontakty/' => ['priority' => '0.7', 'changefreq' => 'monthly']
];

foreach ($main_pages as $url => $settings) {
    $sitemap_content .= '
  <url>
    <loc>' . $site_url . $url . '</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>' . $settings['changefreq'] . '</changefreq>
    <priority>' . $settings['priority'] . '</priority>
  </url>';
}

// 3. Добавляем статьи блога
$posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
]);

foreach ($posts as $post) {
    $post_url = get_permalink($post->ID);
    $lastmod = date('Y-m-d', strtotime($post->post_modified));
    
    $sitemap_content .= '
  <url>
    <loc>' . $post_url . '</loc>
    <lastmod>' . $lastmod . '</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>';
}

$sitemap_content .= '
</urlset>';

// 4. Сохраняем основной sitemap
file_put_contents('sitemap.xml', $sitemap_content);
echo "✅ Основной sitemap.xml создан\n";

// 5. Создаем sitemap для изображений
$image_sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1
]);

foreach ($attachments as $attachment) {
    $image_url = wp_get_attachment_url($attachment->ID);
    if ($image_url) {
        $image_sitemap_content .= '
  <url>
    <loc>' . $image_url . '</loc>
    <image:image>
      <image:loc>' . $image_url . '</image:loc>
      <image:title>' . htmlspecialchars($attachment->post_title) . '</image:title>
      <image:caption>' . htmlspecialchars($attachment->post_excerpt) . '</image:caption>
    </image:image>
  </url>';
    }
}

$image_sitemap_content .= '
</urlset>';

// 6. Сохраняем sitemap для изображений
file_put_contents('sitemap-images.xml', $image_sitemap_content);
echo "✅ Sitemap для изображений sitemap-images.xml создан\n";

// 7. Создаем robots.txt
$robots_content = 'User-agent: *
Allow: /

# Sitemaps
Sitemap: ' . $site_url . '/sitemap.xml
Sitemap: ' . $site_url . '/sitemap-images.xml

# Disallow admin areas
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Disallow: /wp-content/themes/
Disallow: /wp-content/cache/
Disallow: /wp-content/uploads/cache/

# Allow important directories
Allow: /wp-content/uploads/

# Crawl delay
Crawl-delay: 1';

file_put_contents('robots.txt', $robots_content);
echo "✅ robots.txt создан\n";

echo "\n📊 СТАТИСТИКА:\n";
echo "Основных страниц: " . count($main_pages) . "\n";
echo "Статей блога: " . count($posts) . "\n";
echo "Изображений: " . count($attachments) . "\n";
echo "Всего URL в sitemap: " . (count($main_pages) + count($posts)) . "\n";

echo "\n🎯 ФАЙЛЫ СОЗДАНЫ:\n";
echo "✅ sitemap.xml - основной sitemap\n";
echo "✅ sitemap-images.xml - sitemap для изображений\n";
echo "✅ robots.txt - файл для поисковых роботов\n";

echo "\nЗавершено: " . date('Y-m-d H:i:s') . "\n";
?>

