<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    echo "WordPress не загружен.";
    exit;
}

wp_set_current_user(1);

$article_title = "Возврат аванса по банковской гарантии: процедура взыскания 2025";
$article_slug = sanitize_title($article_title);
$article_content = file_get_contents(__DIR__ . '/generated-article-advance-return-procedure.html');

// Удаляем все <style> теги из контента
$article_content_cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $article_content);

// Функция для корректного подсчета русских слов
function count_russian_words($text) {
    $text = strip_tags($text); // Удаляем HTML теги
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text); // Удаляем все, кроме букв, цифр и пробелов
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY); // Разбиваем по пробелам
    return count($words);
}

$word_count = count_russian_words($article_content_cleaned);

$post_id = wp_insert_post(array(
    'post_title'   => $article_title,
    'post_content' => $article_content_cleaned,
    'post_status'  => 'publish',
    'post_author'  => 1,
    'post_type'    => 'post',
    'post_name'    => $article_slug,
));

if (is_wp_error($post_id)) {
    echo "Ошибка при публикации статьи: " . $post_id->get_error_message() . "\n";
} else {
    echo "✅ Статья успешно создана! ID: " . $post_id . "\n";
    echo "✅ Статья опубликована: " . get_permalink($post_id) . "\n";
    echo "✅ Количество слов: " . $word_count . "\n";
    if ($word_count < 2500) {
        echo "⚠️  Предупреждение: количество слов меньше требуемого минимума\n";
    }
    echo "\n🎉 Статья успешно опубликована и готова к просмотру!\n";
}
?>

