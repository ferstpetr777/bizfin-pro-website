<?php
/**
 * Тест исправленной версии РНП API
 * Company Rating Checker - Test Fixed RNP
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем исправленную версию
require_once('rnp-api-fixed.php');

echo "🔍 ТЕСТ ИСПРАВЛЕННОЙ ВЕРСИИ РНП API\n";
echo "====================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр исправленного РНП API
$rnp_api = new RNPApiFixed();

echo "🚀 ТЕСТИРОВАНИЕ ИСПРАВЛЕННОЙ ВЕРСИИ...\n";
echo "======================================\n\n";

// Тестируем несколько запусков для проверки детерминированности
echo "📊 ТЕСТ НА ДЕТЕРМИНИРОВАННОСТЬ (10 запусков):\n";
echo "=============================================\n";

$consistent_results = true;
$first_result = null;

for ($i = 1; $i <= 10; $i++) {
    echo "   Запуск {$i}: ";
    
    try {
        $rnp_data = $rnp_api->get_dishonest_supplier_data($test_inn);
        
        if ($rnp_data && !is_wp_error($rnp_data)) {
            $is_dishonest = $rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ';
            $violations = $rnp_data['violation_count'] ?? 0;
            $reputation = $rnp_data['reputation_impact'] ?? 'Не указана';
            
            echo "Недобросовестный: {$is_dishonest}, Нарушений: {$violations}, Репутация: {$reputation}\n";
            
            // Проверяем консистентность
            if ($i === 1) {
                $first_result = $rnp_data;
            } else {
                if ($first_result['is_dishonest_supplier'] !== $rnp_data['is_dishonest_supplier'] ||
                    $first_result['violation_count'] !== $rnp_data['violation_count'] ||
                    $first_result['reputation_impact'] !== $rnp_data['reputation_impact']) {
                    $consistent_results = false;
                }
            }
        } else {
            echo "Ошибка получения данных\n";
            $consistent_results = false;
        }
    } catch (Exception $e) {
        echo "Исключение: " . $e->getMessage() . "\n";
        $consistent_results = false;
    }
}

echo "\n📈 РЕЗУЛЬТАТ ТЕСТА НА ДЕТЕРМИНИРОВАННОСТЬ:\n";
echo "==========================================\n";
if ($consistent_results) {
    echo "   ✅ РЕЗУЛЬТАТЫ КОНСИСТЕНТНЫ - исправление работает!\n";
} else {
    echo "   ❌ РЕЗУЛЬТАТЫ НЕКОНСИСТЕНТНЫ - требуется дополнительная работа\n";
}

// Детальный анализ последнего результата
echo "\n🔍 ДЕТАЛЬНЫЙ АНАЛИЗ ПОСЛЕДНЕГО РЕЗУЛЬТАТА:\n";
echo "==========================================\n";

try {
    $rnp_data = $rnp_api->get_dishonest_supplier_data($test_inn);
    
    if ($rnp_data && !is_wp_error($rnp_data)) {
        echo "   🏢 ИНН: " . ($rnp_data['inn'] ?? 'Не указан') . "\n";
        echo "   🚫 Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   📈 Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
        echo "   🎯 Репутационное воздействие: " . ($rnp_data['reputation_impact'] ?? 'Не указано') . "\n";
        echo "   📅 Последнее обновление: " . ($rnp_data['last_updated'] ?? 'Не указано') . "\n";
        echo "   🔍 Источник данных: " . ($rnp_data['source'] ?? 'Не указан') . "\n";
        echo "   🧠 Эвристический анализ: " . (isset($rnp_data['heuristic_analysis']) && $rnp_data['heuristic_analysis'] ? 'ДА' : 'НЕТ') . "\n";
        
        if (isset($rnp_data['violation_probability'])) {
            $probability = $rnp_data['violation_probability'];
            $percentage = round($probability * 100, 2);
            echo "   📊 Вероятность нарушений: {$percentage}%\n";
        }
        
        if (isset($rnp_data['rnp_factors']) && !empty($rnp_data['rnp_factors'])) {
            echo "\n   🔍 Факторы анализа:\n";
            foreach ($rnp_data['rnp_factors'] as $factor) {
                echo "      📊 {$factor}\n";
            }
        }
        
        // Рекомендации
        echo "\n   💡 Рекомендации:\n";
        $recommendations = $rnp_api->get_rnp_recommendations($rnp_data);
        foreach ($recommendations as $index => $recommendation) {
            $rec_number = $index + 1;
            echo "      {$rec_number}. {$recommendation}\n";
        }
        
    } else {
        echo "   ❌ Данные не получены\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ТЕСТ ИСПРАВЛЕННОЙ ВЕРСИИ ЗАВЕРШЕН!\n";
echo "=====================================\n";
?>
