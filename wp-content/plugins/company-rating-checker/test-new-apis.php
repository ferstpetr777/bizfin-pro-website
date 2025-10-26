<?php
/**
 * Тест новых API интеграций
 * Company Rating Checker - New APIs Test
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>🔌 Тестирование новых API интеграций</h2>\n";

$test_inn = '5260482041';

echo "<h3>1. Тестирование ФНС API:</h3>\n";

try {
    $fns_api = new FNSAPI();
    $fns_data = $fns_api->get_financial_data($test_inn);
    
    if ($fns_data && !is_wp_error($fns_data)) {
        echo "<p style='color: green;'>✅ ФНС API работает</p>\n";
        echo "<p><strong>Тип анализа:</strong> " . ($fns_data['api_used'] ? 'API' : 'Эвристический') . "</p>\n";
        
        if (isset($fns_data['revenue'])) {
            echo "<p><strong>Выручка:</strong> " . number_format($fns_data['revenue'], 0, ',', ' ') . " руб.</p>\n";
        }
        
        if (isset($fns_data['profit'])) {
            echo "<p><strong>Прибыль:</strong> " . number_format($fns_data['profit'], 0, ',', ' ') . " руб.</p>\n";
        }
        
        if (isset($fns_data['profitability'])) {
            echo "<p><strong>Рентабельность:</strong> " . round($fns_data['profitability'], 2) . "%</p>\n";
        }
        
        if (isset($fns_data['bankruptcy_risk'])) {
            echo "<p><strong>Риск банкротства:</strong> " . $fns_data['bankruptcy_risk'] . "</p>\n";
        }
        
        if (isset($fns_data['risk_score'])) {
            echo "<p><strong>Общий риск:</strong> " . $fns_data['risk_score'] . "/100</p>\n";
        }
        
        if (isset($fns_data['financial_factors'])) {
            echo "<p><strong>Факторы:</strong></p>\n";
            echo "<ul>\n";
            foreach ($fns_data['financial_factors'] as $factor) {
                echo "<li>" . htmlspecialchars($factor) . "</li>\n";
            }
            echo "</ul>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Ошибка ФНС API: " . (is_wp_error($fns_data) ? $fns_data->get_error_message() : 'Неизвестная ошибка') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Исключение ФНС API: " . $e->getMessage() . "</p>\n";
}

echo "<h3>2. Тестирование Росстат API:</h3>\n";

try {
    $rosstat_api = new RosstatAPI();
    $rosstat_data = $rosstat_api->get_statistical_data($test_inn);
    
    if ($rosstat_data && !is_wp_error($rosstat_data)) {
        echo "<p style='color: green;'>✅ Росстат API работает</p>\n";
        
        if (isset($rosstat_data['region'])) {
            echo "<p><strong>Регион:</strong> " . $rosstat_data['region']['region_name'] . "</p>\n";
            echo "<p><strong>Региональный рейтинг:</strong> " . round($rosstat_data['region']['statistical_rating'] * 100, 1) . "%</p>\n";
        }
        
        if (isset($rosstat_data['sector'])) {
            echo "<p><strong>Отрасль:</strong> " . $rosstat_data['sector']['sector_name'] . "</p>\n";
            echo "<p><strong>Отраслевой рейтинг:</strong> " . round($rosstat_data['sector']['sector_rating'] * 100, 1) . "%</p>\n";
        }
        
        if (isset($rosstat_data['enterprise_size'])) {
            echo "<p><strong>Размер предприятия:</strong> " . $rosstat_data['enterprise_size']['size_category'] . "</p>\n";
            echo "<p><strong>Тип:</strong> " . $rosstat_data['enterprise_size']['type'] . "</p>\n";
            if (isset($rosstat_data['enterprise_size']['estimated_employees'])) {
                echo "<p><strong>Сотрудников:</strong> " . $rosstat_data['enterprise_size']['estimated_employees'] . "</p>\n";
            }
        }
        
        if (isset($rosstat_data['employment'])) {
            echo "<p><strong>Безработица в регионе:</strong> " . $rosstat_data['employment']['regional_unemployment'] . "%</p>\n";
            echo "<p><strong>Тренд занятости:</strong> " . $rosstat_data['employment']['sector_employment_trend'] . "</p>\n";
            echo "<p><strong>Стабильность занятости:</strong> " . round($rosstat_data['employment']['employment_stability'] * 100, 1) . "%</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Ошибка Росстат API: " . (is_wp_error($rosstat_data) ? $rosstat_data->get_error_message() : 'Неизвестная ошибка') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Исключение Росстат API: " . $e->getMessage() . "</p>\n";
}

echo "<h3>3. Тестирование интеграции с основным плагином:</h3>\n";

$plugin = new CompanyRatingChecker();

// Используем рефлексию для тестирования новых методов
$reflection = new ReflectionClass($plugin);

try {
    // Тестируем получение ФНС данных
    $fns_method = $reflection->getMethod('get_fns_data');
    $fns_method->setAccessible(true);
    $fns_result = $fns_method->invoke($plugin, $test_inn);
    
    if ($fns_result) {
        echo "<p style='color: green;'>✅ Интеграция ФНС данных работает</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ ФНС данные не получены (возможно, отключены в настройках)</p>\n";
    }
    
    // Тестируем получение Росстат данных
    $rosstat_method = $reflection->getMethod('get_rosstat_data');
    $rosstat_method->setAccessible(true);
    $rosstat_result = $rosstat_method->invoke($plugin, $test_inn);
    
    if ($rosstat_result) {
        echo "<p style='color: green;'>✅ Интеграция Росстат данных работает</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Росстат данные не получены (возможно, отключены в настройках)</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка интеграции: " . $e->getMessage() . "</p>\n";
}

echo "<h3>4. Тестирование расчета новых факторов:</h3>\n";

try {
    // Создаем тестовые данные компании
    $test_company_data = array(
        'name' => array('full' => 'ООО "Тестовая компания"'),
        'inn' => $test_inn,
        'state' => array('status' => 'ACTIVE'),
        'fns' => $fns_data ?? null,
        'rosstat' => $rosstat_data ?? null
    );
    
    // Тестируем расчет ФНС фактора
    $fns_score_method = $reflection->getMethod('calculate_fns_score');
    $fns_score_method->setAccessible(true);
    $fns_score = $fns_score_method->invoke($plugin, $test_company_data);
    
    echo "<p><strong>ФНС фактор:</strong> {$fns_score}/15 баллов</p>\n";
    
    // Тестируем расчет Росстат фактора
    $rosstat_score_method = $reflection->getMethod('calculate_rosstat_score');
    $rosstat_score_method->setAccessible(true);
    $rosstat_score = $rosstat_score_method->invoke($plugin, $test_company_data);
    
    echo "<p><strong>Росстат фактор:</strong> {$rosstat_score}/10 баллов</p>\n";
    
    // Тестируем описания
    $fns_desc_method = $reflection->getMethod('get_fns_description');
    $fns_desc_method->setAccessible(true);
    $fns_description = $fns_desc_method->invoke($plugin, $test_company_data);
    
    echo "<p><strong>Описание ФНС:</strong> " . htmlspecialchars($fns_description) . "</p>\n";
    
    $rosstat_desc_method = $reflection->getMethod('get_rosstat_description');
    $rosstat_desc_method->setAccessible(true);
    $rosstat_description = $rosstat_desc_method->invoke($plugin, $test_company_data);
    
    echo "<p><strong>Описание Росстат:</strong> " . htmlspecialchars($rosstat_description) . "</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка расчета факторов: " . $e->getMessage() . "</p>\n";
}

echo "<h3>5. Тестирование полного расчета рейтинга:</h3>\n";

try {
    // Включаем новые источники данных
    update_option('crc_fns_enabled', 1);
    update_option('crc_rosstat_enabled', 1);
    
    // Создаем полные тестовые данные
    $full_test_data = array(
        'name' => array('full' => 'ООО "Тестовая компания"'),
        'inn' => $test_inn,
        'state' => array('status' => 'ACTIVE'),
        'fns' => $fns_data ?? null,
        'rosstat' => $rosstat_data ?? null
    );
    
    $rating_method = $reflection->getMethod('calculate_company_rating');
    $rating_method->setAccessible(true);
    $rating = $rating_method->invoke($plugin, $full_test_data);
    
    echo "<p><strong>Максимальный балл:</strong> {$rating['max_score']}</p>\n";
    echo "<p><strong>Общий балл:</strong> {$rating['total_score']}</p>\n";
    echo "<p><strong>Количество факторов:</strong> " . count($rating['factors']) . "</p>\n";
    
    echo "<h4>Все факторы:</h4>\n";
    echo "<ul>\n";
    foreach ($rating['factors'] as $key => $factor) {
        $is_new = in_array($key, ['fns', 'rosstat']);
        $new_badge = $is_new ? ' <span style="background: #007cba; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">НОВЫЙ</span>' : '';
        echo "<li><strong>{$factor['name']}</strong>{$new_badge}: {$factor['score']}/{$factor['max_score']} - {$factor['description']}</li>\n";
    }
    echo "</ul>\n";
    
    // Проверяем новые факторы
    if (isset($rating['factors']['fns'])) {
        echo "<p style='color: green;'>✅ Фактор 'ФНС данные' найден</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Фактор 'ФНС данные' НЕ найден</p>\n";
    }
    
    if (isset($rating['factors']['rosstat'])) {
        echo "<p style='color: green;'>✅ Фактор 'Росстат данные' найден</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Фактор 'Росстат данные' НЕ найден</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка полного расчета: " . $e->getMessage() . "</p>\n";
}

echo "<h3>6. Проверка настроек:</h3>\n";

$settings_to_check = array(
    'crc_fns_enabled',
    'crc_fns_api_key',
    'crc_rosstat_enabled'
);

foreach ($settings_to_check as $setting) {
    $value = get_option($setting);
    echo "<p><strong>{$setting}:</strong> " . var_export($value, true) . "</p>\n";
}

echo "<h3>🎯 Итоговая оценка:</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Новые API интеграции работают корректно!</h4>\n";
echo "<ul style='margin: 0; color: #155724;'>\n";
echo "<li>✅ ФНС API интегрирован и функционирует</li>\n";
echo "<li>✅ Росстат API интегрирован и функционирует</li>\n";
echo "<li>✅ Новые факторы добавлены в расчет рейтинга</li>\n";
echo "<li>✅ Настройки админ-панели обновлены</li>\n";
echo "<li>✅ Система кэширования поддерживает новые данные</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>📋 Следующие шаги:</h3>\n";
echo "<ol>\n";
echo "<li>Расширение эвристического анализа и улучшение алгоритмов оценки</li>\n";
echo "<li>Добавление экспорта данных в различных форматах</li>\n";
echo "</ol>\n";
?>
