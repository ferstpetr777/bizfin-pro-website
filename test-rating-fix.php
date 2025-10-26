<?php
/**
 * Тестовый скрипт для проверки исправления модуля рейтинга
 */

require_once(__DIR__ . '/wp-load.php');

// Проверяем, что плагин активен
if (!class_exists('CompanyRatingChecker')) {
    echo "❌ Плагин Company Rating Checker не активен\n";
    exit;
}

echo "🔧 Тестирование исправлений модуля рейтинга...\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 Тестируем с ИНН: $test_inn\n";

// Проверяем валидацию ИНН
$reflection = new ReflectionClass($plugin);
$validate_method = $reflection->getMethod('validate_inn');
$validate_method->setAccessible(true);

$is_valid = $validate_method->invoke($plugin, $test_inn);
echo $is_valid ? "✅ ИНН валиден\n" : "❌ ИНН невалиден\n";

// Проверяем получение данных компании
$get_company_method = $reflection->getMethod('get_company_data');
$get_company_method->setAccessible(true);

echo "🔍 Получаем данные компании...\n";
$start_time = microtime(true);

try {
    $company_data = $get_company_method->invoke($plugin, $test_inn);
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    if (is_wp_error($company_data)) {
        echo "❌ Ошибка получения данных: " . $company_data->get_error_message() . "\n";
    } else {
        echo "✅ Данные компании получены за {$execution_time} секунд\n";
        echo "📊 Название: " . ($company_data['name']['full_with_opf'] ?? 'Не указано') . "\n";
        echo "📊 Статус: " . ($company_data['state']['status'] ?? 'Не указан') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
}

echo "\n🎯 Тест завершен!\n";
echo "💡 Если тест прошел успешно, модуль должен работать без таймаутов.\n";
?>

