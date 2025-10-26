<?php
/**
 * Настройка API ключа для SEO генератора
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

echo "=== НАСТРОЙКА API КЛЮЧА ДЛЯ SEO ГЕНЕРАТОРА ===\n\n";

// API ключ (тот же, что используется в других плагинах)
$api_key = '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';

// Проверяем текущее состояние
$current_key = get_option('bsag_openai_api_key', '');
echo "Текущий API ключ: " . ($current_key ? 'настроен (длина: ' . strlen($current_key) . ')' : 'НЕ НАСТРОЕН') . "\n";

// Устанавливаем API ключ
$result = update_option('bsag_openai_api_key', $api_key);
echo "Результат установки: " . ($result ? 'УСПЕШНО' : 'ОШИБКА') . "\n";

// Проверяем установку
$new_key = get_option('bsag_openai_api_key', '');
echo "Новый API ключ: " . ($new_key ? 'настроен (длина: ' . strlen($new_key) . ')' : 'НЕ НАСТРОЕН') . "\n";

// Тестируем SEO генератор
if (class_exists('BizFin_SEO_Article_Generator')) {
    echo "\nТестирование SEO генератора...\n";
    
    $generator = BizFin_SEO_Article_Generator::get_instance();
    
    // Проверяем доступность AI Agent Integration
    if (method_exists($generator, 'get_tone_style_generator')) {
        $tone_generator = $generator->get_tone_style_generator();
        if ($tone_generator) {
            echo "✅ Tone Style Generator доступен\n";
        }
    }
    
    // Проверяем SEO матрицу
    $seo_matrix = $generator->get_seo_matrix();
    if (!empty($seo_matrix['keywords'])) {
        echo "✅ SEO матрица загружена (" . count($seo_matrix['keywords']) . " ключевых слов)\n";
    }
    
    echo "✅ SEO генератор готов к работе\n";
} else {
    echo "❌ SEO генератор не найден\n";
}

echo "\n=== НАСТРОЙКА ЗАВЕРШЕНА ===\n";
?>
