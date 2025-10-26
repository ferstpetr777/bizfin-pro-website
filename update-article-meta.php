<?php
/**
 * Обновление мета-данных статьи с правильным подсчетом слов
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

$post_id = 2872; // ID созданной статьи
$post = get_post($post_id);

if (!$post) {
    echo "❌ Статья не найдена\n";
    exit;
}

// Подсчитываем слова правильно
$content = $post->post_content;
$clean_content = strip_tags($content);
$clean_content = preg_replace('/<!--.*?-->/s', '', $clean_content);
$clean_content = preg_replace('/<script.*?<\/script>/s', '', $clean_content);
$clean_content = preg_replace('/<style.*?<\/style>/s', '', $clean_content);
$clean_content = html_entity_decode($clean_content, ENT_QUOTES, 'UTF-8');
$clean_content = preg_replace('/\s+/', ' ', $clean_content);
$clean_content = trim($clean_content);

// Подсчитываем слова с учетом кириллицы
$words = preg_split('/\s+/u', $clean_content, -1, PREG_SPLIT_NO_EMPTY);
$word_count = count($words);

echo "📊 Подсчет слов:\n";
echo "Очищенный контент: " . strlen($clean_content) . " символов\n";
echo "Количество слов: {$word_count}\n";

// Обновляем мета-данные
update_post_meta($post_id, '_bsag_word_count', $word_count);
update_post_meta($post_id, '_bsag_min_words', 2500);
update_post_meta($post_id, '_bsag_length_validation', [
    'word_count' => $word_count,
    'min_required' => 2500,
    'meets_requirement' => $word_count >= 2500,
    'deficit' => max(0, 2500 - $word_count),
    'percentage' => round(($word_count / 2500) * 100, 1)
]);

// Проверяем выполнение критериев матрицы
echo "\n🎯 Проверка критериев матрицы:\n";

// 1. БЕЗУСЛОВНОЕ ПРАВИЛО: Простое определение
$has_simple_definition = strpos($content, '<strong>Стоимость банковской гарантии</strong> — это премия') !== false;
echo "✅ Простое определение: " . ($has_simple_definition ? "ДА" : "НЕТ") . "\n";

// 2. БЕЗУСЛОВНОЕ ПРАВИЛО: Симпатичный пример
$has_sympathetic_example = strpos($content, 'ООО "СтройИнвест"') !== false;
echo "✅ Симпатичный пример: " . ($has_sympathetic_example ? "ДА" : "НЕТ") . "\n";

// 3. БЕЗУСЛОВНОЕ ПРАВИЛО: Кликабельное оглавление
$has_toc = strpos($content, '<nav class="toc">') !== false;
echo "✅ Кликабельное оглавление: " . ($has_toc ? "ДА" : "НЕТ") . "\n";

// 4. БЕЗУСЛОВНОЕ ПРАВИЛО: Изображение после оглавления
$has_image = strpos($content, 'article-image-container') !== false;
echo "✅ Изображение после оглавления: " . ($has_image ? "ДА" : "НЕТ") . "\n";

// 5. Внутренние ссылки (3-7)
$internal_links = substr_count($content, 'href="/');
echo "✅ Внутренние ссылки: {$internal_links} (требуется 3-7)\n";

// 6. FAQ секция
$has_faq = strpos($content, 'Частые вопросы') !== false;
echo "✅ FAQ секция: " . ($has_faq ? "ДА" : "НЕТ") . "\n";

// 7. CTA блоки
$has_cta = strpos($content, 'cta-block') !== false;
echo "✅ CTA блоки: " . ($has_cta ? "ДА" : "НЕТ") . "\n";

// 8. Калькулятор
$has_calculator = strpos($content, 'calculator-container') !== false;
echo "✅ Калькулятор: " . ($has_calculator ? "ДА" : "НЕТ") . "\n";

// 9. Адаптивный дизайн
$has_responsive = strpos($content, '@media (max-width: 768px)') !== false;
echo "✅ Адаптивный дизайн: " . ($has_responsive ? "ДА" : "NЕТ") . "\n";

// 10. Структура H2 (8-12)
$h2_count = substr_count($content, '<h2>');
echo "✅ H2 заголовки: {$h2_count} (требуется 8-12)\n";

// 11. Длина статьи
$meets_length = $word_count >= 2500;
echo "✅ Длина статьи: {$word_count} слов (требуется ≥2500): " . ($meets_length ? "ДА" : "НЕТ") . "\n";

// 12. SEO оптимизация
$has_focus_keyword = strpos($content, 'стоимость банковской гарантии') !== false;
echo "✅ Фокусное ключевое слово: " . ($has_focus_keyword ? "ДА" : "НЕТ") . "\n";

echo "\n📋 Итоговая оценка:\n";
$total_criteria = 12;
$passed_criteria = 0;

if ($has_simple_definition) $passed_criteria++;
if ($has_sympathetic_example) $passed_criteria++;
if ($has_toc) $passed_criteria++;
if ($has_image) $passed_criteria++;
if ($internal_links >= 3 && $internal_links <= 7) $passed_criteria++;
if ($has_faq) $passed_criteria++;
if ($has_cta) $passed_criteria++;
if ($has_calculator) $passed_criteria++;
if ($has_responsive) $passed_criteria++;
if ($h2_count >= 8 && $h2_count <= 12) $passed_criteria++;
if ($meets_length) $passed_criteria++;
if ($has_focus_keyword) $passed_criteria++;

$score = round(($passed_criteria / $total_criteria) * 100);
echo "🎯 Выполнено критериев: {$passed_criteria}/{$total_criteria} ({$score}%)\n";

if ($score >= 90) {
    echo "🏆 ОТЛИЧНО! Статья полностью соответствует критериям матрицы плагина\n";
} elseif ($score >= 75) {
    echo "✅ ХОРОШО! Статья в основном соответствует критериям\n";
} else {
    echo "⚠️ ТРЕБУЕТСЯ ДОРАБОТКА! Не все критерии выполнены\n";
}

echo "\n🔗 Ссылка на статью: " . get_permalink($post_id) . "\n";
echo "📊 Количество слов: {$word_count}\n";
echo "📅 Дата создания: " . $post->post_date . "\n";
?>
