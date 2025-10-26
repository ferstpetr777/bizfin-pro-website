<?php
/**
 * Получение развернутых данных ФНС и ФССП
 * Company Rating Checker - Get Detailed FNS and FSSP Data
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ПОЛУЧЕНИЕ РАЗВЕРНУТЫХ ДАННЫХ ФНС И ФССП\n";
echo "==========================================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время запроса: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПОЛУЧЕНИЕ ДАННЫХ ФНС...\n";
echo "==========================\n\n";

// 1. Получаем данные ФНС
echo "1️⃣ ДАННЫЕ ФНС:\n";
echo "---------------\n";
try {
    $get_fns_data_method = $reflection->getMethod('get_fns_data');
    $get_fns_data_method->setAccessible(true);
    $fns_data = $get_fns_data_method->invoke($plugin, $test_inn);
    
    if ($fns_data && !is_wp_error($fns_data)) {
        echo "   ✅ Данные ФНС успешно получены:\n\n";
        
        // Основные финансовые показатели
        echo "   📊 ОСНОВНЫЕ ФИНАНСОВЫЕ ПОКАЗАТЕЛИ:\n";
        echo "   ===================================\n";
        echo "   💰 Выручка: " . number_format($fns_data['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   📈 Прибыль: " . number_format($fns_data['profit'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   🏦 Задолженность: " . number_format($fns_data['debt'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   📊 Рентабельность: " . number_format($fns_data['profitability'] ?? 0, 2, ',', ' ') . "%\n";
        echo "   📉 Коэффициент задолженности: " . number_format($fns_data['debt_ratio'] ?? 0, 2, ',', ' ') . "%\n";
        echo "   ⚠️ Риск банкротства: " . ($fns_data['bankruptcy_risk'] ?? 'Не указан') . "\n";
        echo "   🏛️ Налоговая задолженность: " . number_format($fns_data['tax_debt'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   🎯 Риск-скор: " . ($fns_data['risk_score'] ?? 'Не указан') . "/100\n\n";
        
        // Факторы анализа
        if (isset($fns_data['financial_factors']) && is_array($fns_data['financial_factors'])) {
            echo "   🔍 ФАКТОРЫ ФИНАНСОВОГО АНАЛИЗА:\n";
            echo "   ================================\n";
            foreach ($fns_data['financial_factors'] as $i => $factor) {
                echo "   " . ($i + 1) . ". {$factor}\n";
            }
            echo "\n";
        }
        
        // Метаданные
        echo "   📋 МЕТАДАННЫЕ:\n";
        echo "   ==============\n";
        echo "   🔍 Источник: " . ($fns_data['source'] ?? 'Не указан') . "\n";
        echo "   📅 Последнее обновление: " . ($fns_data['last_updated'] ?? 'Не указано') . "\n";
        echo "   🤖 API использован: " . (isset($fns_data['api_used']) && $fns_data['api_used'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   🧠 Эвристический анализ: " . (isset($fns_data['heuristic_analysis']) && $fns_data['heuristic_analysis'] ? 'ДА' : 'НЕТ') . "\n\n";
        
    } else {
        echo "   ❌ Данные ФНС не получены\n";
        if (is_wp_error($fns_data)) {
            echo "   Ошибка: " . $fns_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка получения данных ФНС: " . $e->getMessage() . "\n";
}

echo "\n🚀 ПОЛУЧЕНИЕ ДАННЫХ ФССП...\n";
echo "============================\n\n";

// 2. Получаем данные ФССП
echo "2️⃣ ДАННЫЕ ФССП:\n";
echo "---------------\n";
try {
    $get_fssp_data_method = $reflection->getMethod('get_fssp_data');
    $get_fssp_data_method->setAccessible(true);
    $fssp_data = $get_fssp_data_method->invoke($plugin, $test_inn);
    
    if ($fssp_data && !is_wp_error($fssp_data)) {
        echo "   ✅ Данные ФССП успешно получены:\n\n";
        
        // Основная информация
        echo "   📊 ОСНОВНАЯ ИНФОРМАЦИЯ:\n";
        echo "   ======================\n";
        echo "   🏛️ ИНН: " . ($fssp_data['inn'] ?? 'Не указан') . "\n";
        echo "   ⚖️ Есть исполнительные производства: " . (isset($fssp_data['has_enforcement_proceedings']) && $fssp_data['has_enforcement_proceedings'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   📋 Количество производств: " . ($fssp_data['proceedings_count'] ?? 0) . "\n";
        echo "   💰 Общая сумма задолженности: " . number_format($fssp_data['total_debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   ⚠️ Уровень финансового риска: " . ($fssp_data['financial_risk_level'] ?? 'Не указан') . "\n";
        echo "   📊 Вероятность производств: " . number_format(($fssp_data['proceeding_probability'] ?? 0) * 100, 1, ',', ' ') . "%\n\n";
        
        // Детали производств
        if (isset($fssp_data['proceedings']) && is_array($fssp_data['proceedings']) && count($fssp_data['proceedings']) > 0) {
            echo "   📋 ДЕТАЛИ ИСПОЛНИТЕЛЬНЫХ ПРОИЗВОДСТВ:\n";
            echo "   =====================================\n";
            foreach ($fssp_data['proceedings'] as $i => $proceeding) {
                echo "   " . ($i + 1) . ". Производство №" . ($proceeding['proceeding_id'] ?? 'Не указан') . "\n";
                echo "      📝 Тип: " . ($proceeding['type'] ?? 'Не указан') . "\n";
                echo "      📄 Описание: " . ($proceeding['description'] ?? 'Не указано') . "\n";
                echo "      💰 Сумма долга: " . number_format($proceeding['debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
                echo "      🎯 Приоритет: " . ($proceeding['priority'] ?? 'Не указан') . "\n";
                echo "      📅 Дата возбуждения: " . ($proceeding['initiation_date'] ?? 'Не указана') . "\n";
                echo "      👮 Судебный пристав: " . ($proceeding['bailiff'] ?? 'Не указан') . "\n";
                echo "      🏛️ Взыскатель: " . ($proceeding['creditor'] ?? 'Не указан') . "\n";
                echo "      📊 Статус: " . ($proceeding['status'] ?? 'Не указан') . "\n";
                echo "      📈 Процент исполнения: " . ($proceeding['execution_percentage'] ?? 0) . "%\n\n";
            }
        } else {
            echo "   📋 ИСПОЛНИТЕЛЬНЫЕ ПРОИЗВОДСТВА:\n";
            echo "   ================================\n";
            echo "   ✅ Исполнительных производств не найдено\n\n";
        }
        
        // Факторы анализа
        if (isset($fssp_data['fssp_factors']) && is_array($fssp_data['fssp_factors'])) {
            echo "   🔍 ФАКТОРЫ АНАЛИЗА ФССП:\n";
            echo "   ========================\n";
            foreach ($fssp_data['fssp_factors'] as $i => $factor) {
                echo "   " . ($i + 1) . ". {$factor}\n";
            }
            echo "\n";
        }
        
        // Проверенные источники
        if (isset($fssp_data['sources_checked']) && is_array($fssp_data['sources_checked'])) {
            echo "   🔍 ПРОВЕРЕННЫЕ ИСТОЧНИКИ:\n";
            echo "   ==========================\n";
            foreach ($fssp_data['sources_checked'] as $source_name => $source_info) {
                $status = $source_info['available'] ? '✅ Доступен' : '❌ Недоступен';
                echo "   - {$source_name}: {$status}\n";
                echo "     URL: {$source_info['url']}\n";
                echo "     Название: {$source_info['name']}\n\n";
            }
        }
        
        // Метаданные
        echo "   📋 МЕТАДАННЫЕ:\n";
        echo "   ==============\n";
        echo "   🔍 Источник: " . ($fssp_data['source'] ?? 'Не указан') . "\n";
        echo "   📅 Последнее обновление: " . ($fssp_data['last_updated'] ?? 'Не указано') . "\n";
        echo "   🤖 API использован: " . (isset($fssp_data['api_used']) && $fssp_data['api_used'] ? 'ДА' : 'НЕТ') . "\n";
        echo "   🧠 Эвристический анализ: " . (isset($fssp_data['heuristic_analysis']) && $fssp_data['heuristic_analysis'] ? 'ДА' : 'НЕТ') . "\n\n";
        
    } else {
        echo "   ❌ Данные ФССП не получены\n";
        if (is_wp_error($fssp_data)) {
            echo "   Ошибка: " . $fssp_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка получения данных ФССП: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения запроса: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ПОЛУЧЕНИЕ ДАННЫХ ФНС И ФССП ЗАВЕРШЕНО!\n";
echo "==========================================\n";
?>
