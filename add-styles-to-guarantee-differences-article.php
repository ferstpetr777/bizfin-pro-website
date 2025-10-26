<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    echo "WordPress не загружен.";
    exit;
}

wp_set_current_user(1);

$article_title = "Гарантия обеспечения возврата авансового платежа и исполнение контракта: в чём разница";
$css_content = file_get_contents(__DIR__ . '/wp-content/themes/bizfin-pro/article-styles-guarantee-differences.css');

$existing_post = get_page_by_title($article_title, OBJECT, 'post');

if ($existing_post) {
    $post_id = $existing_post->ID;
    echo "📝 Добавление стилей к статье: " . $article_title . "\n";

    // Получаем текущий контент
    $current_content = $existing_post->post_content;

    // Удаляем все предыдущие <style> блоки, если они есть
    $current_content_cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $current_content);

    // Добавляем новые стили в начало контента
    $updated_content = '<style>' . $css_content . '</style>' . $current_content_cleaned;

    // Функция для корректного подсчета русских слов
    function count_russian_words($text) {
        $text = strip_tags($text); // Удаляем HTML теги
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text); // Удаляем все, кроме букв, цифр и пробелов
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY); // Разбиваем по пробелам
        return count($words);
    }

    $word_count = count_russian_words($current_content_cleaned);

    $post_data = array(
        'ID'           => $post_id,
        'post_content' => $updated_content,
        'post_status'  => 'publish',
        'post_author'  => 1,
        'post_type'    => 'post',
    );
    $result = wp_update_post($post_data);

    if (is_wp_error($result)) {
        echo "Ошибка при обновлении статьи: " . $result->get_error_message() . "\n";
    } else {
        echo "✅ Стили успешно добавлены к статье\n";
        echo "✅ Количество слов: " . $word_count . "\n";
        if ($word_count < 2500) {
            echo "⚠️  Предупреждение: количество слов меньше требуемого минимума\n";
        }
        echo "✅ Статья обновлена: " . get_permalink($post_id) . "\n";
        echo "\n🎉 Статья готова к просмотру с полными стилями!\n";
    }
} else {
    echo "Статья '" . $article_title . "' не найдена.\n";
}
?>

