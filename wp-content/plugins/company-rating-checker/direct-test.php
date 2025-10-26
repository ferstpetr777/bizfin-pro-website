<?php
/**
 * Прямой тест методов расчета рейтинга
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>🎯 Прямой тест методов расчета рейтинга</h2>\n";

$plugin = new CompanyRatingChecker();
$test_inn = '5260482041';

// Создаем тестовые данные компании
$test_company_data = array(
    'name' => array('full' => 'ООО "Тестовая компания"'),
    'inn' => $test_inn,
    'ogrn' => '1234567890123',
    'state' => array(
        'status' => 'ACTIVE',
        'registration_date' => 1262304000000 // 2010-01-01
    ),
    'capital' => array('value' => 10000000),
    'employee_count' => 50,
    'okved' => '62.01',
    'address' => array(
        'value' => 'г. Москва, ул. Тестовая, д. 1',
        'data' => array('region' => 'Москва')
    )
);

echo "<h3>1. Тестирование получения арбитражных данных:</h3>\n";
try {
    // Используем рефлексию для доступа к приватному методу
    $reflection = new ReflectionClass($plugin);
    $arbitration_method = $reflection->getMethod('get_arbitration_data');
    $arbitration_method->setAccessible(true);
    
    $arbitration_data = $arbitration_method->invoke($plugin, $test_inn);
    if ($arbitration_data) {
        echo "<p style='color: green;'>✅ Арбитражные данные получены</p>\n";
        echo "<p>Данные: " . json_encode($arbitration_data, JSON_UNESCAPED_UNICODE) . "</p>\n";
        $test_company_data['arbitration'] = $arbitration_data;
    } else {
        echo "<p style='color: red;'>❌ Арбитражные данные не получены</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка получения арбитражных данных: " . $e->getMessage() . "</p>\n";
}

echo "<h3>2. Тестирование получения данных о закупках:</h3>\n";
try {
    // Используем рефлексию для доступа к приватному методу
    $zakupki_method = $reflection->getMethod('get_zakupki_data');
    $zakupki_method->setAccessible(true);
    
    $zakupki_data = $zakupki_method->invoke($plugin, $test_inn);
    if ($zakupki_data) {
        echo "<p style='color: green;'>✅ Данные о закупках получены</p>\n";
        echo "<p>Данные: " . json_encode($zakupki_data, JSON_UNESCAPED_UNICODE) . "</p>\n";
        $test_company_data['zakupki'] = $zakupki_data;
    } else {
        echo "<p style='color: red;'>❌ Данные о закупках не получены</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка получения данных о закупках: " . $e->getMessage() . "</p>\n";
}

echo "<h3>3. Тестирование расчета рейтинга:</h3>\n";
try {
    // Используем рефлексию для доступа к приватному методу
    $reflection = new ReflectionClass($plugin);
    $method = $reflection->getMethod('calculate_company_rating');
    $method->setAccessible(true);
    
    $rating = $method->invoke($plugin, $test_company_data);
    
    echo "<p style='color: green;'>✅ Расчет рейтинга выполнен</p>\n";
    echo "<p><strong>Максимальный балл:</strong> {$rating['max_score']}</p>\n";
    echo "<p><strong>Общий балл:</strong> {$rating['total_score']}</p>\n";
    echo "<p><strong>Количество факторов:</strong> " . count($rating['factors']) . "</p>\n";
    
    echo "<h4>Список всех факторов:</h4>\n";
    echo "<ul>\n";
    foreach ($rating['factors'] as $key => $factor) {
        $is_new = in_array($key, ['arbitration', 'zakupki']);
        $new_badge = $is_new ? ' <span style="background: #007cba; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">НОВЫЙ</span>' : '';
        echo "<li><strong>{$factor['name']}</strong>{$new_badge}: {$factor['score']}/{$factor['max_score']} - {$factor['description']}</li>\n";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка расчета рейтинга: " . $e->getMessage() . "</p>\n";
    echo "<p>Стек вызовов:</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<h3>4. Проверка версии файла:</h3>\n";
$file_time = filemtime('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php');
echo "<p>Время изменения файла: " . date('Y-m-d H:i:s', $file_time) . "</p>\n";

// Проверяем содержимое файла
$file_content = file_get_contents('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php');
if (strpos($file_content, 'calculate_arbitration_score') !== false) {
    echo "<p style='color: green;'>✅ Метод calculate_arbitration_score найден в файле</p>\n";
} else {
    echo "<p style='color: red;'>❌ Метод calculate_arbitration_score НЕ найден в файле</p>\n";
}

if (strpos($file_content, 'calculate_zakupki_score') !== false) {
    echo "<p style='color: green;'>✅ Метод calculate_zakupki_score найден в файле</p>\n";
} else {
    echo "<p style='color: red;'>❌ Метод calculate_zakupki_score НЕ найден в файле</p>\n";
}

if (strpos($file_content, 'max_score = 120') !== false) {
    echo "<p style='color: green;'>✅ max_score = 120 найден в файле</p>\n";
} else {
    echo "<p style='color: red;'>❌ max_score = 120 НЕ найден в файле</p>\n";
}
?>
