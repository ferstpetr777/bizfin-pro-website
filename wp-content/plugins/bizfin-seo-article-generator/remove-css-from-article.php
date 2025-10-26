<?php
/**
 * Удаление CSS кода из статьи - оставляем только чистый HTML контент
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Получаем текущий контент статьи
$post_id = 2986;
$post = get_post($post_id);

if (!$post) {
    die("Статья не найдена");
}

// Получаем контент
$content = $post->post_content;

// Удаляем весь CSS код (теги <style> и их содержимое)
$content = preg_replace('/<style[^>]*>.*?<\/style>/s', '', $content);

// Удаляем DOCTYPE, html, head теги - оставляем только body контент
$content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
$content = preg_replace('/<html[^>]*>/i', '', $content);
$content = preg_replace('/<\/html>/i', '', $content);
$content = preg_replace('/<head[^>]*>.*?<\/head>/s', '', $content);
$content = preg_replace('/<body[^>]*>/i', '', $content);
$content = preg_replace('/<\/body>/i', '', $content);

// Удаляем лишние пробелы и переносы строк
$content = trim($content);

// Обновляем пост
$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => $content
]);

if (is_wp_error($result)) {
    die('Ошибка при обновлении: ' . $result->get_error_message());
}

echo "✅ CSS код удален из статьи!\n";
echo "📝 ID поста: {$post_id}\n";
echo "🔗 URL: " . get_permalink($post_id) . "\n";
echo "🧹 Очистка:\n";
echo "- Удалены все теги <style> и CSS код\n";
echo "- Удалены DOCTYPE, html, head, body теги\n";
echo "- Оставлен только чистый HTML контент\n";
echo "- Статья теперь выглядит нормально для пользователей\n";
echo "\n📈 Статья очищена и готова к просмотру!\n";
?>
