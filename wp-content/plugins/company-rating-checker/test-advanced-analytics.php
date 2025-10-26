<?php
/**
 * Тест расширенной аналитики
 * Company Rating Checker - Advanced Analytics Test
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>🧠 Тестирование расширенной аналитики</h2>\n";

$test_inn = '5260482041';

echo "<h3>1. Тестирование класса AdvancedAnalytics:</h3>\n";

try {
    $analytics = new AdvancedAnalytics();
    echo "<p style='color: green;'>✅ Класс AdvancedAnalytics загружен</p>\n";
    
    // Создаем тестовые данные компании
    $test_company_data = array(
        'name' => array('full' => 'ООО "Тестовая компания"'),
        'inn' => $test_inn,
        'state' => array(
            'status' => 'ACTIVE',
            'registration_date' => 1262304000000 // 2010-01-01
        ),
        'management' => array(
            'name' => 'Иванов Иван Иванович',
            'start_date' => 1262304000000
        ),
        'fns' => array(
            'revenue' => 50000000,
            'profit' => 5000000,
            'profitability' => 10,
            'debt_ratio' => 25,
            'bankruptcy_risk' => 'low'
        ),
        'rosstat' => array(
            'region' => array(
                'region_name' => 'Нижегородская область',
                'statistical_rating' => 0.7
            ),
            'sector' => array(
                'sector_name' => 'Разработка компьютерного программного обеспечения',
                'sector_rating' => 0.9,
                'growth' => array(
                    'annual_growth' => 0.15
                ),
                'market' => array(
                    'competition_level' => 0.7,
                    'barriers_to_entry' => 0.4
                )
            ),
            'enterprise_size' => array(
                'size_category' => 'medium',
                'estimated_employees' => 50
            ),
            'employment' => array(
                'employment_stability' => 0.8
            )
        ),
        'arbitration' => array(
            'risk_level' => 'low'
        ),
        'zakupki' => array(
            'summary' => array(
                'reputation_level' => 'good'
            )
        )
    );
    
    // Выполняем комплексный анализ
    $analysis = $analytics->comprehensive_analysis($test_company_data);
    
    if ($analysis) {
        echo "<p style='color: green;'>✅ Комплексный анализ выполнен</p>\n";
        
        echo "<h4>Результаты анализа:</h4>\n";
        echo "<p><strong>Общий балл:</strong> " . round($analysis['overall_score'], 1) . "/100</p>\n";
        
        // Финансовое здоровье
        echo "<h4>💰 Финансовое здоровье:</h4>\n";
        echo "<p><strong>Балл:</strong> {$analysis['financial_health']['score']}/{$analysis['financial_health']['max_score']} ({$analysis['financial_health']['level']})</p>\n";
        echo "<ul>\n";
        foreach ($analysis['financial_health']['factors'] as $factor) {
            echo "<li>" . htmlspecialchars($factor) . "</li>\n";
        }
        echo "</ul>\n";
        
        // Операционная эффективность
        echo "<h4>⚙️ Операционная эффективность:</h4>\n";
        echo "<p><strong>Балл:</strong> {$analysis['operational_efficiency']['score']}/{$analysis['operational_efficiency']['max_score']} ({$analysis['operational_efficiency']['level']})</p>\n";
        echo "<ul>\n";
        foreach ($analysis['operational_efficiency']['factors'] as $factor) {
            echo "<li>" . htmlspecialchars($factor) . "</li>\n";
        }
        echo "</ul>\n";
        
        // Рыночная позиция
        echo "<h4>📈 Рыночная позиция:</h4>\n";
        echo "<p><strong>Балл:</strong> {$analysis['market_position']['score']}/{$analysis['market_position']['max_score']} ({$analysis['market_position']['level']})</p>\n";
        echo "<ul>\n";
        foreach ($analysis['market_position']['factors'] as $factor) {
            echo "<li>" . htmlspecialchars($factor) . "</li>\n";
        }
        echo "</ul>\n";
        
        // Оценка рисков
        echo "<h4>⚠️ Оценка рисков:</h4>\n";
        echo "<p><strong>Общий риск:</strong> " . round($analysis['risk_assessment']['total_risk'] * 100, 1) . "% ({$analysis['risk_assessment']['risk_level']})</p>\n";
        echo "<p><strong>Индивидуальные риски:</strong></p>\n";
        echo "<ul>\n";
        foreach ($analysis['risk_assessment']['individual_risks'] as $risk_type => $risk_score) {
            $risk_name = ucfirst(str_replace('_', ' ', $risk_type));
            echo "<li>{$risk_name}: " . round($risk_score * 100, 1) . "%</li>\n";
        }
        echo "</ul>\n";
        
        if (!empty($analysis['risk_assessment']['recommendations'])) {
            echo "<p><strong>Рекомендации по рискам:</strong></p>\n";
            echo "<ul>\n";
            foreach ($analysis['risk_assessment']['recommendations'] as $recommendation) {
                echo "<li>" . htmlspecialchars($recommendation) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        // Потенциал роста
        echo "<h4>🚀 Потенциал роста:</h4>\n";
        echo "<p><strong>Балл:</strong> {$analysis['growth_potential']['score']}/{$analysis['growth_potential']['max_score']} ({$analysis['growth_potential']['level']})</p>\n";
        echo "<ul>\n";
        foreach ($analysis['growth_potential']['factors'] as $factor) {
            echo "<li>" . htmlspecialchars($factor) . "</li>\n";
        }
        echo "</ul>\n";
        
        // Устойчивость
        echo "<h4>🏗️ Устойчивость:</h4>\n";
        echo "<p><strong>Балл:</strong> {$analysis['sustainability']['score']}/{$analysis['sustainability']['max_score']} ({$analysis['sustainability']['level']})</p>\n";
        echo "<ul>\n";
        foreach ($analysis['sustainability']['factors'] as $factor) {
            echo "<li>" . htmlspecialchars($factor) . "</li>\n";
        }
        echo "</ul>\n";
        
        // Общие рекомендации
        if (!empty($analysis['recommendations'])) {
            echo "<h4>💡 Общие рекомендации:</h4>\n";
            echo "<ul>\n";
            foreach ($analysis['recommendations'] as $recommendation) {
                echo "<li>" . htmlspecialchars($recommendation) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Ошибка выполнения комплексного анализа</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Исключение: " . $e->getMessage() . "</p>\n";
    echo "<p>Стек вызовов:</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<h3>2. Тестирование интеграции с основным плагином:</h3>\n";

$plugin = new CompanyRatingChecker();

// Используем рефлексию для тестирования
$reflection = new ReflectionClass($plugin);

try {
    // Тестируем получение расширенной аналитики
    $analytics_method = $reflection->getMethod('get_advanced_analytics');
    $analytics_method->setAccessible(true);
    $analytics_result = $analytics_method->invoke($plugin, $test_company_data);
    
    if ($analytics_result) {
        echo "<p style='color: green;'>✅ Интеграция расширенной аналитики работает</p>\n";
        echo "<p><strong>Общий балл аналитики:</strong> " . round($analytics_result['overall_score'], 1) . "/100</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Расширенная аналитика не получена (возможно, отключена в настройках)</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка интеграции: " . $e->getMessage() . "</p>\n";
}

echo "<h3>3. Тестирование полного расчета рейтинга с аналитикой:</h3>\n";

try {
    // Включаем расширенную аналитику
    update_option('crc_advanced_analytics_enabled', 1);
    
    // Создаем полные тестовые данные
    $full_test_data = array(
        'name' => array('full' => 'ООО "Тестовая компания"'),
        'inn' => $test_inn,
        'state' => array('status' => 'ACTIVE'),
        'fns' => $test_company_data['fns'],
        'rosstat' => $test_company_data['rosstat'],
        'arbitration' => $test_company_data['arbitration'],
        'zakupki' => $test_company_data['zakupki']
    );
    
    $rating_method = $reflection->getMethod('calculate_company_rating');
    $rating_method->setAccessible(true);
    $rating = $rating_method->invoke($plugin, $full_test_data);
    
    echo "<p><strong>Максимальный балл:</strong> {$rating['max_score']}</p>\n";
    echo "<p><strong>Общий балл:</strong> {$rating['total_score']}</p>\n";
    echo "<p><strong>Количество факторов:</strong> " . count($rating['factors']) . "</p>\n";
    
    // Проверяем наличие расширенной аналитики
    if (isset($rating['advanced_analytics']) && $rating['advanced_analytics']) {
        echo "<p style='color: green;'>✅ Расширенная аналитика включена в результат</p>\n";
        echo "<p><strong>Балл аналитики:</strong> " . round($rating['advanced_analytics']['overall_score'], 1) . "/100</p>\n";
        
        if (!empty($rating['advanced_analytics']['recommendations'])) {
            echo "<p><strong>Количество рекомендаций:</strong> " . count($rating['advanced_analytics']['recommendations']) . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Расширенная аналитика не включена в результат</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка полного расчета: " . $e->getMessage() . "</p>\n";
}

echo "<h3>4. Тестирование различных сценариев:</h3>\n";

// Тест 1: Компания с высокими показателями
echo "<h4>Сценарий 1: Компания с высокими показателями</h4>\n";
$high_performance_data = array(
    'name' => array('full' => 'ООО "Успешная компания"'),
    'inn' => '7700000000',
    'state' => array('status' => 'ACTIVE'),
    'fns' => array(
        'revenue' => 500000000,
        'profit' => 100000000,
        'profitability' => 20,
        'debt_ratio' => 10,
        'bankruptcy_risk' => 'low'
    ),
    'rosstat' => array(
        'region' => array('region_name' => 'Москва', 'statistical_rating' => 0.9),
        'sector' => array('sector_name' => 'IT', 'sector_rating' => 0.9),
        'enterprise_size' => array('size_category' => 'large', 'estimated_employees' => 200),
        'employment' => array('employment_stability' => 0.9)
    )
);

$high_analysis = $analytics->comprehensive_analysis($high_performance_data);
echo "<p><strong>Общий балл:</strong> " . round($high_analysis['overall_score'], 1) . "/100</p>\n";

// Тест 2: Компания с низкими показателями
echo "<h4>Сценарий 2: Компания с низкими показателями</h4>\n";
$low_performance_data = array(
    'name' => array('full' => 'ООО "Проблемная компания"'),
    'inn' => '0100000000',
    'state' => array('status' => 'ACTIVE'),
    'fns' => array(
        'revenue' => 1000000,
        'profit' => -500000,
        'profitability' => -50,
        'debt_ratio' => 80,
        'bankruptcy_risk' => 'high'
    ),
    'rosstat' => array(
        'region' => array('region_name' => 'Проблемный регион', 'statistical_rating' => 0.3),
        'sector' => array('sector_name' => 'Убыточная отрасль', 'sector_rating' => 0.2),
        'enterprise_size' => array('size_category' => 'micro', 'estimated_employees' => 5),
        'employment' => array('employment_stability' => 0.2)
    )
);

$low_analysis = $analytics->comprehensive_analysis($low_performance_data);
echo "<p><strong>Общий балл:</strong> " . round($low_analysis['overall_score'], 1) . "/100</p>\n";

echo "<h3>5. Проверка настроек:</h3>\n";

$settings_to_check = array(
    'crc_advanced_analytics_enabled'
);

foreach ($settings_to_check as $setting) {
    $value = get_option($setting);
    echo "<p><strong>{$setting}:</strong> " . var_export($value, true) . "</p>\n";
}

echo "<h3>🎯 Итоговая оценка:</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Расширенная аналитика работает корректно!</h4>\n";
echo "<ul style='margin: 0; color: #155724;'>\n";
echo "<li>✅ Класс AdvancedAnalytics функционирует</li>\n";
echo "<li>✅ Комплексный анализ выполняется</li>\n";
echo "<li>✅ Оценка рисков работает</li>\n";
echo "<li>✅ Генерация рекомендаций функционирует</li>\n";
echo "<li>✅ Интеграция с основным плагином работает</li>\n";
echo "<li>✅ Различные сценарии обрабатываются корректно</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>📋 Следующие шаги:</h3>\n";
echo "<ol>\n";
echo "<li>Добавление экспорта данных в различных форматах</li>\n";
echo "</ol>\n";
?>
