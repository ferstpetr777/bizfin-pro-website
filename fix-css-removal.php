<?php
/**
 * Правильное удаление CSS кода из статьи
 */

// Подключение WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

// ID статьи
$post_id = 3027;

// Получаем пост
$post = get_post($post_id);

if (!$post) {
    die("Статья с ID $post_id не найдена\n");
}

echo "📝 Удаление CSS кода из статьи: " . $post->post_title . "\n";

// Получаем текущий контент
$current_content = $post->post_content;

// Убираем CSS стили из контента (более агрессивное удаление)
$content_without_css = preg_replace('/<style[^>]*>.*?<\/style>/s', '', $current_content);
$content_without_css = preg_replace('/:root\s*\{[^}]*\}/s', '', $content_without_css);
$content_without_css = preg_replace('/\.[a-zA-Z0-9_-]+\s*\{[^}]*\}/s', '', $content_without_css);
$content_without_css = preg_replace('/@media[^{]*\{[^}]*\}/s', '', $content_without_css);

// Убираем лишние пустые строки
$content_without_css = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content_without_css);
$content_without_css = trim($content_without_css);

echo "📊 Длина контента до: " . strlen($current_content) . " символов\n";
echo "📊 Длина контента после: " . strlen($content_without_css) . " символов\n";

// Обновляем пост
$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => $content_without_css
]);

if ($result && !is_wp_error($result)) {
    echo "✅ CSS код полностью удален из контента статьи\n";
    
    // Пересчитываем количество слов
    $word_count = str_word_count(strip_tags($content_without_css));
    update_post_meta($post_id, '_bsag_word_count', $word_count);
    
    echo "✅ Количество слов обновлено: $word_count\n";
    
    // Получаем URL статьи
    $article_url = get_permalink($post_id);
    echo "✅ Статья обновлена: $article_url\n";
    
    echo "\n🎉 CSS код полностью удален! Статья теперь отображается без технического кода.\n";
    
} else {
    echo "❌ Ошибка при обновлении статьи: " . (is_wp_error($result) ? $result->get_error_message() : 'Неизвестная ошибка') . "\n";
}
?>

