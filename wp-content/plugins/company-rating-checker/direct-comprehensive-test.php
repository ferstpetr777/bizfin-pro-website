<?php
/**
 * Прямой комплексный тест всех источников данных
 * Company Rating Checker - Direct Comprehensive Test
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем все классы
require_once('company-rating-checker.php');
require_once('simple-arbitration.php');
require_once('zakupki-api.php');
require_once('cache-manager.php');
require_once('fns-api.php');
require_once('rosstat-api.php');
require_once('advanced-analytics.php');
require_once('data-export.php');
require_once('efrsb-api.php');
require_once('rnp-api.php');
require_once('fssp-api.php');

echo "🔍 ПРЯМОЙ КОМПЛЕКСНЫЙ ТЕСТ ВСЕХ ИСТОЧНИКОВ ДАННЫХ\n";
echo "=================================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 Тестовый ИНН: {$test_inn}\n";
echo "⏰ Время начала теста: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Используем рефлексию для доступа к приватным методам
$reflection = new ReflectionClass($plugin);

echo "🚀 ЗАПУСК ПРЯМОГО АНАЛИЗА...\n";
echo "============================\n\n";

// Создаем массив для хранения всех данных
$company_data = array();
$all_scores = array();
$total_score = 0;
$max_score = 100; // Базовый балл

echo "📊 ТЕСТИРОВАНИЕ КАЖДОГО ИСТОЧНИКА ДАННЫХ:\n";
echo "========================================\n\n";

// 1. Тестируем базовые данные (DaData)
echo "1️⃣ БАЗОВЫЕ ДАННЫЕ (DaData API):\n";
echo "-------------------------------\n";
try {
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $basic_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($basic_data && !is_wp_error($basic_data)) {
        $company_data['basic'] = $basic_data;
        echo "   ✅ Название: " . ($basic_data['name'] ?? 'Не указано') . "\n";
        echo "   ✅ Адрес: " . ($basic_data['address'] ?? 'Не указан') . "\n";
        echo "   ✅ ОКВЭД: " . ($basic_data['okved'] ?? 'Не указан') . "\n";
        echo "   ✅ Статус: " . ($basic_data['status'] ?? 'Не указан') . "\n";
        echo "   ✅ Руководитель: " . ($basic_data['manager'] ?? 'Не указан') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Тестируем ЕГРЮЛ данные
echo "2️⃣ ЕГРЮЛ ДАННЫЕ:\n";
echo "----------------\n";
try {
    $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
    $get_egrul_data_method->setAccessible(true);
    $egrul_data = $get_egrul_data_method->invoke($plugin, $test_inn);
    
    if ($egrul_data && !is_wp_error($egrul_data)) {
        $company_data['egrul'] = $egrul_data;
        echo "   ✅ Статус: " . ($egrul_data['status'] ?? 'Не указан') . "\n";
        echo "   ✅ Руководитель: " . ($egrul_data['manager'] ?? 'Не указан') . "\n";
        echo "   ✅ Дата регистрации: " . ($egrul_data['registration_date'] ?? 'Не указана') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Тестируем МСП данные
echo "3️⃣ МСП ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_msp_data_method = $reflection->getMethod('get_msp_data');
    $get_msp_data_method->setAccessible(true);
    $msp_data = $get_msp_data_method->invoke($plugin, $test_inn);
    
    if ($msp_data && !is_wp_error($msp_data)) {
        $company_data['msp'] = $msp_data;
        echo "   ✅ Статус: " . ($msp_data['status'] ?? 'Не указан') . "\n";
        echo "   ✅ Категория: " . ($msp_data['category'] ?? 'Не указана') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Тестируем арбитражные данные
echo "4️⃣ АРБИТРАЖНЫЕ ДАННЫЕ:\n";
echo "----------------------\n";
try {
    $get_arbitration_data_method = $reflection->getMethod('get_arbitration_data');
    $get_arbitration_data_method->setAccessible(true);
    $arbitration_data = $get_arbitration_data_method->invoke($plugin, $test_inn);
    
    if ($arbitration_data && !is_wp_error($arbitration_data)) {
        $company_data['arbitration'] = $arbitration_data;
        echo "   ✅ Уровень риска: " . ($arbitration_data['risk_level'] ?? 'Не указан') . "\n";
        echo "   ✅ Балл риска: " . ($arbitration_data['risk_score'] ?? 'Не указан') . "/100\n";
        echo "   ✅ Источник: " . ($arbitration_data['source'] ?? 'Не указан') . "\n";
        $max_score += 10; // Арбитражные риски
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Тестируем госзакупки
echo "5️⃣ ГОСУДАРСТВЕННЫЕ ЗАКУПКИ:\n";
echo "---------------------------\n";
try {
    $get_zakupki_data_method = $reflection->getMethod('get_zakupki_data');
    $get_zakupki_data_method->setAccessible(true);
    $zakupki_data = $get_zakupki_data_method->invoke($plugin, $test_inn);
    
    if ($zakupki_data && !is_wp_error($zakupki_data)) {
        $company_data['zakupki'] = $zakupki_data;
        echo "   ✅ Репутация: " . ($zakupki_data['reputation'] ?? 'Не указана') . "\n";
        echo "   ✅ Количество контрактов: " . ($zakupki_data['contracts_count'] ?? 'Не указано') . "\n";
        echo "   ✅ Общая сумма: " . number_format($zakupki_data['total_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   ✅ Источник: " . ($zakupki_data['source'] ?? 'Не указан') . "\n";
        $max_score += 10; // Госзакупки
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Тестируем ФНС данные
echo "6️⃣ ФНС ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_fns_data_method = $reflection->getMethod('get_fns_data');
    $get_fns_data_method->setAccessible(true);
    $fns_data = $get_fns_data_method->invoke($plugin, $test_inn);
    
    if ($fns_data && !is_wp_error($fns_data)) {
        $company_data['fns'] = $fns_data;
        echo "   ✅ Выручка: " . number_format($fns_data['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   ✅ Рентабельность: " . ($fns_data['profitability'] ?? 'Не указана') . "%\n";
        echo "   ✅ Коэффициент задолженности: " . ($fns_data['debt_ratio'] ?? 'Не указан') . "%\n";
        echo "   ✅ Риск банкротства: " . ($fns_data['bankruptcy_risk'] ?? 'Не указан') . "\n";
        echo "   ✅ Источник: " . ($fns_data['source'] ?? 'Не указан') . "\n";
        $max_score += 15; // ФНС данные
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Тестируем Росстат данные
echo "7️⃣ РОССТАТ ДАННЫЕ:\n";
echo "------------------\n";
try {
    $get_rosstat_data_method = $reflection->getMethod('get_rosstat_data');
    $get_rosstat_data_method->setAccessible(true);
    $rosstat_data = $get_rosstat_data_method->invoke($plugin, $test_inn);
    
    if ($rosstat_data && !is_wp_error($rosstat_data)) {
        $company_data['rosstat'] = $rosstat_data;
        if (isset($rosstat_data['region'])) {
            echo "   ✅ Регион: " . ($rosstat_data['region']['region_name'] ?? 'Не указан') . "\n";
            echo "   ✅ Региональный рейтинг: " . ($rosstat_data['region']['statistical_rating'] ?? 'Не указан') . "/10\n";
        }
        if (isset($rosstat_data['sector'])) {
            echo "   ✅ Отрасль: " . ($rosstat_data['sector']['sector_name'] ?? 'Не указана') . "\n";
            echo "   ✅ Отраслевой рейтинг: " . ($rosstat_data['sector']['sector_rating'] ?? 'Не указан') . "/10\n";
        }
        if (isset($rosstat_data['enterprise_size'])) {
            echo "   ✅ Размер предприятия: " . ($rosstat_data['enterprise_size']['size_category'] ?? 'Не указан') . "\n";
        }
        echo "   ✅ Источник: " . ($rosstat_data['source'] ?? 'Не указан') . "\n";
        $max_score += 10; // Росстат данные
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. Тестируем ЕФРСБ данные
echo "8️⃣ ЕФРСБ ДАННЫЕ:\n";
echo "----------------\n";
try {
    $get_efrsb_data_method = $reflection->getMethod('get_efrsb_data');
    $get_efrsb_data_method->setAccessible(true);
    $efrsb_data = $get_efrsb_data_method->invoke($plugin, $test_inn);
    
    if ($efrsb_data && !is_wp_error($efrsb_data)) {
        $company_data['efrsb'] = $efrsb_data;
        echo "   ✅ Статус банкротства: " . ($efrsb_data['bankruptcy_status'] ?? 'Не указан') . "\n";
        echo "   ✅ Уровень риска: " . ($efrsb_data['bankruptcy_risk_level'] ?? 'Не указан') . "\n";
        echo "   ✅ Балл риска: " . ($efrsb_data['bankruptcy_risk_score'] ?? 'Не указан') . "/100\n";
        echo "   ✅ Количество дел: " . count($efrsb_data['bankruptcy_cases'] ?? []) . "\n";
        echo "   ✅ Источник: " . ($efrsb_data['source'] ?? 'Не указан') . "\n";
        $max_score += 20; // ЕФРСБ данные
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 9. Тестируем РНП данные
echo "9️⃣ РНП ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_rnp_data_method = $reflection->getMethod('get_rnp_data');
    $get_rnp_data_method->setAccessible(true);
    $rnp_data = $get_rnp_data_method->invoke($plugin, $test_inn);
    
    if ($rnp_data && !is_wp_error($rnp_data)) {
        $company_data['rnp'] = $rnp_data;
        echo "   ✅ Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'Да' : 'Нет') . "\n";
        echo "   ✅ Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
        echo "   ✅ Репутационное воздействие: " . ($rnp_data['reputation_impact'] ?? 'Не указано') . "\n";
        echo "   ✅ Источник: " . ($rnp_data['source'] ?? 'Не указан') . "\n";
        $max_score += 15; // РНП данные
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 10. Тестируем ФССП данные
echo "🔟 ФССП ДАННЫЕ:\n";
echo "---------------\n";
try {
    $get_fssp_data_method = $reflection->getMethod('get_fssp_data');
    $get_fssp_data_method->setAccessible(true);
    $fssp_data = $get_fssp_data_method->invoke($plugin, $test_inn);
    
    if ($fssp_data && !is_wp_error($fssp_data)) {
        $company_data['fssp'] = $fssp_data;
        echo "   ✅ Исполнительные производства: " . ($fssp_data['has_enforcement_proceedings'] ? 'Есть' : 'Нет') . "\n";
        echo "   ✅ Количество производств: " . ($fssp_data['proceedings_count'] ?? 0) . "\n";
        echo "   ✅ Общая задолженность: " . number_format($fssp_data['total_debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   ✅ Финансовый риск: " . ($fssp_data['financial_risk_level'] ?? 'Не указан') . "\n";
        echo "   ✅ Источник: " . ($fssp_data['source'] ?? 'Не указан') . "\n";
        $max_score += 15; // ФССП данные
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Теперь рассчитываем рейтинг
echo "📊 РАСЧЕТ РЕЙТИНГА:\n";
echo "===================\n\n";

try {
    $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
    $calculate_rating_method->setAccessible(true);
    $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
    
    if ($rating_result) {
        echo "✅ РЕЙТИНГ РАССЧИТАН УСПЕШНО!\n\n";
        
        // Выводим детальные факторы
        echo "📋 ДЕТАЛЬНЫЕ ФАКТОРЫ РЕЙТИНГА:\n";
        echo "=============================\n";
        foreach ($rating_result['factors'] as $key => $factor) {
            $percentage = round(($factor['score'] / $factor['max_score']) * 100, 1);
            $status_icon = $percentage >= 80 ? '🟢' : ($percentage >= 60 ? '🟡' : '🔴');
            echo "   {$status_icon} {$factor['name']}: {$factor['score']}/{$factor['max_score']} ({$percentage}%)\n";
            echo "      📝 {$factor['description']}\n\n";
        }
        
        // ИТОГОВЫЕ РЕЗУЛЬТАТЫ
        echo "🏆 ИТОГОВЫЕ РЕЗУЛЬТАТЫ РЕЙТИНГА:\n";
        echo "===============================\n";
        echo "   📊 Общий балл: " . $rating_result['total_score'] . "/" . $rating_result['max_score'] . "\n";
        echo "   🎯 Рейтинг: " . $rating_result['rating']['level'] . " - " . $rating_result['rating']['description'] . "\n";
        echo "   📈 Процент выполнения: " . round(($rating_result['total_score'] / $rating_result['max_score']) * 100, 2) . "%\n\n";
        
        // Расширенная аналитика
        if (isset($rating_result['advanced_analytics'])) {
            echo "1️⃣1️⃣ РАСШИРЕННАЯ АНАЛИТИКА:\n";
            echo "--------------------------\n";
            $analytics = $rating_result['advanced_analytics'];
            echo "   ✅ Финансовое здоровье: " . ($analytics['financial_health']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Операционная эффективность: " . ($analytics['operational_efficiency']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Рыночная позиция: " . ($analytics['market_position']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Общий риск: " . ($analytics['overall_risk']['level'] ?? 'Не указан') . "\n\n";
        }
        
    } else {
        echo "❌ Ошибка при расчете рейтинга\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при расчете рейтинга: " . $e->getMessage() . "\n";
}

// СТАТИСТИКА ИСТОЧНИКОВ
echo "📊 СТАТИСТИКА ИСТОЧНИКОВ ДАННЫХ:\n";
echo "===============================\n";
$sources_count = 0;
$sources_working = 0;

$sources = [
    'basic' => 'DaData API',
    'egrul' => 'ЕГРЮЛ',
    'msp' => 'МСП',
    'arbitration' => 'Арбитражные суды',
    'zakupki' => 'Госзакупки',
    'fns' => 'ФНС',
    'rosstat' => 'Росстат',
    'efrsb' => 'ЕФРСБ',
    'rnp' => 'РНП',
    'fssp' => 'ФССП'
];

foreach ($sources as $key => $name) {
    $sources_count++;
    if (isset($company_data[$key])) {
        $sources_working++;
        echo "   ✅ {$name}: Данные получены\n";
    } else {
        echo "   ❌ {$name}: Данные не получены\n";
    }
}

echo "\n   📈 Работоспособность источников: {$sources_working}/{$sources_count} (" . round(($sources_working/$sources_count)*100, 1) . "%)\n\n";

echo "⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ПРЯМОЙ КОМПЛЕКСНЫЙ ТЕСТ ЗАВЕРШЕН!\n";
echo "====================================\n";
?>
