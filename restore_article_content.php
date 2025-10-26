<?php
require_once('wp-config.php');

// ID статьи для восстановления (Автокредит)
$post_id = 2438;

echo "=== ВОССТАНОВЛЕНИЕ КОНТЕНТА СТАТЬИ ID $post_id ===\n\n";

// Получаем текущую статью
$current_post = get_post($post_id);
echo "Текущий заголовок: " . $current_post->post_title . "\n";
echo "Текущая длина контента: " . strlen($current_post->post_content) . " символов\n\n";

// Находим ревизию от 21 октября
$revisions = wp_get_post_revisions($post_id);
$oct21_revision = null;

foreach ($revisions as $revision) {
    $date = new DateTime($revision->post_date);
    if ($date->format('Y-m-d') == '2025-10-21') {
        $oct21_revision = $revision;
        break;
    }
}

if (!$oct21_revision) {
    echo "❌ Ревизия от 21 октября не найдена!\n";
    exit;
}

echo "✅ Найдена ревизия от 21 октября:\n";
echo "ID ревизии: " . $oct21_revision->ID . "\n";
echo "Дата: " . $oct21_revision->post_date . "\n";
echo "Длина контента ревизии: " . strlen($oct21_revision->post_content) . " символов\n\n";

// Анализируем структуру текущей статьи
echo "=== АНАЛИЗ СТРУКТУРЫ ТЕКУЩЕЙ СТАТЬИ ===\n";
$current_content = $current_post->post_content;

// Ищем где заканчивается шаблонная структура и начинается неправильный контент
$intro_section_end = strpos($current_content, '</section>');
if ($intro_section_end !== false) {
    $intro_section_end += strlen('</section>');
    echo "Найдена секция intro-section, заканчивается на позиции: $intro_section_end\n";
    
    // Показываем что идет после intro-section
    $after_intro = substr($current_content, $intro_section_end, 200);
    echo "Контент после intro-section (первые 200 символов):\n";
    echo $after_intro . "\n\n";
}

// Получаем контент из ревизии
$revision_content = $oct21_revision->post_content;
echo "=== КОНТЕНТ ИЗ РЕВИЗИИ (первые 500 символов) ===\n";
echo substr($revision_content, 0, 500) . "\n...\n\n";

// Создаем новый контент: сохраняем структуру до intro-section, добавляем правильный контент
$new_content = '';

if ($intro_section_end !== false) {
    // Берем структуру до конца intro-section
    $structure_part = substr($current_content, 0, $intro_section_end);
    $new_content = $structure_part . "\n\n" . $revision_content;
} else {
    // Если структура не найдена, просто заменяем весь контент
    $new_content = $revision_content;
}

echo "=== ПЛАН ВОССТАНОВЛЕНИЯ ===\n";
echo "1. Сохранить HTML-структуру статьи (до конца intro-section)\n";
echo "2. Добавить правильный контент из ревизии от 21 октября\n";
echo "3. Обновить статью\n\n";

// Показываем что будет добавлено
echo "=== ПРЕДВАРИТЕЛЬНЫЙ ПРОСМОТР ===\n";
echo "Новая длина контента: " . strlen($new_content) . " символов\n";
echo "Первые 300 символов нового контента:\n";
echo substr($new_content, 0, 300) . "\n...\n\n";

// Спрашиваем подтверждение
echo "Готово к восстановлению? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) == 'y' || trim($line) == 'Y') {
    // Обновляем статью
    $update_result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content
    ));
    
    if ($update_result && !is_wp_error($update_result)) {
        echo "✅ Статья успешно восстановлена!\n";
        echo "ID статьи: $post_id\n";
        echo "Новая длина контента: " . strlen($new_content) . " символов\n";
        
        // Очищаем кеш
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        echo "Кеш очищен.\n";
    } else {
        echo "❌ Ошибка при обновлении статьи!\n";
        if (is_wp_error($update_result)) {
            echo "Ошибка: " . $update_result->get_error_message() . "\n";
        }
    }
} else {
    echo "❌ Восстановление отменено.\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";
?>

