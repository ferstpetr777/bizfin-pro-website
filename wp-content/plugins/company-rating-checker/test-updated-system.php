<?php
/**
 * Тест обновленной системы без проблемных источников данных
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной класс плагина
require_once('company-rating-checker.php');

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Тестируем ИНН 5260482041
$inn = '5260482041';

echo "=== ТЕСТ ОБНОВЛЕННОЙ СИСТЕМЫ ===\n";
echo "ИНН: $inn\n";
echo "Дата теста: " . date('Y-m-d H:i:s') . "\n\n";

// Проверяем настройки источников данных
echo "=== НАСТРОЙКИ ИСТОЧНИКОВ ДАННЫХ ===\n";
echo "ФССП: " . (get_option('crc_fssp_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "Госзакупки: " . (get_option('crc_zakupki_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "ФНС: " . (get_option('crc_fns_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "Арбитраж: " . (get_option('crc_arbitration_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "Росстат: " . (get_option('crc_rosstat_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "ЕФРСБ: " . (get_option('crc_efrsb_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n";
echo "РНП: " . (get_option('crc_rnp_enabled', 1) ? 'ВКЛ' : 'ВЫКЛ') . "\n\n";

// Получаем данные компании через Reflection API
echo "=== ПОЛУЧЕНИЕ ДАННЫХ КОМПАНИИ ===\n";
$reflection = new ReflectionClass($plugin);
$get_company_data_method = $reflection->getMethod('get_company_data');
$get_company_data_method->setAccessible(true);
$company_data = $get_company_data_method->invoke($plugin, $inn);

if ($company_data) {
    echo "✅ Данные получены успешно\n";
    
    // Проверяем какие источники данных есть
    echo "\n=== ИСТОЧНИКИ ДАННЫХ В ОТВЕТЕ ===\n";
    $sources = array('dadata', 'egrul', 'msp', 'arbitration', 'zakupki', 'fns', 'rosstat', 'efrsb', 'rnp', 'fssp');
    foreach ($sources as $source) {
        if (isset($company_data[$source])) {
            echo "✅ $source: ЕСТЬ\n";
        } else {
            echo "❌ $source: НЕТ\n";
        }
    }
    
    // Рассчитываем рейтинг через Reflection API
    echo "\n=== РАСЧЕТ РЕЙТИНГА ===\n";
    $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
    $calculate_rating_method->setAccessible(true);
    $rating = $calculate_rating_method->invoke($plugin, $company_data);
    
    echo "Общий балл: " . $rating['total_score'] . "/" . $rating['max_score'] . "\n";
    echo "Рейтинг: " . $rating['rating'] . "\n\n";
    
    // Показываем факторы
    echo "=== ФАКТОРЫ РЕЙТИНГА ===\n";
    foreach ($rating['factors'] as $key => $factor) {
        echo sprintf("%-25s: %2d/%2d - %s\n", 
            $factor['name'], 
            $factor['score'], 
            $factor['max_score'],
            $factor['description']
        );
    }
    
    // Проверяем, что проблемные источники не влияют на рейтинг
    echo "\n=== ПРОВЕРКА ОТКЛЮЧЕННЫХ ИСТОЧНИКОВ ===\n";
    $problematic_sources = array('zakupki', 'fns', 'fssp');
    foreach ($problematic_sources as $source) {
        if (isset($rating['factors'][$source])) {
            echo "❌ $source: ВЛИЯЕТ НА РЕЙТИНГ (должен быть отключен)\n";
        } else {
            echo "✅ $source: НЕ ВЛИЯЕТ НА РЕЙТИНГ (корректно отключен)\n";
        }
    }
    
} else {
    echo "❌ Ошибка получения данных компании\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЕН ===\n";
?>
