<?php
// Load WordPress environment
require_once('../../../../bizfin-pro.ru/wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/post.php');

if (!defined('ABSPATH')) exit;

$post_id = 3021; // ID статьи, которую нужно исправить

// Читаем HTML файл
$html_content = file_get_contents(__DIR__ . '/generated-article-bank-guarantee-term.html');

// Извлекаем только содержимое body (без DOCTYPE, html, head)
$dom = new DOMDocument();
$dom->loadHTML($html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

// Находим body и извлекаем его содержимое
$body = $dom->getElementsByTagName('body')->item(0);
if ($body) {
    $body_content = '';
    foreach ($body->childNodes as $node) {
        $body_content .= $dom->saveHTML($node);
    }
    
    // Обновляем пост
    $post_data = array(
        'ID'           => $post_id,
        'post_content' => $body_content,
    );
    
    $result = wp_update_post($post_data);
    
    if (is_wp_error($result)) {
        echo "Ошибка при обновлении поста: " . $result->get_error_message() . "\n";
    } else {
        echo "✅ Статья исправлена успешно!\n";
        echo "📊 ID поста: {$post_id}\n";
        echo "🔗 URL: " . get_permalink($post_id) . "\n";
        echo "🎨 Убран лишний HTML код\n";
        echo "✨ Оставлено только содержимое body\n";
        echo "\n🎉 Статья теперь отображается корректно!\n";
    }
} else {
    echo "Ошибка: не удалось найти body в HTML документе\n";
}
?>
