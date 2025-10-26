<?php
/**
 * Сравнительный тест РНП данных
 * Company Rating Checker - RNP Comparison Test
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем все классы
require_once('company-rating-checker.php');
require_once('rnp-api.php');

echo "🔍 СРАВНИТЕЛЬНЫЙ ТЕСТ РНП ДАННЫХ\n";
echo "================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

echo "🚀 ТЕСТИРОВАНИЕ РАЗНЫХ МЕТОДОВ ПОЛУЧЕНИЯ РНП ДАННЫХ...\n";
echo "====================================================\n\n";

// Метод 1: Прямое обращение к РНП API
echo "1️⃣ ПРЯМОЕ ОБРАЩЕНИЕ К РНП API:\n";
echo "-------------------------------\n";
try {
    $rnp_api = new RNPApi();
    $direct_rnp_data = $rnp_api->get_dishonest_supplier_data($test_inn);
    
    if ($direct_rnp_data && !is_wp_error($direct_rnp_data)) {
        echo "   ✅ Данные получены\n";
        echo "   🚫 Недобросовестный поставщик: " . ($direct_rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   📈 Количество нарушений: " . ($direct_rnp_data['violation_count'] ?? 0) . "\n";
        echo "   🎯 Репутация: " . ($direct_rnp_data['reputation_impact'] ?? 'Не указана') . "\n";
        echo "   🔍 Источник: " . ($direct_rnp_data['source'] ?? 'Не указан') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Метод 2: Через основной плагин
echo "2️⃣ ЧЕРЕЗ ОСНОВНОЙ ПЛАГИН:\n";
echo "-------------------------\n";
try {
    $plugin = new CompanyRatingChecker();
    $reflection = new ReflectionClass($plugin);
    
    $get_rnp_data_method = $reflection->getMethod('get_rnp_data');
    $get_rnp_data_method->setAccessible(true);
    $plugin_rnp_data = $get_rnp_data_method->invoke($plugin, $test_inn);
    
    if ($plugin_rnp_data && !is_wp_error($plugin_rnp_data)) {
        echo "   ✅ Данные получены\n";
        echo "   🚫 Недобросовестный поставщик: " . ($plugin_rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   📈 Количество нарушений: " . ($plugin_rnp_data['violation_count'] ?? 0) . "\n";
        echo "   🎯 Репутация: " . ($plugin_rnp_data['reputation_impact'] ?? 'Не указана') . "\n";
        echo "   🔍 Источник: " . ($plugin_rnp_data['source'] ?? 'Не указан') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Метод 3: Через полный анализ компании
echo "3️⃣ ЧЕРЕЗ ПОЛНЫЙ АНАЛИЗ КОМПАНИИ:\n";
echo "--------------------------------\n";
try {
    $plugin = new CompanyRatingChecker();
    $reflection = new ReflectionClass($plugin);
    
    // Получаем все данные компании
    $company_data = array();
    
    // Получаем базовые данные
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data['basic'] = $get_company_data_method->invoke($plugin, $test_inn);
    
    // Получаем РНП данные
    $get_rnp_data_method = $reflection->getMethod('get_rnp_data');
    $get_rnp_data_method->setAccessible(true);
    $company_data['rnp'] = $get_rnp_data_method->invoke($plugin, $test_inn);
    
    // Рассчитываем рейтинг
    $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
    $calculate_rating_method->setAccessible(true);
    $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
    
    if ($rating_result && isset($rating_result['factors']['rnp'])) {
        $rnp_factor = $rating_result['factors']['rnp'];
        echo "   ✅ Данные получены через расчет рейтинга\n";
        echo "   📊 Фактор РНП: {$rnp_factor['score']}/{$rnp_factor['max_score']}\n";
        echo "   📝 Описание: {$rnp_factor['description']}\n";
        
        // Проверяем исходные данные
        if (isset($company_data['rnp'])) {
            $rnp_data = $company_data['rnp'];
            echo "   🚫 Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ') . "\n";
            echo "   📈 Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
            echo "   🎯 Репутация: " . ($rnp_data['reputation_impact'] ?? 'Не указана') . "\n";
        }
    } else {
        echo "   ❌ Данные не получены через расчет рейтинга\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Проверим настройки плагина
echo "4️⃣ ПРОВЕРКА НАСТРОЕК ПЛАГИНА:\n";
echo "-----------------------------\n";
$rnp_enabled = get_option('crc_rnp_enabled', 1);
echo "   🔧 РНП включен: " . ($rnp_enabled ? 'ДА' : 'НЕТ') . "\n";

// Проверим несколько запусков для выявления случайности
echo "\n5️⃣ ТЕСТ НА СЛУЧАЙНОСТЬ (5 запусков):\n";
echo "------------------------------------\n";
for ($i = 1; $i <= 5; $i++) {
    echo "   Запуск {$i}: ";
    try {
        $rnp_api = new RNPApi();
        $test_data = $rnp_api->get_dishonest_supplier_data($test_inn);
        
        if ($test_data && !is_wp_error($test_data)) {
            $is_dishonest = $test_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ';
            $violations = $test_data['violation_count'] ?? 0;
            echo "Недобросовестный: {$is_dishonest}, Нарушений: {$violations}\n";
        } else {
            echo "Ошибка получения данных\n";
        }
    } catch (Exception $e) {
        echo "Исключение: " . $e->getMessage() . "\n";
    }
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 СРАВНИТЕЛЬНЫЙ ТЕСТ РНП ЗАВЕРШЕН!\n";
echo "===================================\n";
?>
