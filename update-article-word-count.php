<?php
/**
 * Обновление количества слов в статье с правильным подсчетом
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

echo "📝 Обновление количества слов в статье: " . $post->post_title . "\n";

// Функция для правильного подсчета слов в русском тексте
function count_words_russian($text) {
    // Убираем HTML теги
    $text = strip_tags($text);
    
    // Убираем лишние пробелы и переносы строк
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Разбиваем на слова по пробелам
    $words = explode(' ', $text);
    
    // Фильтруем пустые элементы
    $words = array_filter($words, function($word) {
        return !empty(trim($word));
    });
    
    return count($words);
}

// Подсчитываем количество слов
$word_count = count_words_russian($post->post_content);

// Обновляем мета-данные
update_post_meta($post_id, '_bsag_word_count', $word_count);

echo "✅ Количество слов: $word_count\n";

if ($word_count >= 2500) {
    echo "✅ Требование по минимальному количеству слов выполнено (2500+)\n";
} else {
    echo "⚠️  Предупреждение: количество слов меньше требуемого минимума\n";
    echo "📝 Текущее количество слов: $word_count из 2500 требуемых\n";
}

// Получаем URL статьи
$article_url = get_permalink($post_id);
echo "✅ Статья доступна по адресу: $article_url\n";

echo "\n🎉 Обновление завершено!\n";
?>

