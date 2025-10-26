<?php
/**
 * Удаление CSS кода из видимого контента статьи
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

// Убираем CSS стили из контента
$content_without_css = preg_replace('/<style>.*?<\/style>/s', '', $post->post_content);

// Обновляем пост
$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => $content_without_css
]);

if ($result && !is_wp_error($result)) {
    echo "✅ CSS код удален из видимого контента статьи\n";
    
    // Пересчитываем количество слов
    $word_count = str_word_count(strip_tags($content_without_css));
    update_post_meta($post_id, '_bsag_word_count', $word_count);
    
    echo "✅ Количество слов обновлено: $word_count\n";
    
    // Получаем URL статьи
    $article_url = get_permalink($post_id);
    echo "✅ Статья обновлена: $article_url\n";
    
    echo "\n🎉 CSS код удален! Статья теперь отображается без технического кода.\n";
    
} else {
    echo "❌ Ошибка при обновлении статьи: " . (is_wp_error($result) ? $result->get_error_message() : 'Неизвестная ошибка') . "\n";
}
?>

