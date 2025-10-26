<?php
/**
 * Отладка отображения данных ФНС
 * Company Rating Checker - Debug FNS Display
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ОТЛАДКА ОТОБРАЖЕНИЯ ДАННЫХ ФНС\n";
echo "==================================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПРОВЕРКА ОТОБРАЖЕНИЯ ДАННЫХ ФНС...\n";
echo "======================================\n\n";

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
        echo "   🏦 Коэффициент задолженности: " . ($fns_data['debt_ratio'] ?? 'Не указан') . "%\n";
        echo "   ⚠️ Риск банкротства: " . ($fns_data['bankruptcy_risk'] ?? 'Не указан') . "\n";
        echo "   🔍 Источник: " . ($fns_data['source'] ?? 'Не указан') . "\n";
        echo "   📅 Последнее обновление: " . ($fns_data['last_updated'] ?? 'Не указано') . "\n\n";
    } else {
        echo "   ❌ Данные ФНС не получены\n";
        if (is_wp_error($fns_data)) {
            echo "   Ошибка: " . $fns_data->get_error_message() . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Проверяем полный анализ компании
echo "2️⃣ ПОЛНЫЙ АНАЛИЗ КОМПАНИИ:\n";
echo "==========================\n";
try {
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($company_data && !is_wp_error($company_data)) {
        echo "   ✅ Данные компании получены\n";
        
        // Проверяем наличие данных ФНС в массиве компании
        if (isset($company_data['fns'])) {
            echo "   ✅ Данные ФНС присутствуют в массиве компании\n";
            $fns_company = $company_data['fns'];
            echo "   📊 Выручка в массиве: " . number_format($fns_company['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        } else {
            echo "   ❌ Данные ФНС отсутствуют в массиве компании\n";
        }
        
        // Рассчитываем рейтинг
        $calculate_rating_method = $reflection->getMethod('calculate_company_rating');
        $calculate_rating_method->setAccessible(true);
        $rating_result = $calculate_rating_method->invoke($plugin, $company_data);
        
        if ($rating_result && isset($rating_result['factors']['fns'])) {
            $fns_factor = $rating_result['factors']['fns'];
            echo "   ✅ Фактор ФНС в рейтинге:\n";
            echo "   📊 Название: {$fns_factor['name']}\n";
            echo "   📈 Балл: {$fns_factor['score']}/{$fns_factor['max_score']}\n";
            echo "   📝 Описание: {$fns_factor['description']}\n\n";
        } else {
            echo "   ❌ Фактор ФНС не найден в рейтинге\n";
        }
        
    } else {
        echo "   ❌ Данные компании не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Проверяем AJAX ответ
echo "3️⃣ ПРОВЕРКА AJAX ОТВЕТА:\n";
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
        
        // Проверяем наличие данных ФНС в AJAX ответе
        if (isset($data['data']['company']['fns'])) {
            echo "   ✅ Данные ФНС присутствуют в AJAX ответе\n";
            $fns_ajax = $data['data']['company']['fns'];
            echo "   📊 Выручка в AJAX: " . number_format($fns_ajax['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
            echo "   📈 Рентабельность в AJAX: " . ($fns_ajax['profitability'] ?? 'Не указана') . "%\n";
            echo "   ⚠️ Риск банкротства в AJAX: " . ($fns_ajax['bankruptcy_risk'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные ФНС отсутствуют в AJAX ответе\n";
        }
        
        // Проверяем факторы в рейтинге
        if (isset($data['data']['rating']['factors']['fns'])) {
            echo "   ✅ Фактор ФНС присутствует в рейтинге AJAX\n";
            $fns_factor_ajax = $data['data']['rating']['factors']['fns'];
            echo "   📊 Фактор ФНС: {$fns_factor_ajax['score']}/{$fns_factor_ajax['max_score']} - {$fns_factor_ajax['name']}\n";
            echo "   📝 Описание: {$fns_factor_ajax['description']}\n";
        } else {
            echo "   ❌ Фактор ФНС отсутствует в рейтинге AJAX\n";
        }
        
    } else {
        echo "   ❌ AJAX запрос неуспешен\n";
        echo "   Ответ: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Проверяем JavaScript файл
echo "4️⃣ ПРОВЕРКА JAVASCRIPT ФАЙЛА:\n";
echo "==============================\n";
$js_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/assets/script.js';
if (file_exists($js_file)) {
    echo "   ✅ JavaScript файл существует\n";
    
    // Проверяем, есть ли код для отображения данных ФНС
    $js_content = file_get_contents($js_file);
    if (strpos($js_content, 'company.fns') !== false) {
        echo "   ✅ Код для отображения данных ФНС присутствует в JavaScript\n";
    } else {
        echo "   ❌ Код для отображения данных ФНС отсутствует в JavaScript\n";
    }
    
    if (strpos($js_content, 'ФНС данные') !== false) {
        echo "   ✅ Текст 'ФНС данные' найден в JavaScript\n";
    } else {
        echo "   ❌ Текст 'ФНС данные' не найден в JavaScript\n";
    }
    
} else {
    echo "   ❌ JavaScript файл не найден\n";
}
echo "\n";

// 5. Проверяем настройки плагина
echo "5️⃣ НАСТРОЙКИ ПЛАГИНА:\n";
echo "=====================\n";
echo "   🔧 ФНС включен: " . (get_option('crc_fns_enabled', 1) ? 'ДА' : 'НЕТ') . "\n";
echo "   🔑 ФНС API ключ: " . (get_option('crc_fns_api_key', '') ? 'Установлен' : 'НЕ установлен') . "\n";
echo "   📊 Режим отладки: " . (get_option('crc_debug_mode', 0) ? 'Включен' : 'Отключен') . "\n";
echo "   🎯 Максимальный балл: " . (get_option('crc_max_score', 195)) . "\n\n";

echo "⏰ Время завершения отладки: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ОТЛАДКА ОТОБРАЖЕНИЯ ДАННЫХ ФНС ЗАВЕРШЕНА!\n";
echo "============================================\n";
?>
