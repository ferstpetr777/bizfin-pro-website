<?php
/**
 * Тест исправленной логики закупок
 * Company Rating Checker - Test Zakupki Fix
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ТЕСТ ИСПРАВЛЕННОЙ ЛОГИКИ ЗАКУПОК\n";
echo "====================================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПРОВЕРКА ИСПРАВЛЕННОЙ ЛОГИКИ ЗАКУПОК...\n";
echo "===========================================\n\n";

// 1. Проверяем улучшенный API закупок
echo "1️⃣ ПРОВЕРКА УЛУЧШЕННОГО API ЗАКУПОК:\n";
echo "------------------------------------\n";
try {
    $get_zakupki_data_method = $reflection->getMethod('get_zakupki_data');
    $get_zakupki_data_method->setAccessible(true);
    $zakupki_data = $get_zakupki_data_method->invoke($plugin, $test_inn);
    
    if ($zakupki_data && !is_wp_error($zakupki_data)) {
        echo "   ✅ Данные о закупках получены:\n";
        echo "   📊 Общее количество контрактов: " . ($zakupki_data['total_contracts'] ?? 'Не указано') . "\n";
        echo "   💰 Общая сумма контрактов: " . number_format($zakupki_data['total_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   📈 Активных контрактов: " . ($zakupki_data['active_contracts'] ?? 'Не указано') . "\n";
        echo "   ✅ Завершенных контрактов: " . ($zakupki_data['completed_contracts'] ?? 'Не указано') . "\n";
        echo "   📊 Средняя сумма контракта: " . number_format($zakupki_data['avg_contract_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   🎯 Репутационный балл: " . ($zakupki_data['reputation_score'] ?? 'Не указан') . "\n";
        echo "   🔍 Источник: " . ($zakupki_data['source'] ?? 'Не указан') . "\n";
        echo "   📅 Последнее обновление: " . ($zakupki_data['last_updated'] ?? 'Не указано') . "\n\n";
        
        // Проверяем тип анализа
        if (isset($zakupki_data['heuristic_analysis']) && $zakupki_data['heuristic_analysis']) {
            echo "   ⚠️ Использован эвристический анализ\n";
        } elseif (isset($zakupki_data['verification'])) {
            echo "   ✅ Проведена верификация отсутствия участия\n";
            echo "   📊 Уровень уверенности: " . ($zakupki_data['verification']['confidence_level'] ?? 0) . "\n";
        } elseif ($zakupki_data['source'] === 'official_api') {
            echo "   ✅ Получены официальные данные\n";
        }
        
        // Проверяем факторы репутации
        if (isset($zakupki_data['reputation_factors'])) {
            echo "\n   📋 Факторы репутации:\n";
            foreach ($zakupki_data['reputation_factors'] as $factor) {
                echo "      - {$factor}\n";
            }
        }
        
        // Проверяем источники
        if (isset($zakupki_data['sources_checked'])) {
            echo "\n   📋 Проверенные источники:\n";
            foreach ($zakupki_data['sources_checked'] as $source_name => $source_info) {
                $status = $source_info['available'] ? '✅ Доступен' : '❌ Недоступен';
                echo "      - {$source_name}: {$status}\n";
            }
        }
        
    } else {
        echo "   ❌ Данные о закупках не получены\n";
        if (is_wp_error($zakupki_data)) {
            echo "   Ошибка: " . $zakupki_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Проверяем анализ ИНН
echo "2️⃣ АНАЛИЗ ИНН ДЛЯ ЗАКУПОК:\n";
echo "---------------------------\n";
if (isset($zakupki_data['inn_analysis'])) {
    $inn_analysis = $zakupki_data['inn_analysis'];
    echo "   📊 Регион: " . ($inn_analysis['region_code'] ?? 'Не указан') . "\n";
    echo "   📈 Фактор закупок: " . ($inn_analysis['zakupki_factor'] ?? 'Не указан') . "\n";
    echo "   🔍 Оценочный ОКВЭД: " . ($inn_analysis['estimated_okved'] ?? 'Не указан') . "\n";
    echo "   ⚡ Фактор активности: " . ($inn_analysis['activity_factor'] ?? 'Не указан') . "\n";
    echo "   📏 Длина ИНН: " . ($inn_analysis['length'] ?? 'Не указана') . "\n";
    
    // Анализируем результат
    $zakupki_factor = $inn_analysis['zakupki_factor'] ?? 0;
    $activity_factor = $inn_analysis['activity_factor'] ?? 0;
    $combined_factor = ($zakupki_factor + $activity_factor) / 2;
    
    echo "\n   🔍 АНАЛИЗ РЕЗУЛЬТАТА:\n";
    echo "      - Комбинированный фактор: " . round($combined_factor, 2) . "\n";
    
    if ($combined_factor < 0.3) {
        echo "      - ✅ ПРАВИЛЬНО: Низкий фактор = отсутствие участия в закупках\n";
    } elseif ($combined_factor < 0.5) {
        echo "      - ⚠️ СРЕДНИЙ: Возможно ограниченное участие\n";
    } else {
        echo "      - ❌ ВЫСОКИЙ: Ожидается участие в закупках\n";
    }
} else {
    echo "   ❌ Анализ ИНН не проведен\n";
}
echo "\n";

// 3. Проверяем полный анализ компании
echo "3️⃣ ПОЛНЫЙ АНАЛИЗ КОМПАНИИ:\n";
echo "==========================\n";
try {
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($company_data && !is_wp_error($company_data)) {
        echo "   ✅ Базовые данные получены\n";
        
        // Добавляем данные о закупках
        $company_data['zakupki'] = $zakupki_data;
        
        // Рассчитываем рейтинг
        $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
        $calculate_rating_method->setAccessible(true);
        $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
        
        if ($rating_result && isset($rating_result['factors']['zakupki'])) {
            $zakupki_factor = $rating_result['factors']['zakupki'];
            echo "   ✅ Фактор закупок в рейтинге:\n";
            echo "   📊 Название: {$zakupki_factor['name']}\n";
            echo "   📈 Балл: {$zakupki_factor['score']}/{$zakupki_factor['max_score']}\n";
            echo "   📝 Описание: {$zakupki_factor['description']}\n\n";
        } else {
            echo "   ❌ Фактор закупок не найден в рейтинге\n";
        }
        
    } else {
        echo "   ❌ Базовые данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Проверяем AJAX ответ
echo "4️⃣ ПРОВЕРКА AJAX ОТВЕТА:\n";
echo "========================\n";
try {
    // Имитируем AJAX запрос
    $_POST['action'] = 'crc_get_company_rating';
    $_POST['inn'] = $test_inn;
    $_POST['nonce'] = wp_create_nonce('crc_nonce');
    
    // Включаем буферизацию
    ob_start();
    
    // Вызываем AJAX обработчик
    $plugin->ajax_get_company_rating();
    
    $response = ob_get_clean();
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "   ✅ AJAX запрос успешен\n";
        
        // Проверяем наличие данных о закупках в ответе
        if (isset($data['data']['company']['zakupki'])) {
            echo "   ✅ Данные о закупках присутствуют в AJAX ответе\n";
            $zakupki_ajax = $data['data']['company']['zakupki'];
            echo "   📊 Контрактов в AJAX: " . ($zakupki_ajax['total_contracts'] ?? 0) . "\n";
            echo "   💰 Сумма в AJAX: " . number_format($zakupki_ajax['total_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
            echo "   🔍 Источник в AJAX: " . ($zakupki_ajax['source'] ?? 'Не указан') . "\n";
            
            // Проверяем правильность результата
            if (($zakupki_ajax['total_contracts'] ?? 0) == 0) {
                echo "   ✅ ПРАВИЛЬНО: Компания не участвует в закупках\n";
            } else {
                echo "   ❌ НЕПРАВИЛЬНО: Показываются контракты, которых нет\n";
            }
        } else {
            echo "   ❌ Данные о закупках отсутствуют в AJAX ответе\n";
        }
        
    } else {
        echo "   ❌ AJAX запрос неуспешен\n";
        echo "   Ответ: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ТЕСТ ИСПРАВЛЕННОЙ ЛОГИКИ ЗАКУПОК ЗАВЕРШЕН!\n";
echo "=============================================\n";
?>
