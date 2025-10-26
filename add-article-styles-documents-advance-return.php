<?php
/**
 * Добавление стилей к статье "Документы для банковской гарантии на возврат аванса"
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

echo "📝 Обновление статьи: " . $post->post_title . "\n";

// Читаем CSS файл
$css_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/bizfin-seo-article-generator/article-styles-documents-advance-return.css';
$css_content = file_get_contents($css_file);

if (!$css_content) {
    die("❌ Не удалось прочитать CSS файл\n");
}

// Добавляем стили в начало контента
$updated_content = '<style>' . $css_content . '</style>' . "\n\n" . $post->post_content;

// Обновляем пост
$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => $updated_content
]);

if ($result && !is_wp_error($result)) {
    echo "✅ Стили успешно добавлены к статье\n";
    
    // Обновляем мета-данные
    update_post_meta($post_id, '_bsag_styles_added', true);
    update_post_meta($post_id, '_bsag_styles_file', 'article-styles-documents-advance-return.css');
    
    // Пересчитываем количество слов
    $word_count = str_word_count(strip_tags($post->post_content));
    update_post_meta($post_id, '_bsag_word_count', $word_count);
    
    echo "✅ Количество слов обновлено: $word_count\n";
    
    if ($word_count >= 2500) {
        echo "✅ Требование по минимальному количеству слов выполнено (2500+)\n";
    } else {
        echo "⚠️  Предупреждение: количество слов меньше требуемого минимума\n";
    }
    
    // Получаем URL статьи
    $article_url = get_permalink($post_id);
    echo "✅ Статья обновлена: $article_url\n";
    
    echo "\n🎉 Статья готова к просмотру с полными стилями!\n";
    
} else {
    echo "❌ Ошибка при обновлении статьи: " . (is_wp_error($result) ? $result->get_error_message() : 'Неизвестная ошибка') . "\n";
}
?>

