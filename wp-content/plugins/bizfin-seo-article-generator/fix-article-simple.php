<?php
// Load WordPress environment
require_once('../../../../bizfin-pro.ru/wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/post.php');

if (!defined('ABSPATH')) exit;

$post_id = 3021; // ID статьи, которую нужно исправить

// Читаем HTML файл
$html_content = file_get_contents(__DIR__ . '/generated-article-bank-guarantee-term.html');

// Извлекаем содержимое между <body> и </body>
$start = strpos($html_content, '<body>');
$end = strpos($html_content, '</body>');

if ($start !== false && $end !== false) {
    $body_content = substr($html_content, $start + 6, $end - $start - 6);
    
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
    echo "Ошибка: не удалось найти теги body в HTML документе\n";
}
?>
