<?php
/**
 * Отладка данных ФНС
 * Company Rating Checker - Debug FNS Data
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Подключаем основной плагин
require_once('company-rating-checker.php');

echo "🔍 ОТЛАДКА ДАННЫХ ФНС\n";
echo "====================\n\n";

$test_inn = '5260482041';
echo "📋 ИНН для анализа: {$test_inn}\n";
echo "⏰ Время анализа: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();
$reflection = new ReflectionClass($plugin);

echo "🚀 ПРОВЕРКА ДАННЫХ ФНС...\n";
echo "=========================\n\n";

// 1. Проверяем метод get_fns_data
echo "1️⃣ ПРЯМОЙ ВЫЗОВ get_fns_data:\n";
echo "------------------------------\n";
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
        
        // Проверяем структуру данных
        echo "   📋 Структура данных ФНС:\n";
        foreach ($fns_data as $key => $value) {
            if (is_array($value)) {
                echo "      - {$key}: [массив с " . count($value) . " элементами]\n";
            } else {
                echo "      - {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
            }
        }
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
echo "---------------------------\n";
try {
    $get_company_data_method = $reflection->getMethod('get_company_data');
    $get_company_data_method->setAccessible(true);
    $company_data = $get_company_data_method->invoke($plugin, $test_inn);
    
    if ($company_data && !is_wp_error($company_data)) {
        echo "   ✅ Базовые данные получены\n";
        
        // Добавляем ФНС данные
        $company_data['fns'] = $fns_data;
        
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
        
        // Проверяем все факторы
        echo "   📋 Все факторы в рейтинге:\n";
        foreach ($rating_result['factors'] as $key => $factor) {
            echo "      - {$key}: {$factor['name']} ({$factor['score']}/{$factor['max_score']})\n";
        }
        
    } else {
        echo "   ❌ Базовые данные не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Проверяем настройки ФНС
echo "3️⃣ НАСТРОЙКИ ФНС:\n";
echo "------------------\n";
echo "   🔧 ФНС включен: " . (get_option('crc_fns_enabled', 1) ? 'ДА' : 'НЕТ') . "\n";
echo "   🔑 ФНС API ключ: " . (get_option('crc_fns_api_key', '') ? 'Установлен' : 'НЕ установлен') . "\n";
echo "   📊 Режим отладки: " . (get_option('crc_debug_mode', 0) ? 'Включен' : 'Отключен') . "\n\n";

// 4. Проверяем МСП данные
echo "4️⃣ ПРОВЕРКА МСП ДАННЫХ:\n";
echo "------------------------\n";
try {
    $get_msp_data_method = $reflection->getMethod('get_msp_data');
    $get_msp_data_method->setAccessible(true);
    $msp_data = $get_msp_data_method->invoke($plugin, $test_inn);
    
    if ($msp_data && !is_wp_error($msp_data)) {
        echo "   ✅ Данные МСП получены:\n";
        echo "   📊 Статус: " . ($msp_data['status'] ?? 'Не указан') . "\n";
        echo "   📈 Категория: " . ($msp_data['category'] ?? 'Не указана') . "\n";
        echo "   🔍 Источник: " . ($msp_data['source'] ?? 'Не указан') . "\n\n";
        
        // Анализируем логику МСП
        $status = $msp_data['status'] ?? '';
        $category = $msp_data['category'] ?? '';
        
        echo "   🔍 АНАЛИЗ ЛОГИКИ МСП:\n";
        echo "      - Статус: '{$status}'\n";
        echo "      - Категория: '{$category}'\n";
        
        if ($status === 'not_msp') {
            echo "      - ❌ ПРОБЛЕМА: Статус 'not_msp' означает 'НЕ является субъектом МСП'\n";
            echo "      - 💡 НО: По данным ФНС выручка 38,358,715 руб. - это МАЛЫЙ БИЗНЕС!\n";
            echo "      - 🎯 КРИТЕРИИ МСП: до 800 млн руб. выручки = малое предприятие\n";
        } elseif (strpos($category, 'Микропредприятие') !== false) {
            echo "      - ✅ Микропредприятие (до 120 млн руб.)\n";
        } elseif (strpos($category, 'Малое предприятие') !== false) {
            echo "      - ✅ Малое предприятие (до 800 млн руб.)\n";
        } elseif (strpos($category, 'Среднее предприятие') !== false) {
            echo "      - ✅ Среднее предприятие (до 2 млрд руб.)\n";
        }
        
    } else {
        echo "   ❌ Данные МСП не получены\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Проверяем AJAX ответ
echo "5️⃣ СИМУЛЯЦИЯ AJAX ЗАПРОСА:\n";
echo "---------------------------\n";
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
        
        // Проверяем наличие ФНС данных в ответе
        if (isset($data['data']['company']['fns'])) {
            echo "   ✅ ФНС данные присутствуют в AJAX ответе\n";
            $fns_ajax = $data['data']['company']['fns'];
            echo "   📊 Выручка в AJAX: " . number_format($fns_ajax['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
        } else {
            echo "   ❌ ФНС данные ОТСУТСТВУЮТ в AJAX ответе\n";
        }
        
        // Проверяем факторы в рейтинге
        if (isset($data['data']['rating']['factors']['fns'])) {
            echo "   ✅ Фактор ФНС присутствует в рейтинге AJAX\n";
        } else {
            echo "   ❌ Фактор ФНС ОТСУТСТВУЕТ в рейтинге AJAX\n";
        }
        
    } else {
        echo "   ❌ AJAX запрос неуспешен\n";
        echo "   Ответ: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка AJAX: " . $e->getMessage() . "\n";
}

echo "\n⏰ Время завершения отладки: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 ОТЛАДКА ДАННЫХ ФНС ЗАВЕРШЕНА!\n";
echo "================================\n";
?>
