<?php
/**
 * Отладка данных о государственных закупках
 * Company Rating Checker - Debug Zakupki Data
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ОТЛАДКА ДАННЫХ О ГОСЗАКУПКАХ\n";
echo "================================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПРОВЕРКА ДАННЫХ О ЗАКУПКАХ...\n";
echo "=================================\n\n";

// 1. Проверяем метод get_zakupki_data
echo "1️⃣ ПРЯМОЙ ВЫЗОВ get_zakupki_data:\n";
echo "----------------------------------\n";
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
        
        // Проверяем источники
        if (isset($zakupki_data['sources_checked'])) {
            echo "   📋 Проверенные источники:\n";
            foreach ($zakupki_data['sources_checked'] as $source_name => $source_info) {
                $status = $source_info['available'] ? '✅ Доступен' : '❌ Недоступен';
                echo "      - {$source_name}: {$status} ({$source_info['url']})\n";
            }
        }
        
        // Проверяем факторы репутации
        if (isset($zakupki_data['reputation_factors'])) {
            echo "\n   📋 Факторы репутации:\n";
            foreach ($zakupki_data['reputation_factors'] as $factor) {
                echo "      - {$factor}\n";
            }
        }
        
        // Проверяем контракты
        if (isset($zakupki_data['contracts']) && is_array($zakupki_data['contracts'])) {
            echo "\n   📋 Детали контрактов:\n";
            foreach ($zakupki_data['contracts'] as $i => $contract) {
                echo "      Контракт " . ($i + 1) . ":\n";
                echo "        - Номер: " . ($contract['number'] ?? 'Не указан') . "\n";
                echo "        - Сумма: " . number_format($contract['amount'] ?? 0, 0, ',', ' ') . " руб.\n";
                echo "        - Статус: " . ($contract['status'] ?? 'Не указан') . "\n";
                echo "        - Заказчик: " . ($contract['customer'] ?? 'Не указан') . "\n";
            }
        } else {
            echo "\n   ⚠️ Детали контрактов отсутствуют\n";
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

// 2. Проверяем настройки закупок
echo "2️⃣ НАСТРОЙКИ ЗАКУПОК:\n";
echo "---------------------\n";
echo "   🔧 Закупки включены: " . (get_option('crc_zakupki_enabled', 1) ? 'ДА' : 'НЕТ') . "\n";
echo "   🔑 API ключ закупок: " . (get_option('crc_zakupki_api_key', '') ? 'Установлен' : 'НЕ установлен') . "\n";
echo "   📊 Режим отладки: " . (get_option('crc_debug_mode', 0) ? 'Включен' : 'Отключен') . "\n\n";

// 3. Проверяем прямые запросы к источникам
echo "3️⃣ ПРОВЕРКА ПРЯМЫХ ЗАПРОСОВ К ИСТОЧНИКАМ:\n";
echo "==========================================\n";

// Проверяем zakupki.gov.ru
echo "   🔍 Проверка zakupki.gov.ru:\n";
$zakupki_url = 'https://zakupki.gov.ru/epz/order/quicksearch/search.html';
$zakupki_response = wp_remote_get($zakupki_url, array('timeout' => 10, 'sslverify' => false));
if (!is_wp_error($zakupki_response)) {
    $code = wp_remote_retrieve_response_code($zakupki_response);
    echo "      - Код ответа: {$code}\n";
    if ($code === 200) {
        echo "      - ✅ Сайт доступен\n";
    } else {
        echo "      - ❌ Сайт недоступен (код: {$code})\n";
    }
} else {
    echo "      - ❌ Ошибка подключения: " . $zakupki_response->get_error_message() . "\n";
}

// Проверяем clearspending.ru
echo "\n   🔍 Проверка clearspending.ru:\n";
$clearspending_url = 'https://clearspending.ru/';
$clearspending_response = wp_remote_get($clearspending_url, array('timeout' => 10, 'sslverify' => false));
if (!is_wp_error($clearspending_response)) {
    $code = wp_remote_retrieve_response_code($clearspending_response);
    echo "      - Код ответа: {$code}\n";
    if ($code === 200) {
        echo "      - ✅ Сайт доступен\n";
    } else {
        echo "      - ❌ Сайт недоступен (код: {$code})\n";
    }
} else {
    echo "      - ❌ Ошибка подключения: " . $clearspending_response->get_error_message() . "\n";
}

// Проверяем goszakupki.ru
echo "\n   🔍 Проверка goszakupki.ru:\n";
$goszakupki_url = 'https://goszakupki.ru/';
$goszakupki_response = wp_remote_get($goszakupki_url, array('timeout' => 10, 'sslverify' => false));
if (!is_wp_error($goszakupki_response)) {
    $code = wp_remote_retrieve_response_code($goszakupki_response);
    echo "      - Код ответа: {$code}\n";
    if ($code === 200) {
        echo "      - ✅ Сайт доступен\n";
    } else {
        echo "      - ❌ Сайт недоступен (код: {$code})\n";
    }
} else {
    echo "      - ❌ Ошибка подключения: " . $goszakupki_response->get_error_message() . "\n";
}

echo "\n";

// 4. Проверяем эвристический анализ
echo "4️⃣ ПРОВЕРКА ЭВРИСТИЧЕСКОГО АНАЛИЗА:\n";
echo "====================================\n";
echo "   🔍 Анализ ИНН для закупок:\n";
echo "      - ИНН: {$test_inn}\n";
echo "      - Длина: " . strlen($test_inn) . " цифр\n";
echo "      - Регион: " . substr($test_inn, 0, 2) . "\n";
echo "      - ОКВЭД фактор: " . (substr($test_inn, 0, 2) == '52' ? 'IT/Технологии' : 'Другие отрасли') . "\n";
echo "      - Активность фактор: " . (strlen($test_inn) == 10 ? 'Юридическое лицо' : 'ИП') . "\n\n";

// 5. Проверяем полный анализ компании
echo "5️⃣ ПОЛНЫЙ АНАЛИЗ КОМПАНИИ:\n";
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

// 6. Проверяем AJAX ответ
echo "6️⃣ ПРОВЕРКА AJAX ОТВЕТА:\n";
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
        } else {
            echo "   ❌ Данные о закупках отсутствуют в AJAX ответе\n";
        }
        
        // Проверяем факторы в рейтинге
        if (isset($data['data']['rating']['factors']['zakupki'])) {
            echo "   ✅ Фактор закупок присутствует в рейтинге AJAX\n";
        } else {
            echo "   ❌ Фактор закупок отсутствует в рейтинге AJAX\n";
        }
        
    } else {
        echo "   ❌ AJAX запрос неуспешен\n";
        echo "   Ответ: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения отладки: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ОТЛАДКА ДАННЫХ О ЗАКУПКАХ ЗАВЕРШЕНА!\n";
echo "=======================================\n";
?>
