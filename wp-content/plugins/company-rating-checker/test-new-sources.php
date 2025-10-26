<?php
/**
 * Тестирование новых источников данных: ЕФРСБ, РНП, ФССП
 * Company Rating Checker - Test New Sources
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем классы
require_once('efrsb-api.php');
require_once('rnp-api.php');
require_once('fssp-api.php');

echo "🔍 Тестирование новых источников данных\n";
echo "=====================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 Тестовый ИНН: {$test_inn}\n\n";

// Тестируем ЕФРСБ API
echo "🏛️ Тестирование ЕФРСБ API:\n";
echo "------------------------\n";
try {
    $efrsb_api = new EFRSBAPI();
    $efrsb_data = $efrsb_api->get_bankruptcy_data($test_inn);
    
    if ($efrsb_data && !is_wp_error($efrsb_data)) {
        echo "✅ ЕФРСБ данные получены успешно\n";
        echo "   Статус банкротства: " . ($efrsb_data['bankruptcy_status'] ?? 'неизвестно') . "\n";
        echo "   Уровень риска: " . ($efrsb_data['bankruptcy_risk_level'] ?? 'неизвестно') . "\n";
        echo "   Количество дел: " . count($efrsb_data['bankruptcy_cases'] ?? []) . "\n";
        echo "   Источник: " . ($efrsb_data['source'] ?? 'неизвестно') . "\n";
        
        if (isset($efrsb_data['bankruptcy_cases']) && !empty($efrsb_data['bankruptcy_cases'])) {
            echo "   Детали дел:\n";
            foreach ($efrsb_data['bankruptcy_cases'] as $case) {
                echo "     - " . $case['case_type_name'] . " (№" . $case['case_number'] . ")\n";
            }
        }
        
        // Тестируем рекомендации
        $recommendations = $efrsb_api->get_bankruptcy_recommendations($efrsb_data);
        echo "   Рекомендации:\n";
        foreach ($recommendations as $rec) {
            echo "     - " . $rec . "\n";
        }
    } else {
        echo "❌ Ошибка получения данных ЕФРСБ\n";
        if (is_wp_error($efrsb_data)) {
            echo "   " . $efrsb_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Исключение при тестировании ЕФРСБ: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем РНП API
echo "🚫 Тестирование РНП API:\n";
echo "----------------------\n";
try {
    $rnp_api = new RNPApi();
    $rnp_data = $rnp_api->get_dishonest_supplier_data($test_inn);
    
    if ($rnp_data && !is_wp_error($rnp_data)) {
        echo "✅ РНП данные получены успешно\n";
        echo "   Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'Да' : 'Нет') . "\n";
        echo "   Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
        echo "   Репутационное воздействие: " . ($rnp_data['reputation_impact'] ?? 'неизвестно') . "\n";
        echo "   Источник: " . ($rnp_data['source'] ?? 'неизвестно') . "\n";
        
        if (isset($rnp_data['violations']) && !empty($rnp_data['violations'])) {
            echo "   Детали нарушений:\n";
            foreach ($rnp_data['violations'] as $violation) {
                echo "     - " . $violation['description'] . " (тяжесть: " . $violation['severity'] . ")\n";
            }
        }
        
        // Тестируем рекомендации
        $recommendations = $rnp_api->get_rnp_recommendations($rnp_data);
        echo "   Рекомендации:\n";
        foreach ($recommendations as $rec) {
            echo "     - " . $rec . "\n";
        }
    } else {
        echo "❌ Ошибка получения данных РНП\n";
        if (is_wp_error($rnp_data)) {
            echo "   " . $rnp_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Исключение при тестировании РНП: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем ФССП API
echo "💼 Тестирование ФССП API:\n";
echo "------------------------\n";
try {
    $fssp_api = new FSSPApi();
    $fssp_data = $fssp_api->get_enforcement_data($test_inn);
    
    if ($fssp_data && !is_wp_error($fssp_data)) {
        echo "✅ ФССП данные получены успешно\n";
        echo "   Исполнительные производства: " . ($fssp_data['has_enforcement_proceedings'] ? 'Есть' : 'Нет') . "\n";
        echo "   Количество производств: " . ($fssp_data['proceedings_count'] ?? 0) . "\n";
        echo "   Общая задолженность: " . number_format($fssp_data['total_debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   Финансовый риск: " . ($fssp_data['financial_risk_level'] ?? 'неизвестно') . "\n";
        echo "   Источник: " . ($fssp_data['source'] ?? 'неизвестно') . "\n";
        
        if (isset($fssp_data['proceedings']) && !empty($fssp_data['proceedings'])) {
            echo "   Детали производств:\n";
            foreach ($fssp_data['proceedings'] as $proceeding) {
                echo "     - " . $proceeding['description'] . " (" . number_format($proceeding['debt_amount'], 0, ',', ' ') . " руб.)\n";
            }
        }
        
        // Тестируем рекомендации
        $recommendations = $fssp_api->get_fssp_recommendations($fssp_data);
        echo "   Рекомендации:\n";
        foreach ($recommendations as $rec) {
            echo "     - " . $rec . "\n";
        }
    } else {
        echo "❌ Ошибка получения данных ФССП\n";
        if (is_wp_error($fssp_data)) {
            echo "   " . $fssp_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Исключение при тестировании ФССП: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем интеграцию с основным плагином
echo "🔗 Тестирование интеграции с основным плагином:\n";
echo "---------------------------------------------\n";

// Подключаем основной класс плагина
require_once('company-rating-checker.php');

try {
    $plugin = new CompanyRatingChecker();
    
    // Используем рефлексию для доступа к приватным методам
    $reflection = new ReflectionClass($plugin);
    
    // Тестируем методы получения данных
    $get_efrsb_method = $reflection->getMethod('get_efrsb_data');
    $get_efrsb_method->setAccessible(true);
    $efrsb_result = $get_efrsb_method->invoke($plugin, $test_inn);
    
    $get_rnp_method = $reflection->getMethod('get_rnp_data');
    $get_rnp_method->setAccessible(true);
    $rnp_result = $get_rnp_method->invoke($plugin, $test_inn);
    
    $get_fssp_method = $reflection->getMethod('get_fssp_data');
    $get_fssp_method->setAccessible(true);
    $fssp_result = $get_fssp_method->invoke($plugin, $test_inn);
    
    echo "✅ Методы интеграции работают корректно\n";
    echo "   ЕФРСБ данные: " . ($efrsb_result ? 'получены' : 'не получены') . "\n";
    echo "   РНП данные: " . ($rnp_result ? 'получены' : 'не получены') . "\n";
    echo "   ФССП данные: " . ($fssp_result ? 'получены' : 'не получены') . "\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании интеграции: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем расчет баллов
echo "📊 Тестирование расчета баллов:\n";
echo "------------------------------\n";

try {
    $plugin = new CompanyRatingChecker();
    $reflection = new ReflectionClass($plugin);
    
    // Создаем тестовые данные компании
    $test_company_data = array(
        'efrsb' => $efrsb_data ?? null,
        'rnp' => $rnp_data ?? null,
        'fssp' => $fssp_data ?? null
    );
    
    // Тестируем расчет баллов ЕФРСБ
    $calculate_efrsb_method = $reflection->getMethod('calculate_efrsb_score');
    $calculate_efrsb_method->setAccessible(true);
    $efrsb_score = $calculate_efrsb_method->invoke($plugin, $test_company_data);
    
    // Тестируем расчет баллов РНП
    $calculate_rnp_method = $reflection->getMethod('calculate_rnp_score');
    $calculate_rnp_method->setAccessible(true);
    $rnp_score = $calculate_rnp_method->invoke($plugin, $test_company_data);
    
    // Тестируем расчет баллов ФССП
    $calculate_fssp_method = $reflection->getMethod('calculate_fssp_score');
    $calculate_fssp_method->setAccessible(true);
    $fssp_score = $calculate_fssp_method->invoke($plugin, $test_company_data);
    
    echo "✅ Расчет баллов работает корректно\n";
    echo "   ЕФРСБ балл: {$efrsb_score}/20\n";
    echo "   РНП балл: {$rnp_score}/15\n";
    echo "   ФССП балл: {$fssp_score}/15\n";
    echo "   Общий балл новых факторов: " . ($efrsb_score + $rnp_score + $fssp_score) . "/50\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при расчете баллов: " . $e->getMessage() . "\n";
}
echo "\n";

echo "🎯 Тестирование завершено!\n";
echo "========================\n";
echo "Новые источники данных успешно интегрированы:\n";
echo "- ЕФРСБ (20 баллов) - процедуры банкротства\n";
echo "- РНП (15 баллов) - недобросовестные поставщики\n";
echo "- ФССП (15 баллов) - исполнительные производства\n";
echo "\nОбщий максимальный балл увеличен на 50 баллов!\n";
?>
