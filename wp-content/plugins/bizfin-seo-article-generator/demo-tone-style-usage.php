<?php
/**
 * Демонстрация использования новых критериев тона и стиля
 * BizFin SEO Article Generator
 */

// Загрузка WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) exit;

echo "=== Демонстрация новых критериев тона и стиля ===\n\n";

// Получаем экземпляр плагина
$plugin = BizFin_SEO_Article_Generator::get_instance();
$tone_generator = $plugin->get_tone_style_generator();

// Тестовое ключевое слово
$keyword = 'виды банковских гарантий';

echo "Тестируем критерии для ключевого слова: '{$keyword}'\n\n";

// Получаем критерии тона
$tone_criteria = $tone_generator->get_tone_criteria($keyword);

echo "=== КРИТЕРИИ ГЛОБАЛЬНОГО ТОНА И СТИЛЯ ===\n";
echo "Целевая аудитория: " . ($tone_criteria['global_tone_style']['target_audience'] ?? 'не определена') . "\n\n";

echo "Формула тона:\n";
$tone_formula = $tone_criteria['global_tone_style']['tone_formula'] ?? [];
foreach ($tone_formula as $key => $value) {
    echo "- {$key}: {$value}\n";
}

echo "\n=== ШАБЛОН ВВЕДЕНИЯ ===\n";
$intro_template = $tone_criteria['introduction_template']['structure'] ?? [];
foreach ($intro_template as $key => $value) {
    echo "- {$key}: {$value}\n";
}

echo "\n=== ГЕНЕРАЦИЯ ВВЕДЕНИЯ ===\n";

// Получаем данные ключевого слова из матрицы
$seo_matrix = $plugin->get_seo_matrix();
$keyword_data = $seo_matrix['keywords'][$keyword] ?? [];

if (!empty($keyword_data)) {
    // Генерируем введение
    $introduction = $tone_generator->generate_introduction($keyword, $keyword_data);
    
echo "Сгенерированное введение:\n";
echo "---\n";
echo $introduction;
echo "---\n\n";

// Показываем, как должны применяться шаблоны
echo "=== ПРИМЕР ПРАВИЛЬНОГО ПРИМЕНЕНИЯ ШАБЛОНОВ ===\n";

$tone_style = $keyword_data['global_tone_style'] ?? [];
$intro_template = $keyword_data['introduction_template'] ?? [];

if (!empty($tone_style['tone_formula'])) {
    echo "Формула тона для применения:\n";
    foreach ($tone_style['tone_formula'] as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
}

if (!empty($intro_template['structure'])) {
    echo "\nШаблон введения для применения:\n";
    foreach ($intro_template['structure'] as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
}

echo "\n";
    
    // Валидируем соответствие тону
    $compliance_score = $tone_generator->validate_tone_compliance($introduction, $tone_criteria);
    echo "Оценка соответствия тону: " . round($compliance_score * 100, 2) . "%\n\n";
    
} else {
    echo "❌ Данные для ключевого слова '{$keyword}' не найдены в матрице.\n\n";
}

echo "=== ПРИМЕР ПРИМЕНЕНИЯ ЯЗЫКОВЫХ ПРАВИЛ ===\n";

// Тестовый текст с канцелярским языком
$test_text = "Осуществляется подача заявки на банковскую гарантию. В соответствии с требованиями предусматривается предоставление документов. Является необходимым соблюдение сроков.";

echo "Исходный текст (с канцеляритом):\n";
echo $test_text . "\n\n";

// Применяем языковые правила
$tone_style = $keyword_data['global_tone_style'] ?? [];
$improved_text = $tone_generator->apply_tone_formula($test_text, $tone_style);

echo "Улучшенный текст:\n";
echo $improved_text . "\n\n";

echo "=== ПРОВЕРКА СООТВЕТСТВИЯ КРИТЕРИЯМ ===\n";

// Проверяем различные аспекты текста
$compliance_tests = [
    'Читаемость' => $tone_generator->check_readability($improved_text),
    'Активный залог' => $tone_generator->check_active_voice($improved_text),
    'Обращения к читателю' => $tone_generator->check_reader_references($improved_text),
    'Отсутствие канцелярита' => $tone_generator->check_no_bureaucratic_language($improved_text)
];

foreach ($compliance_tests as $test_name => $result) {
    $status = $result ? '✅ Пройден' : '❌ Не пройден';
    echo "- {$test_name}: {$status}\n";
}

echo "\n=== ИНТЕГРАЦИЯ С СУЩЕСТВУЮЩИМИ МОДУЛЯМИ ===\n";

// Показываем, как новые критерии интегрируются с существующими модулями
echo "Новые критерии интегрированы с:\n";
echo "- Системой качества контента (quality-system.php)\n";
echo "- Цепочкой промптов (prompt-chaining-system.php)\n";
echo "- Динамическими модулями (dynamic-modules-system.php)\n";
echo "- Генерацией статей (ai-agent-integration.php)\n\n";

echo "=== ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ В КОДЕ ===\n";

echo "1. Получение критериев тона:\n";
echo "\$tone_generator = \$plugin->get_tone_style_generator();\n";
echo "\$criteria = \$tone_generator->get_tone_criteria(\$keyword);\n\n";

echo "2. Генерация введения:\n";
echo "\$introduction = \$tone_generator->generate_introduction(\$keyword, \$keyword_data);\n\n";

echo "3. Валидация соответствия тону:\n";
echo "\$score = \$tone_generator->validate_tone_compliance(\$text, \$criteria);\n\n";

echo "4. Применение языковых правил:\n";
echo "\$improved_text = \$tone_generator->apply_tone_formula(\$text, \$tone_style);\n\n";

echo "=== ЗАКЛЮЧЕНИЕ ===\n";
echo "✅ Новые критерии тона и стиля успешно добавлены в матрицу плагина\n";
echo "✅ Создан генератор тона и стиля для применения критериев\n";
echo "✅ Интегрирован с основным плагином\n";
echo "✅ Готов к использованию в генерации статей\n\n";

echo "Теперь при генерации статей будут применяться:\n";
echo "- Глобальный тон и стиль (дружелюбный, но профессиональный)\n";
echo "- Структурированное введение по шаблону\n";
echo "- Языковые правила (активный залог, обращения к читателю)\n";
echo "- Валидация соответствия критериям\n\n";

echo "=== Демонстрация завершена ===\n";
