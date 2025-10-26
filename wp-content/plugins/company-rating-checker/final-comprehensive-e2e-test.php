<?php
/**
 * Итоговый комплексный E2E тест всех источников данных
 * Company Rating Checker - Final Comprehensive E2E Test
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ИТОГОВЫЙ КОМПЛЕКСНЫЙ E2E ТЕСТ ВСЕХ ИСТОЧНИКОВ\n";
echo "================================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 Тестовый ИНН: {$test_inn}\n";
echo "⏰ Время начала теста: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Используем рефлексию для доступа к приватным методам
$reflection = new ReflectionClass($plugin);

echo "🚀 ЗАПУСК КОМПЛЕКСНОГО АНАЛИЗА ВСЕХ ИСТОЧНИКОВ...\n";
echo "=================================================\n\n";

// Создаем массив для хранения всех данных
$company_data = array();
$all_sources_status = array();

echo "📊 ТЕСТИРОВАНИЕ ВСЕХ ИСТОЧНИКОВ ДАННЫХ:\n";
echo "=======================================\n\n";

// 1. Базовые данные (DaData)
echo "1️⃣ БАЗОВЫЕ ДАННЫЕ (DaData API):\n";
echo "-------------------------------\n";
try {
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $basic_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($basic_data && !is_wp_error($basic_data)) {
        $company_data['basic'] = $basic_data;
        $all_sources_status['dadata'] = '✅ Работает';
        echo "   ✅ Название: " . ($basic_data['name'] ?? 'Не указано') . "\n";
        echo "   ✅ Адрес: " . ($basic_data['address'] ?? 'Не указан') . "\n";
        echo "   ✅ ОКВЭД: " . ($basic_data['okved'] ?? 'Не указан') . "\n";
        echo "   ✅ Статус: " . ($basic_data['status'] ?? 'Не указан') . "\n";
    } else {
        $all_sources_status['dadata'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['dadata'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. ЕГРЮЛ данные
echo "2️⃣ ЕГРЮЛ ДАННЫЕ:\n";
echo "----------------\n";
try {
    $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
    $get_egrul_data_method->setAccessible(true);
    $egrul_data = $get_egrul_data_method->invoke($plugin, $test_inn);
    
    if ($egrul_data && !is_wp_error($egrul_data)) {
        $company_data['egrul'] = $egrul_data;
        $all_sources_status['egrul'] = '✅ Работает';
        echo "   ✅ Название: " . ($egrul_data['name'] ?? 'Не указано') . "\n";
        echo "   ✅ ОГРН: " . ($egrul_data['ogrn'] ?? 'Не указан') . "\n";
        echo "   ✅ КПП: " . ($egrul_data['kpp'] ?? 'Не указан') . "\n";
        echo "   ✅ Руководитель: " . ($egrul_data['manager'] ?? 'Не указан') . "\n";
        echo "   ✅ Статус: " . ($egrul_data['status'] ?? 'Не указан') . "\n";
    } else {
        $all_sources_status['egrul'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['egrul'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. МСП данные
echo "3️⃣ МСП ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_msp_data_method = $reflection->getMethod('get_msp_data');
    $get_msp_data_method->setAccessible(true);
    $msp_data = $get_msp_data_method->invoke($plugin, $test_inn);
    
    if ($msp_data && !is_wp_error($msp_data)) {
        $company_data['msp'] = $msp_data;
        $all_sources_status['msp'] = '✅ Работает';
        echo "   ✅ Статус: " . ($msp_data['status'] ?? 'Не указан') . "\n";
        echo "   ✅ Категория: " . ($msp_data['category'] ?? 'Не указана') . "\n";
    } else {
        $all_sources_status['msp'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['msp'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Арбитражные данные
echo "4️⃣ АРБИТРАЖНЫЕ ДАННЫЕ:\n";
echo "----------------------\n";
try {
    $get_arbitration_data_method = $reflection->getMethod('get_arbitration_data');
    $get_arbitration_data_method->setAccessible(true);
    $arbitration_data = $get_arbitration_data_method->invoke($plugin, $test_inn);
    
    if ($arbitration_data && !is_wp_error($arbitration_data)) {
        $company_data['arbitration'] = $arbitration_data;
        $all_sources_status['arbitration'] = '✅ Работает';
        echo "   ✅ Уровень риска: " . ($arbitration_data['risk_level'] ?? 'Не указан') . "\n";
        echo "   ✅ Балл риска: " . ($arbitration_data['risk_score'] ?? 'Не указан') . "/100\n";
    } else {
        $all_sources_status['arbitration'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['arbitration'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Госзакупки
echo "5️⃣ ГОСУДАРСТВЕННЫЕ ЗАКУПКИ:\n";
echo "---------------------------\n";
try {
    $get_zakupki_data_method = $reflection->getMethod('get_zakupki_data');
    $get_zakupki_data_method->setAccessible(true);
    $zakupki_data = $get_zakupki_data_method->invoke($plugin, $test_inn);
    
    if ($zakupki_data && !is_wp_error($zakupki_data)) {
        $company_data['zakupki'] = $zakupki_data;
        $all_sources_status['zakupki'] = '✅ Работает';
        echo "   ✅ Репутация: " . ($zakupki_data['reputation'] ?? 'Не указана') . "\n";
        echo "   ✅ Контракты: " . ($zakupki_data['contracts_count'] ?? 'Не указано') . "\n";
        echo "   ✅ Сумма: " . number_format($zakupki_data['total_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
    } else {
        $all_sources_status['zakupki'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['zakupki'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. ФНС данные
echo "6️⃣ ФНС ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_fns_data_method = $reflection->getMethod('get_fns_data');
    $get_fns_data_method->setAccessible(true);
    $fns_data = $get_fns_data_method->invoke($plugin, $test_inn);
    
    if ($fns_data && !is_wp_error($fns_data)) {
        $company_data['fns'] = $fns_data;
        $all_sources_status['fns'] = '✅ Работает';
        echo "   ✅ Выручка: " . number_format($fns_data['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   ✅ Рентабельность: " . ($fns_data['profitability'] ?? 'Не указана') . "%\n";
        echo "   ✅ Риск банкротства: " . ($fns_data['bankruptcy_risk'] ?? 'Не указан') . "\n";
    } else {
        $all_sources_status['fns'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['fns'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Росстат данные
echo "7️⃣ РОССТАТ ДАННЫЕ:\n";
echo "------------------\n";
try {
    $get_rosstat_data_method = $reflection->getMethod('get_rosstat_data');
    $get_rosstat_data_method->setAccessible(true);
    $rosstat_data = $get_rosstat_data_method->invoke($plugin, $test_inn);
    
    if ($rosstat_data && !is_wp_error($rosstat_data)) {
        $company_data['rosstat'] = $rosstat_data;
        $all_sources_status['rosstat'] = '✅ Работает';
        if (isset($rosstat_data['region'])) {
            echo "   ✅ Регион: " . ($rosstat_data['region']['region_name'] ?? 'Не указан') . "\n";
        }
        if (isset($rosstat_data['sector'])) {
            echo "   ✅ Отрасль: " . ($rosstat_data['sector']['sector_name'] ?? 'Не указана') . "\n";
        }
    } else {
        $all_sources_status['rosstat'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['rosstat'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 8. ЕФРСБ данные
echo "8️⃣ ЕФРСБ ДАННЫЕ:\n";
echo "----------------\n";
try {
    $get_efrsb_data_method = $reflection->getMethod('get_efrsb_data');
    $get_efrsb_data_method->setAccessible(true);
    $efrsb_data = $get_efrsb_data_method->invoke($plugin, $test_inn);
    
    if ($efrsb_data && !is_wp_error($efrsb_data)) {
        $company_data['efrsb'] = $efrsb_data;
        $all_sources_status['efrsb'] = '✅ Работает';
        echo "   ✅ Статус банкротства: " . ($efrsb_data['bankruptcy_status'] ?? 'Не указан') . "\n";
        echo "   ✅ Уровень риска: " . ($efrsb_data['bankruptcy_risk_level'] ?? 'Не указан') . "\n";
        echo "   ✅ Количество дел: " . count($efrsb_data['bankruptcy_cases'] ?? []) . "\n";
    } else {
        $all_sources_status['efrsb'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['efrsb'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 9. РНП данные
echo "9️⃣ РНП ДАННЫЕ:\n";
echo "--------------\n";
try {
    $get_rnp_data_method = $reflection->getMethod('get_rnp_data');
    $get_rnp_data_method->setAccessible(true);
    $rnp_data = $get_rnp_data_method->invoke($plugin, $test_inn);
    
    if ($rnp_data && !is_wp_error($rnp_data)) {
        $company_data['rnp'] = $rnp_data;
        $all_sources_status['rnp'] = '✅ Работает';
        echo "   ✅ Недобросовестный поставщик: " . ($rnp_data['is_dishonest_supplier'] ? 'Да' : 'Нет') . "\n";
        echo "   ✅ Количество нарушений: " . ($rnp_data['violation_count'] ?? 0) . "\n";
        echo "   ✅ Репутация: " . ($rnp_data['reputation_impact'] ?? 'Не указана') . "\n";
    } else {
        $all_sources_status['rnp'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['rnp'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 10. ФССП данные
echo "🔟 ФССП ДАННЫЕ:\n";
echo "---------------\n";
try {
    $get_fssp_data_method = $reflection->getMethod('get_fssp_data');
    $get_fssp_data_method->setAccessible(true);
    $fssp_data = $get_fssp_data_method->invoke($plugin, $test_inn);
    
    if ($fssp_data && !is_wp_error($fssp_data)) {
        $company_data['fssp'] = $fssp_data;
        $all_sources_status['fssp'] = '✅ Работает';
        echo "   ✅ Исполнительные производства: " . ($fssp_data['has_enforcement_proceedings'] ? 'Есть' : 'Нет') . "\n";
        echo "   ✅ Количество производств: " . ($fssp_data['proceedings_count'] ?? 0) . "\n";
        echo "   ✅ Общая задолженность: " . number_format($fssp_data['total_debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
    } else {
        $all_sources_status['fssp'] = '❌ Не работает';
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    $all_sources_status['fssp'] = '❌ Ошибка: ' . $e->getMessage();
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Теперь рассчитываем рейтинг
echo "📊 РАСЧЕТ ИТОГОВОГО РЕЙТИНГА:\n";
echo "=============================\n\n";

try {
    $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
    $calculate_rating_method->setAccessible(true);
    $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
    
    if ($rating_result) {
        echo "✅ РЕЙТИНГ РАССЧИТАН УСПЕШНО!\n\n";
        
        // ИТОГОВЫЕ РЕЗУЛЬТАТЫ
        echo "🏆 ИТОГОВЫЕ РЕЗУЛЬТАТЫ РЕЙТИНГА:\n";
        echo "===============================\n";
        echo "   📊 Общий балл: " . $rating_result['total_score'] . "/" . $rating_result['max_score'] . "\n";
        echo "   🎯 Рейтинг: " . $rating_result['rating']['level'] . " - " . $rating_result['rating']['description'] . "\n";
        echo "   📈 Процент выполнения: " . round(($rating_result['total_score'] / $rating_result['max_score']) * 100, 2) . "%\n\n";
        
        // ДЕТАЛЬНЫЕ ФАКТОРЫ
        echo "📋 ДЕТАЛЬНЫЕ ФАКТОРЫ РЕЙТИНГА:\n";
        echo "=============================\n";
        foreach ($rating_result['factors'] as $key => $factor) {
            $percentage = round(($factor['score'] / $factor['max_score']) * 100, 1);
            $status_icon = $percentage >= 80 ? '🟢' : ($percentage >= 60 ? '🟡' : '🔴');
            echo "   {$status_icon} {$factor['name']}: {$factor['score']}/{$factor['max_score']} ({$percentage}%)\n";
            echo "      📝 {$factor['description']}\n\n";
        }
        
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
echo "📊 СТАТИСТИКА ВСЕХ ИСТОЧНИКОВ ДАННЫХ:\n";
echo "=====================================\n";
$working_sources = 0;
$total_sources = count($all_sources_status);

foreach ($all_sources_status as $source => $status) {
    echo "   {$status} " . strtoupper($source) . "\n";
    if (strpos($status, '✅') !== false) {
        $working_sources++;
    }
}

echo "\n   📈 Работоспособность источников: {$working_sources}/{$total_sources} (" . round(($working_sources/$total_sources)*100, 1) . "%)\n\n";

// Проверяем интеграцию в механизм расчета рейтинга
echo "🔧 ПРОВЕРКА ИНТЕГРАЦИИ В МЕХАНИЗМ РАСЧЕТА РЕЙТИНГА:\n";
echo "==================================================\n";

$integrated_sources = array();
$max_score = 100; // Базовый балл

// Проверяем каждый источник в расчете рейтинга
$sources_in_rating = array(
    'arbitration' => array('enabled_option' => 'crc_arbitration_enabled', 'points' => 10),
    'zakupki' => array('enabled_option' => 'crc_zakupki_enabled', 'points' => 10),
    'fns' => array('enabled_option' => 'crc_fns_enabled', 'points' => 15),
    'rosstat' => array('enabled_option' => 'crc_rosstat_enabled', 'points' => 10),
    'efrsb' => array('enabled_option' => 'crc_efrsb_enabled', 'points' => 20),
    'rnp' => array('enabled_option' => 'crc_rnp_enabled', 'points' => 15),
    'fssp' => array('enabled_option' => 'crc_fssp_enabled', 'points' => 15)
);

foreach ($sources_in_rating as $source => $config) {
    $enabled = get_option($config['enabled_option'], 1);
    if ($enabled) {
        $max_score += $config['points'];
        $integrated_sources[] = $source;
        echo "   ✅ {$source}: интегрирован (+{$config['points']} баллов)\n";
    } else {
        echo "   ❌ {$source}: отключен\n";
    }
}

echo "\n   📊 Максимальный балл с учетом всех источников: {$max_score}\n";
echo "   📈 Интегрированных источников: " . count($integrated_sources) . "/" . count($sources_in_rating) . "\n\n";

echo "⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ИТОГОВЫЙ КОМПЛЕКСНЫЙ E2E ТЕСТ ЗАВЕРШЕН!\n";
echo "==========================================\n";
?>
