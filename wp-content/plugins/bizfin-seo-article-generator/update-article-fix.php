<?php
/**
 * Обновление статьи с исправлением стилей
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== ОБНОВЛЕНИЕ СТАТЬИ: ИСПРАВЛЕНИЕ СТИЛЕЙ ===\n\n";

// Читаем обновленный HTML контент
$html_content = file_get_contents('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/bizfin-seo-article-generator/generated-article-bank-guarantee-advance-return.html');

// Извлекаем только body контент
$dom = new DOMDocument();
@$dom->loadHTML($html_content);
$xpath = new DOMXPath($dom);
$body = $xpath->query('//body')->item(0);
$body_content = '';
if ($body) {
    foreach ($body->childNodes as $node) {
        $body_content .= $dom->saveHTML($node);
    }
}

// Обновляем пост
$post_id = 2991;
$updated = wp_update_post([
    'ID' => $post_id,
    'post_content' => $body_content
]);

if (is_wp_error($updated)) {
    echo "❌ Ошибка обновления поста: " . $updated->get_error_message() . "\n";
    exit;
}

echo "✅ Статья обновлена успешно!\n";
echo "📊 ID поста: $post_id\n";
echo "🔗 URL: " . get_permalink($post_id) . "\n";
echo "🎨 Исправлен контраст текста в intro-section\n";
echo "✨ Добавлена тень для лучшей читаемости\n";

echo "\n🎉 Проблема с читаемостью исправлена!\n";
?>
