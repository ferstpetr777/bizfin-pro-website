<?php
/**
 * Тест исправлений
 * Company Rating Checker - Test Fixes
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ТЕСТ ИСПРАВЛЕНИЙ\n";
echo "==================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПРОВЕРКА ИСПРАВЛЕНИЙ...\n";
echo "==========================\n\n";

// 1. Проверяем данные ФНС
echo "1️⃣ ПРОВЕРКА ДАННЫХ ФНС:\n";
echo "------------------------\n";
try {
    $get_fns_data_method = $reflection->getMethod('get_fns_data');
    $get_fns_data_method->setAccessible(true);
    $fns_data = $get_fns_data_method->invoke($plugin, $test_inn);
    
    if ($fns_data && !is_wp_error($fns_data)) {
        echo "   ✅ Данные ФНС получены:\n";
        echo "   📊 Выручка: " . number_format($fns_data['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        echo "   📈 Рентабельность: " . ($fns_data['profitability'] ?? 'Не указана') . "%\n";
        echo "   ⚠️ Риск банкротства: " . ($fns_data['bankruptcy_risk'] ?? 'Не указан') . "\n\n";
    } else {
        echo "   ❌ Данные ФНС не получены\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n\n";
}

// 2. Проверяем исправленные данные МСП
echo "2️⃣ ПРОВЕРКА ИСПРАВЛЕННЫХ ДАННЫХ МСП:\n";
echo "------------------------------------\n";
try {
    $get_msp_data_method = $reflection->getMethod('get_msp_data');
    $get_msp_data_method->setAccessible(true);
    $msp_data = $get_msp_data_method->invoke($plugin, $test_inn, $fns_data);
    
    if ($msp_data && !is_wp_error($msp_data)) {
        echo "   ✅ Данные МСП получены:\n";
        echo "   📊 Статус: " . ($msp_data['status'] ?? 'Не указан') . "\n";
        echo "   📈 Категория: " . ($msp_data['category'] ?? 'Не указана') . "\n";
        echo "   🔍 Источник: " . ($msp_data['source'] ?? 'Не указан') . "\n";
        
        if (isset($msp_data['correction_applied']) && $msp_data['correction_applied']) {
            echo "   ✅ Коррекция применена на основе данных ФНС\n";
            echo "   💰 Использованная выручка: " . number_format($msp_data['revenue_used'] ?? 0, 0, ',', ' ') . " руб.\n";
        }
        
        // Проверяем правильность статуса
        $revenue = $fns_data['revenue'] ?? 0;
        $status = $msp_data['status'] ?? '';
        $category = $msp_data['category'] ?? '';
        
        echo "\n   🔍 АНАЛИЗ ПРАВИЛЬНОСТИ СТАТУСА МСП:\n";
        echo "      - Выручка: " . number_format($revenue, 0, ',', ' ') . " руб.\n";
        echo "      - Статус: '{$status}'\n";
        echo "      - Категория: '{$category}'\n";
        
        if ($revenue <= 120000000 && $status === 'micro') {
            echo "      ✅ ПРАВИЛЬНО: Микропредприятие (до 120 млн руб.)\n";
        } elseif ($revenue <= 800000000 && $status === 'small') {
            echo "      ✅ ПРАВИЛЬНО: Малое предприятие (до 800 млн руб.)\n";
        } elseif ($revenue <= 2000000000 && $status === 'medium') {
            echo "      ✅ ПРАВИЛЬНО: Среднее предприятие (до 2 млрд руб.)\n";
        } elseif ($revenue > 2000000000 && $status === 'not_msp') {
            echo "      ✅ ПРАВИЛЬНО: Не является субъектом МСП (свыше 2 млрд руб.)\n";
        } else {
            echo "      ❌ НЕПРАВИЛЬНО: Статус не соответствует выручке\n";
        }
        
    } else {
        echo "   ❌ Данные МСП не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Проверяем полный анализ с исправлениями
echo "3️⃣ ПРОВЕРКА ПОЛНОГО АНАЛИЗА С ИСПРАВЛЕНИЯМИ:\n";
echo "============================================\n";
try {
    // Получаем все данные компании
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($company_data && !is_wp_error($company_data)) {
        echo "   ✅ Данные компании получены\n";
        
        // Рассчитываем рейтинг
        $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
        $calculate_rating_method->setAccessible(true);
        $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
        
        if ($rating_result) {
            echo "   ✅ Рейтинг рассчитан\n";
            echo "   📊 Общий балл: " . $rating_result['total_score'] . "/" . $rating_result['max_score'] . "\n";
            echo "   🎯 Рейтинг: " . $rating_result['rating']['level'] . " - " . $rating_result['rating']['name'] . "\n";
            
            // Проверяем максимальный балл
            $expected_max_score = 195; // Базовый 100 + новые источники 95
            if ($rating_result['max_score'] == $expected_max_score) {
                echo "   ✅ ПРАВИЛЬНО: Максимальный балл = {$expected_max_score}\n";
            } else {
                echo "   ❌ НЕПРАВИЛЬНО: Максимальный балл = {$rating_result['max_score']}, ожидался {$expected_max_score}\n";
            }
            
            // Проверяем факторы ФНС и МСП
            if (isset($rating_result['factors']['fns'])) {
                $fns_factor = $rating_result['factors']['fns'];
                echo "   ✅ Фактор ФНС: {$fns_factor['score']}/{$fns_factor['max_score']} - {$fns_factor['name']}\n";
            } else {
                echo "   ❌ Фактор ФНС отсутствует\n";
            }
            
            if (isset($rating_result['factors']['msp'])) {
                $msp_factor = $rating_result['factors']['msp'];
                echo "   ✅ Фактор МСП: {$msp_factor['score']}/{$msp_factor['max_score']} - {$msp_factor['name']}\n";
                echo "      📝 Описание: {$msp_factor['description']}\n";
            } else {
                echo "   ❌ Фактор МСП отсутствует\n";
            }
            
        } else {
            echo "   ❌ Ошибка расчета рейтинга\n";
        }
        
    } else {
        echo "   ❌ Данные компании не получены\n";
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
        
        // Проверяем максимальный балл в AJAX
        $ajax_max_score = $data['data']['rating']['max_score'] ?? 0;
        if ($ajax_max_score == 195) {
            echo "   ✅ ПРАВИЛЬНО: Максимальный балл в AJAX = {$ajax_max_score}\n";
        } else {
            echo "   ❌ НЕПРАВИЛЬНО: Максимальный балл в AJAX = {$ajax_max_score}, ожидался 195\n";
        }
        
        // Проверяем наличие данных ФНС в AJAX
        if (isset($data['data']['company']['fns'])) {
            echo "   ✅ Данные ФНС присутствуют в AJAX ответе\n";
        } else {
            echo "   ❌ Данные ФНС отсутствуют в AJAX ответе\n";
        }
        
        // Проверяем исправленный статус МСП в AJAX
        if (isset($data['data']['company']['msp'])) {
            $msp_ajax = $data['data']['company']['msp'];
            echo "   ✅ Данные МСП в AJAX: {$msp_ajax['status']} - {$msp_ajax['category']}\n";
        } else {
            echo "   ❌ Данные МСП отсутствуют в AJAX ответе\n";
        }
        
    } else {
        echo "   ❌ AJAX запрос неуспешен\n";
        echo "   Ответ: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ТЕСТ ИСПРАВЛЕНИЙ ЗАВЕРШЕН!\n";
echo "=============================\n";
?>
