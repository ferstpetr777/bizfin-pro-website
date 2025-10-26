<?php
/**
 * Детальный анализ данных РНП
 * Company Rating Checker - Detailed RNP Analysis
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем класс РНП
require_once('rnp-api.php');

echo "🔍 ДЕТАЛЬНЫЙ АНАЛИЗ ДАННЫХ РНП\n";
echo "==============================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр РНП API
$rnp_api = new RNPApi();

echo "🚀 ЗАПУСК ДЕТАЛЬНОГО АНАЛИЗА РНП...\n";
echo "===================================\n\n";

try {
    // Получаем данные от РНП
    $rnp_data = $rnp_api->get_dishonest_supplier_data($test_inn);
    
    if ($rnp_data && !is_wp_error($rnp_data)) {
        echo "✅ ДАННЫЕ РНП ПОЛУЧЕНЫ УСПЕШНО!\n\n";
        
        // Основная информация
        echo "📊 ОСНОВНАЯ ИНФОРМАЦИЯ:\n";
        echo "======================\n";
        echo "   🏢 ИНН: " . ($rnp_data['inn'] ?? 'Не указан') . "\n";
        echo "   🚫 Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   📈 Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
        echo "   🎯 Репутационное воздействие: " . ($rnp_data['reputation_impact'] ?? 'Не указано') . "\n";
        echo "   📅 Последнее обновление: " . ($rnp_data['last_updated'] ?? 'Не указано') . "\n";
        echo "   🔍 Источник данных: " . ($rnp_data['source'] ?? 'Не указан') . "\n";
        echo "   🧠 Эвристический анализ: " . (isset($rnp_data['heuristic_analysis']) && $rnp_data['heuristic_analysis'] ? 'ДА' : 'НЕТ') . "\n\n";
        
        // Детальная информация о нарушениях
        if (isset($rnp_data['violations']) && !empty($rnp_data['violations'])) {
            echo "🚨 ДЕТАЛЬНАЯ ИНФОРМАЦИЯ О НАРУШЕНИЯХ:\n";
            echo "====================================\n";
            
            foreach ($rnp_data['violations'] as $index => $violation) {
                $violation_number = $index + 1;
                echo "   📋 Нарушение #{$violation_number}:\n";
                echo "      🆔 ID нарушения: " . ($violation['violation_id'] ?? 'Не указан') . "\n";
                echo "      📝 Тип нарушения: " . ($violation['type'] ?? 'Не указан') . "\n";
                echo "      📄 Описание: " . ($violation['description'] ?? 'Не указано') . "\n";
                echo "      ⚠️ Тяжесть: " . ($violation['severity'] ?? 'Не указана') . "\n";
                echo "      💰 Размер штрафа: " . number_format($violation['penalty_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
                echo "      📅 Дата нарушения: " . ($violation['violation_date'] ?? 'Не указана') . "\n";
                echo "      📄 Номер контракта: " . ($violation['contract_number'] ?? 'Не указан') . "\n";
                echo "      🏢 Заказчик: " . ($violation['customer'] ?? 'Не указан') . "\n";
                echo "      📊 Статус: " . ($violation['status'] ?? 'Не указан') . "\n";
                echo "\n";
            }
        } else {
            echo "✅ НАРУШЕНИЙ НЕ ОБНАРУЖЕНО\n\n";
        }
        
        // Факторы анализа (если есть)
        if (isset($rnp_data['rnp_factors']) && !empty($rnp_data['rnp_factors'])) {
            echo "🔍 ФАКТОРЫ АНАЛИЗА РНП:\n";
            echo "=======================\n";
            foreach ($rnp_data['rnp_factors'] as $factor) {
                echo "   📊 {$factor}\n";
            }
            echo "\n";
        }
        
        // Вероятность нарушений
        if (isset($rnp_data['violation_probability'])) {
            echo "📈 ВЕРОЯТНОСТЬ НАРУШЕНИЙ:\n";
            echo "========================\n";
            $probability = $rnp_data['violation_probability'];
            $percentage = round($probability * 100, 2);
            echo "   🎯 Вероятность: {$percentage}%\n";
            
            if ($probability > 0.7) {
                echo "   🚨 ВЫСОКИЙ РИСК НАРУШЕНИЙ\n";
            } elseif ($probability > 0.4) {
                echo "   ⚠️ СРЕДНИЙ РИСК НАРУШЕНИЙ\n";
            } else {
                echo "   ✅ НИЗКИЙ РИСК НАРУШЕНИЙ\n";
            }
            echo "\n";
        }
        
        // Проверенные источники
        if (isset($rnp_data['sources_checked']) && !empty($rnp_data['sources_checked'])) {
            echo "🌐 ПРОВЕРЕННЫЕ ИСТОЧНИКИ РНП:\n";
            echo "=============================\n";
            foreach ($rnp_data['sources_checked'] as $source_key => $source_info) {
                $status = $source_info['available'] ? '✅ Доступен' : '❌ Недоступен';
                echo "   {$status} {$source_info['name']}\n";
                echo "      🔗 URL: {$source_info['url']}\n";
            }
            echo "\n";
        }
        
        // Рекомендации
        echo "💡 РЕКОМЕНДАЦИИ РНП:\n";
        echo "===================\n";
        try {
            $recommendations = $rnp_api->get_rnp_recommendations($rnp_data);
            foreach ($recommendations as $index => $recommendation) {
                $rec_number = $index + 1;
                echo "   {$rec_number}. {$recommendation}\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Ошибка получения рекомендаций: " . $e->getMessage() . "\n";
        }
        echo "\n";
        
        // Дополнительная статистика
        echo "📊 ДОПОЛНИТЕЛЬНАЯ СТАТИСТИКА:\n";
        echo "============================\n";
        
        if (isset($rnp_data['violations']) && !empty($rnp_data['violations'])) {
            $total_penalty = 0;
            $high_severity_count = 0;
            $active_violations = 0;
            
            foreach ($rnp_data['violations'] as $violation) {
                $total_penalty += $violation['penalty_amount'] ?? 0;
                if (in_array($violation['severity'] ?? '', ['high', 'very_high'])) {
                    $high_severity_count++;
                }
                if (($violation['status'] ?? '') === 'active') {
                    $active_violations++;
                }
            }
            
            echo "   💰 Общая сумма штрафов: " . number_format($total_penalty, 0, ',', ' ') . " руб.\n";
            echo "   🚨 Серьезных нарушений: {$high_severity_count}\n";
            echo "   ⚡ Активных нарушений: {$active_violations}\n";
        }
        
        echo "\n";
        
    } else {
        echo "❌ ОШИБКА ПОЛУЧЕНИЯ ДАННЫХ РНП\n";
        if (is_wp_error($rnp_data)) {
            echo "   Код ошибки: " . $rnp_data->get_error_code() . "\n";
            echo "   Сообщение: " . $rnp_data->get_error_message() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
}

echo "\n⏰ Время завершения анализа: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ДЕТАЛЬНЫЙ АНАЛИЗ РНП ЗАВЕРШЕН!\n";
echo "=================================\n";

// Дополнительно выведем сырые данные для отладки
echo "\n🔧 СЫРЫЕ ДАННЫЕ РНП (для отладки):\n";
echo "==================================\n";
if (isset($rnp_data)) {
    echo json_encode($rnp_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "Данные не получены";
}
echo "\n";
?>
