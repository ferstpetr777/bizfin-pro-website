<?php
/**
 * Комплексный E2E тест всех источников данных
 * Company Rating Checker - Comprehensive End-to-End Test
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

echo "🔍 КОМПЛЕКСНЫЙ E2E ТЕСТ ВСЕХ ИСТОЧНИКОВ ДАННЫХ\n";
echo "===============================================\n\n";

// Тестовый ИНН
$test_inn = '5260482041';

echo "📋 Тестовый ИНН: {$test_inn}\n";
echo "⏰ Время начала теста: " . date('Y-m-d H:i:s') . "\n\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Используем рефлексию для доступа к приватным методам
$reflection = new ReflectionClass($plugin);

echo "🚀 ЗАПУСК КОМПЛЕКСНОГО АНАЛИЗА...\n";
echo "==================================\n\n";

// Получаем полные данные компании через AJAX метод
$ajax_method = $reflection->getMethod('ajax_get_company_rating');
$ajax_method->setAccessible(true);

// Симулируем AJAX запрос
$_POST['inn'] = $test_inn;

// Захватываем вывод
ob_start();
try {
    $ajax_method->invoke($plugin);
    $output = ob_get_clean();
    
    // Парсим JSON ответ
    $response = json_decode($output, true);
    
    if ($response && isset($response['success']) && $response['success']) {
        $company_data = $response['data']['company'];
        $rating_data = $response['data']['rating'];
        
        echo "✅ КОМПЛЕКСНЫЙ АНАЛИЗ ЗАВЕРШЕН УСПЕШНО!\n\n";
        
        // Выводим детальную информацию по каждому источнику
        echo "📊 ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ ПО ИСТОЧНИКАМ:\n";
        echo "=====================================\n\n";
        
        // 1. Базовые данные (DaData)
        echo "1️⃣ БАЗОВЫЕ ДАННЫЕ (DaData API):\n";
        echo "-------------------------------\n";
        if (isset($company_data['basic'])) {
            $basic = $company_data['basic'];
            echo "   ✅ Название: " . ($basic['name'] ?? 'Не указано') . "\n";
            echo "   ✅ Адрес: " . ($basic['address'] ?? 'Не указан') . "\n";
            echo "   ✅ ОКВЭД: " . ($basic['okved'] ?? 'Не указан') . "\n";
            echo "   ✅ Статус: " . ($basic['status'] ?? 'Не указан') . "\n";
            echo "   ✅ Руководитель: " . ($basic['manager'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 2. ЕГРЮЛ данные
        echo "2️⃣ ЕГРЮЛ ДАННЫЕ:\n";
        echo "----------------\n";
        if (isset($company_data['egrul'])) {
            $egrul = $company_data['egrul'];
            echo "   ✅ Статус: " . ($egrul['status'] ?? 'Не указан') . "\n";
            echo "   ✅ Руководитель: " . ($egrul['manager'] ?? 'Не указан') . "\n";
            echo "   ✅ Дата регистрации: " . ($egrul['registration_date'] ?? 'Не указана') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 3. МСП данные
        echo "3️⃣ МСП ДАННЫЕ:\n";
        echo "--------------\n";
        if (isset($company_data['msp'])) {
            $msp = $company_data['msp'];
            echo "   ✅ Статус: " . ($msp['status'] ?? 'Не указан') . "\n";
            echo "   ✅ Категория: " . ($msp['category'] ?? 'Не указана') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 4. Арбитражные данные
        echo "4️⃣ АРБИТРАЖНЫЕ ДАННЫЕ:\n";
        echo "----------------------\n";
        if (isset($company_data['arbitration'])) {
            $arbitration = $company_data['arbitration'];
            echo "   ✅ Уровень риска: " . ($arbitration['risk_level'] ?? 'Не указан') . "\n";
            echo "   ✅ Балл риска: " . ($arbitration['risk_score'] ?? 'Не указан') . "/100\n";
            echo "   ✅ Источник: " . ($arbitration['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 5. Госзакупки
        echo "5️⃣ ГОСУДАРСТВЕННЫЕ ЗАКУПКИ:\n";
        echo "---------------------------\n";
        if (isset($company_data['zakupki'])) {
            $zakupki = $company_data['zakupki'];
            echo "   ✅ Репутация: " . ($zakupki['reputation'] ?? 'Не указана') . "\n";
            echo "   ✅ Количество контрактов: " . ($zakupki['contracts_count'] ?? 'Не указано') . "\n";
            echo "   ✅ Общая сумма: " . number_format($zakupki['total_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
            echo "   ✅ Источник: " . ($zakupki['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 6. ФНС данные
        echo "6️⃣ ФНС ДАННЫЕ:\n";
        echo "--------------\n";
        if (isset($company_data['fns'])) {
            $fns = $company_data['fns'];
            echo "   ✅ Выручка: " . number_format($fns['revenue'] ?? 0, 0, ',', ' ') . " руб.\n";
            echo "   ✅ Рентабельность: " . ($fns['profitability'] ?? 'Не указана') . "%\n";
            echo "   ✅ Коэффициент задолженности: " . ($fns['debt_ratio'] ?? 'Не указан') . "%\n";
            echo "   ✅ Риск банкротства: " . ($fns['bankruptcy_risk'] ?? 'Не указан') . "\n";
            echo "   ✅ Источник: " . ($fns['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 7. Росстат данные
        echo "7️⃣ РОССТАТ ДАННЫЕ:\n";
        echo "------------------\n";
        if (isset($company_data['rosstat'])) {
            $rosstat = $company_data['rosstat'];
            if (isset($rosstat['region'])) {
                echo "   ✅ Регион: " . ($rosstat['region']['region_name'] ?? 'Не указан') . "\n";
                echo "   ✅ Региональный рейтинг: " . ($rosstat['region']['statistical_rating'] ?? 'Не указан') . "/10\n";
            }
            if (isset($rosstat['sector'])) {
                echo "   ✅ Отрасль: " . ($rosstat['sector']['sector_name'] ?? 'Не указана') . "\n";
                echo "   ✅ Отраслевой рейтинг: " . ($rosstat['sector']['sector_rating'] ?? 'Не указан') . "/10\n";
            }
            if (isset($rosstat['enterprise_size'])) {
                echo "   ✅ Размер предприятия: " . ($rosstat['enterprise_size']['size_category'] ?? 'Не указан') . "\n";
            }
            echo "   ✅ Источник: " . ($rosstat['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 8. ЕФРСБ данные
        echo "8️⃣ ЕФРСБ ДАННЫЕ:\n";
        echo "----------------\n";
        if (isset($company_data['efrsb'])) {
            $efrsb = $company_data['efrsb'];
            echo "   ✅ Статус банкротства: " . ($efrsb['bankruptcy_status'] ?? 'Не указан') . "\n";
            echo "   ✅ Уровень риска: " . ($efrsb['bankruptcy_risk_level'] ?? 'Не указан') . "\n";
            echo "   ✅ Балл риска: " . ($efrsb['bankruptcy_risk_score'] ?? 'Не указан') . "/100\n";
            echo "   ✅ Количество дел: " . count($efrsb['bankruptcy_cases'] ?? []) . "\n";
            echo "   ✅ Источник: " . ($efrsb['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 9. РНП данные
        echo "9️⃣ РНП ДАННЫЕ:\n";
        echo "--------------\n";
        if (isset($company_data['rnp'])) {
            $rnp = $company_data['rnp'];
            echo "   ✅ Недобросовестный поставщик: " . ($rnp['is_dishonest_supplier'] ? 'Да' : 'Нет') . "\n";
            echo "   ✅ Количество нарушений: " . ($rnp['violation_count'] ?? 0) . "\n";
            echo "   ✅ Репутационное воздействие: " . ($rnp['reputation_impact'] ?? 'Не указано') . "\n";
            echo "   ✅ Источник: " . ($rnp['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 10. ФССП данные
        echo "🔟 ФССП ДАННЫЕ:\n";
        echo "---------------\n";
        if (isset($company_data['fssp'])) {
            $fssp = $company_data['fssp'];
            echo "   ✅ Исполнительные производства: " . ($fssp['has_enforcement_proceedings'] ? 'Есть' : 'Нет') . "\n";
            echo "   ✅ Количество производств: " . ($fssp['proceedings_count'] ?? 0) . "\n";
            echo "   ✅ Общая задолженность: " . number_format($fssp['total_debt_amount'] ?? 0, 0, ',', ' ') . " руб.\n";
            echo "   ✅ Финансовый риск: " . ($fssp['financial_risk_level'] ?? 'Не указан') . "\n";
            echo "   ✅ Источник: " . ($fssp['source'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // 11. Расширенная аналитика
        echo "1️⃣1️⃣ РАСШИРЕННАЯ АНАЛИТИКА:\n";
        echo "--------------------------\n";
        if (isset($rating_data['advanced_analytics'])) {
            $analytics = $rating_data['advanced_analytics'];
            echo "   ✅ Финансовое здоровье: " . ($analytics['financial_health']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Операционная эффективность: " . ($analytics['operational_efficiency']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Рыночная позиция: " . ($analytics['market_position']['score'] ?? 'Не указано') . "/100\n";
            echo "   ✅ Общий риск: " . ($analytics['overall_risk']['level'] ?? 'Не указан') . "\n";
        } else {
            echo "   ❌ Данные не получены\n";
        }
        echo "\n";
        
        // ИТОГОВЫЕ РЕЗУЛЬТАТЫ
        echo "🏆 ИТОГОВЫЕ РЕЗУЛЬТАТЫ РЕЙТИНГА:\n";
        echo "===============================\n";
        echo "   📊 Общий балл: " . $rating_data['total_score'] . "/" . $rating_data['max_score'] . "\n";
        echo "   🎯 Рейтинг: " . $rating_data['rating']['level'] . " - " . $rating_data['rating']['description'] . "\n";
        echo "   📈 Процент выполнения: " . round(($rating_data['total_score'] / $rating_data['max_score']) * 100, 2) . "%\n\n";
        
        // ДЕТАЛЬНЫЕ ФАКТОРЫ
        echo "📋 ДЕТАЛЬНЫЕ ФАКТОРЫ РЕЙТИНГА:\n";
        echo "=============================\n";
        foreach ($rating_data['factors'] as $key => $factor) {
            $percentage = round(($factor['score'] / $factor['max_score']) * 100, 1);
            $status_icon = $percentage >= 80 ? '🟢' : ($percentage >= 60 ? '🟡' : '🔴');
            echo "   {$status_icon} {$factor['name']}: {$factor['score']}/{$factor['max_score']} ({$percentage}%)\n";
            echo "      📝 {$factor['description']}\n\n";
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
        
    } else {
        echo "❌ ОШИБКА ПРИ ВЫПОЛНЕНИИ КОМПЛЕКСНОГО АНАЛИЗА\n";
        if (isset($response['data'])) {
            echo "   Ошибка: " . $response['data'] . "\n";
        }
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
}

echo "\n⏰ Время завершения теста: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 КОМПЛЕКСНЫЙ E2E ТЕСТ ЗАВЕРШЕН!\n";
echo "================================\n";
?>
