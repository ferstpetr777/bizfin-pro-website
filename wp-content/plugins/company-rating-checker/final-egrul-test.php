<?php
/**
 * Финальный тест интеграции улучшенного ЕГРЮЛ API
 * Company Rating Checker - Final EGRUL Integration Test
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ФИНАЛЬНЫЙ ТЕСТ ИНТЕГРАЦИИ УЛУЧШЕННОГО ЕГРЮЛ API\n";
echo "==================================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Используем рефлексию для доступа к приватным методам
$reflection = new ReflectionClass($plugin);

echo "🚀 ТЕСТИРОВАНИЕ ИНТЕГРАЦИИ...\n";
echo "=============================\n\n";

// Тестируем метод get_egrul_data
echo "1️⃣ ТЕСТ МЕТОДА get_egrul_data:\n";
echo "------------------------------\n";
try {
    $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
    $get_egrul_data_method->setAccessible(true);
    $egrul_data = $get_egrul_data_method->invoke($plugin, $test_inn);
    
    if ($egrul_data && !is_wp_error($egrul_data)) {
        echo "   ✅ Данные ЕГРЮЛ получены успешно\n";
        echo "   📝 Название: " . ($egrul_data['name'] ?? 'Не указано') . "\n";
        echo "   🆔 ОГРН: " . ($egrul_data['ogrn'] ?? 'Не указан') . "\n";
        echo "   🏛️ КПП: " . ($egrul_data['kpp'] ?? 'Не указан') . "\n";
        echo "   📍 Адрес: " . ($egrul_data['address'] ?? 'Не указан') . "\n";
        echo "   📊 Статус: " . ($egrul_data['status'] ?? 'Не указан') . "\n";
        echo "   📅 Дата регистрации: " . ($egrul_data['registration_date'] ?? 'Не указана') . "\n";
        echo "   👤 Руководитель: " . ($egrul_data['manager'] ?? 'Не указан') . "\n";
        echo "   🏭 ОКВЭД: " . ($egrul_data['okved'] ?? 'Не указан') . "\n";
        echo "   💰 Уставный капитал: " . number_format($egrul_data['authorized_capital'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   🔍 Источник: " . ($egrul_data['source'] ?? 'Не указан') . "\n";
    } else {
        echo "   ❌ Данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем полный анализ компании
echo "2️⃣ ТЕСТ ПОЛНОГО АНАЛИЗА КОМПАНИИ:\n";
echo "----------------------------------\n";
try {
    // Получаем все данные компании
    $company_data = array();
    
    // Получаем базовые данные
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data['basic'] = $get_company_data_method->invoke($plugin, $test_inn);
    
    // Получаем ЕГРЮЛ данные
    $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
    $get_egrul_data_method->setAccessible(true);
    $company_data['egrul'] = $get_egrul_data_method->invoke($plugin, $test_inn);
    
    // Получаем МСП данные
    $get_msp_data_method = $reflection->getMethod('get_msp_data');
    $get_msp_data_method->setAccessible(true);
    $company_data['msp'] = $get_msp_data_method->invoke($plugin, $test_inn);
    
    // Рассчитываем рейтинг
    $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
    $calculate_rating_method->setAccessible(true);
    $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
    
    if ($rating_result && isset($rating_result['factors']['status'])) {
        $status_factor = $rating_result['factors']['status'];
        echo "   ✅ Данные получены через расчет рейтинга\n";
        echo "   📊 Фактор статуса: {$status_factor['score']}/{$status_factor['max_score']}\n";
        echo "   📝 Описание: {$status_factor['description']}\n";
        
        // Проверяем исходные данные ЕГРЮЛ
        if (isset($company_data['egrul'])) {
            $egrul_data = $company_data['egrul'];
            echo "   📝 Название из ЕГРЮЛ: " . ($egrul_data['name'] ?? 'Не указано') . "\n";
            echo "   📊 Статус из ЕГРЮЛ: " . ($egrul_data['status'] ?? 'Не указан') . "\n";
            echo "   🔍 Источник ЕГРЮЛ: " . ($egrul_data['source'] ?? 'Не указан') . "\n";
        }
    } else {
        echo "   ❌ Данные не получены через расчет рейтинга\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// Тестируем несколько запусков для проверки стабильности
echo "3️⃣ ТЕСТ НА СТАБИЛЬНОСТЬ (3 запуска):\n";
echo "------------------------------------\n";
$consistent_results = true;
$first_result = null;

for ($i = 1; $i <= 3; $i++) {
    echo "   Запуск {$i}: ";
    
    try {
        $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
        $get_egrul_data_method->setAccessible(true);
        $test_data = $get_egrul_data_method->invoke($plugin, $test_inn);
        
        if ($test_data && !is_wp_error($test_data)) {
            $name = $test_data['name'] ?? 'Не указано';
            $status = $test_data['status'] ?? 'Не указан';
            $source = $test_data['source'] ?? 'Не указан';
            
            echo "Название: {$name}, Статус: {$status}, Источник: {$source}\n";
            
            // Проверяем консистентность
            if ($i === 1) {
                $first_result = $test_data;
            } else {
                if ($first_result['name'] !== $test_data['name'] ||
                    $first_result['status'] !== $test_data['status']) {
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

echo "\n📈 РЕЗУЛЬТАТ ТЕСТА НА СТАБИЛЬНОСТЬ:\n";
echo "====================================\n";
if ($consistent_results) {
    echo "   ✅ РЕЗУЛЬТАТЫ СТАБИЛЬНЫ - интеграция работает корректно!\n";
} else {
    echo "   ⚠️ РЕЗУЛЬТАТЫ НЕСТАБИЛЬНЫ - требуется дополнительная работа\n";
}

// Проверяем качество данных
echo "\n4️⃣ АНАЛИЗ КАЧЕСТВА ДАННЫХ ЕГРЮЛ:\n";
echo "=================================\n";
try {
    $get_egrul_data_method = $reflection->getMethod('get_egrul_data');
    $get_egrul_data_method->setAccessible(true);
    $egrul_data = $get_egrul_data_method->invoke($plugin, $test_inn);
    
    if ($egrul_data && !is_wp_error($egrul_data)) {
        $data_quality = analyze_data_quality($egrul_data);
        echo "   📊 Качество данных: {$data_quality['score']}/100\n";
        echo "   📝 Заполненность: {$data_quality['completeness']}%\n";
        echo "   ✅ Полных полей: {$data_quality['complete_fields']}\n";
        echo "   ❌ Пустых полей: {$data_quality['empty_fields']}\n";
        
        if ($data_quality['score'] >= 80) {
            echo "   🎯 ОТЛИЧНОЕ качество данных!\n";
        } elseif ($data_quality['score'] >= 60) {
            echo "   👍 ХОРОШЕЕ качество данных\n";
        } elseif ($data_quality['score'] >= 40) {
            echo "   ⚠️ УДОВЛЕТВОРИТЕЛЬНОЕ качество данных\n";
        } else {
            echo "   ❌ НИЗКОЕ качество данных\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка анализа качества: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ФИНАЛЬНЫЙ ТЕСТ ИНТЕГРАЦИИ ЗАВЕРШЕН!\n";
echo "======================================\n";

// Функция анализа качества данных
function analyze_data_quality($data) {
    $required_fields = array('inn', 'name', 'ogrn', 'kpp', 'address', 'status', 'registration_date', 'manager', 'okved', 'authorized_capital');
    $complete_fields = 0;
    $empty_fields = 0;
    
    foreach ($required_fields as $field) {
        if (isset($data[$field]) && !empty($data[$field]) && $data[$field] !== 'Не указано' && $data[$field] !== 'Не указан' && $data[$field] !== 'Не указана') {
            $complete_fields++;
        } else {
            $empty_fields++;
        }
    }
    
    $completeness = round(($complete_fields / count($required_fields)) * 100, 1);
    $score = $completeness;
    
    return array(
        'score' => $score,
        'completeness' => $completeness,
        'complete_fields' => $complete_fields,
        'empty_fields' => $empty_fields
    );
}
?>
