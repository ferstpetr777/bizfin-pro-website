<?php
/**
 * Отладка новых факторов
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>🔍 Отладка новых факторов</h2>\n";

$plugin = new CompanyRatingChecker();
$test_inn = '5260482041';

// Имитируем POST запрос
$_POST['inn'] = $test_inn;
$_POST['nonce'] = wp_create_nonce('crc_nonce');

echo "<h3>Тестирование с отладкой:</h3>\n";

// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Захватываем вывод
ob_start();
try {
    $plugin->ajax_get_company_rating();
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && isset($data['success']) && $data['success']) {
        $rating = $data['data']['rating'];
        
        echo "<p><strong>Максимальный балл:</strong> {$rating['max_score']}</p>\n";
        echo "<p><strong>Общий балл:</strong> {$rating['total_score']}</p>\n";
        echo "<p><strong>Количество факторов:</strong> " . count($rating['factors']) . "</p>\n";
        
        echo "<h4>Список факторов:</h4>\n";
        echo "<ul>\n";
        foreach ($rating['factors'] as $key => $factor) {
            echo "<li><strong>{$factor['name']}</strong>: {$factor['score']}/{$factor['max_score']}</li>\n";
        }
        echo "</ul>\n";
        
        // Проверяем новые факторы
        if (isset($rating['factors']['arbitration'])) {
            echo "<p style='color: green;'>✅ Фактор 'Арбитражные риски' найден</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Фактор 'Арбитражные риски' НЕ найден</p>\n";
        }
        
        if (isset($rating['factors']['zakupki'])) {
            echo "<p style='color: green;'>✅ Фактор 'Государственные закупки' найден</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Фактор 'Государственные закупки' НЕ найден</p>\n";
        }
        
        // Проверяем данные компании
        $company = $data['data']['company'];
        if (isset($company['arbitration'])) {
            echo "<p style='color: green;'>✅ Арбитражные данные в компании найдены</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Арбитражные данные в компании НЕ найдены</p>\n";
        }
        
        if (isset($company['zakupki'])) {
            echo "<p style='color: green;'>✅ Данные о закупках в компании найдены</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Данные о закупках в компании НЕ найдены</p>\n";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Ошибка в ответе</p>\n";
        echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Исключение: " . $e->getMessage() . "</p>\n";
    echo "<p>Стек вызовов:</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

// Тестируем классы напрямую
echo "<h3>Тестирование классов напрямую:</h3>\n";

try {
    $arbitration_api = new SimpleArbitrationAPI();
    $arbitration_data = $arbitration_api->get_arbitration_info($test_inn);
    echo "<p style='color: green;'>✅ SimpleArbitrationAPI работает</p>\n";
    echo "<p>Данные: " . json_encode($arbitration_data, JSON_UNESCAPED_UNICODE) . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка SimpleArbitrationAPI: " . $e->getMessage() . "</p>\n";
}

try {
    $zakupki_api = new ZakupkiAPI();
    $zakupki_data = $zakupki_api->get_zakupki_info($test_inn);
    echo "<p style='color: green;'>✅ ZakupkiAPI работает</p>\n";
    echo "<p>Данные: " . json_encode($zakupki_data, JSON_UNESCAPED_UNICODE) . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка ZakupkiAPI: " . $e->getMessage() . "</p>\n";
}
?>
