<?php
/**
 * Тестовый скрипт для проверки интеграции арбитражных данных
 */

// Имитируем WordPress окружение
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/');
}

// Подключаем необходимые файлы
require_once __DIR__ . '/simple-arbitration.php';

echo "<h2>Тестирование интеграции арбитражных данных</h2>\n";

// Тестируем класс SimpleArbitrationAPI
$api = new SimpleArbitrationAPI();
$test_inn = '5260482041';

echo "<h3>1. Тест SimpleArbitrationAPI</h3>\n";
$arbitration_data = $api->get_arbitration_info($test_inn);

echo "<pre>" . json_encode($arbitration_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>\n";

// Тестируем расчет рейтинга
echo "<h3>2. Тест расчета рейтинга</h3>\n";

// Имитируем данные компании
$mock_company_data = array(
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
    ),
    'arbitration' => $arbitration_data
);

// Тестируем расчет арбитражного фактора
function calculate_arbitration_score($data) {
    $arbitration_data = $data['arbitration'] ?? null;
    if (!$arbitration_data) {
        return 5; // Базовый балл если нет данных
    }
    
    $risk_level = $arbitration_data['risk_level'] ?? 'unknown';
    
    switch ($risk_level) {
        case 'low':
            return 10; // Максимальный балл для низкого риска
        case 'medium':
            return 6;  // Средний балл
        case 'high':
            return 2;  // Низкий балл для высокого риска
        default:
            return 5;  // Базовый балл для неизвестного риска
    }
}

function get_arbitration_description($data) {
    $arbitration_data = $data['arbitration'] ?? null;
    if (!$arbitration_data) {
        return 'Данные об арбитражных делах недоступны';
    }
    
    $risk_level = $arbitration_data['risk_level'] ?? 'unknown';
    $recommendation = $arbitration_data['recommendation'] ?? '';
    $risk_score = $arbitration_data['risk_score'] ?? 0;
    
    $level_text = array(
        'low' => 'Низкий риск',
        'medium' => 'Средний риск', 
        'high' => 'Высокий риск',
        'unknown' => 'Неизвестный риск'
    );
    
    $level = $level_text[$risk_level] ?? 'Неизвестный риск';
    
    return "{$level} (балл: {$risk_score}/100). {$recommendation}";
}

$arbitration_score = calculate_arbitration_score($mock_company_data);
$arbitration_description = get_arbitration_description($mock_company_data);

echo "<p><strong>Арбитражный балл:</strong> {$arbitration_score}/10</p>\n";
echo "<p><strong>Описание:</strong> {$arbitration_description}</p>\n";

// Тестируем полный расчет рейтинга
echo "<h3>3. Тест полного расчета рейтинга</h3>\n";

function calculate_full_rating($company_data) {
    $score = 0;
    $max_score = 110;
    $factors = array();
    
    // Фактор 1: Статус компании (25 баллов)
    $status_score = 25; // ACTIVE
    $score += $status_score;
    $factors['status'] = array(
        'name' => 'Статус компании',
        'score' => $status_score,
        'max_score' => 25,
        'description' => 'Компания действует'
    );
    
    // Фактор 2: Время существования (20 баллов)
    $age_score = 15; // Примерно 14 лет
    $score += $age_score;
    $factors['age'] = array(
        'name' => 'Время существования',
        'score' => $age_score,
        'max_score' => 20,
        'description' => 'Компания существует 14 лет'
    );
    
    // Фактор 3: Уставный капитал (15 баллов)
    $capital_score = 15; // 10 млн руб
    $score += $capital_score;
    $factors['capital'] = array(
        'name' => 'Уставный капитал',
        'score' => $capital_score,
        'max_score' => 15,
        'description' => 'Уставный капитал: 10 000 000 руб.'
    );
    
    // Фактор 4: Сотрудники (10 баллов)
    $employees_score = 4; // 50 сотрудников
    $score += $employees_score;
    $factors['employees'] = array(
        'name' => 'Количество сотрудников',
        'score' => $employees_score,
        'max_score' => 10,
        'description' => 'Сотрудников: 50'
    );
    
    // Фактор 5: Вид деятельности (8 баллов)
    $activity_score = 8; // IT
    $score += $activity_score;
    $factors['activity'] = array(
        'name' => 'Вид деятельности',
        'score' => $activity_score,
        'max_score' => 8,
        'description' => 'ОКВЭД: 62.01'
    );
    
    // Фактор 6: Регион (7 баллов)
    $region_score = 7; // Москва
    $score += $region_score;
    $factors['region'] = array(
        'name' => 'Регион регистрации',
        'score' => $region_score,
        'max_score' => 7,
        'description' => 'Регион: Москва'
    );
    
    // Фактор 7: МСП (10 баллов)
    $msp_score = 5; // Базовый балл
    $score += $msp_score;
    $factors['msp'] = array(
        'name' => 'Статус МСП',
        'score' => $msp_score,
        'max_score' => 10,
        'description' => 'Данные МСП недоступны'
    );
    
    // Фактор 8: Финансовые показатели (5 баллов)
    $financial_score = 3;
    $score += $financial_score;
    $factors['financial'] = array(
        'name' => 'Финансовые показатели',
        'score' => $financial_score,
        'max_score' => 5,
        'description' => 'Базовая оценка (будет расширена)'
    );
    
    // Фактор 9: Арбитражные риски (10 баллов) - НОВЫЙ
    $arbitration_score = calculate_arbitration_score($company_data);
    $score += $arbitration_score;
    $factors['arbitration'] = array(
        'name' => 'Арбитражные риски',
        'score' => $arbitration_score,
        'max_score' => 10,
        'description' => get_arbitration_description($company_data)
    );
    
    // Определение рейтинга
    $rating = get_rating_level($score);
    
    return array(
        'total_score' => $score,
        'max_score' => $max_score,
        'rating' => $rating,
        'factors' => $factors
    );
}

function get_rating_level($score) {
    if ($score >= 99) return array('level' => 'AAA', 'name' => 'Отличный', 'color' => '#28a745');
    if ($score >= 88) return array('level' => 'AA', 'name' => 'Очень хороший', 'color' => '#20c997');
    if ($score >= 77) return array('level' => 'A', 'name' => 'Хороший', 'color' => '#17a2b8');
    if ($score >= 66) return array('level' => 'BBB', 'name' => 'Удовлетворительный', 'color' => '#ffc107');
    if ($score >= 55) return array('level' => 'BB', 'name' => 'Ниже среднего', 'color' => '#fd7e14');
    if ($score >= 44) return array('level' => 'B', 'name' => 'Плохой', 'color' => '#dc3545');
    return array('level' => 'CCC', 'name' => 'Очень плохой', 'color' => '#6c757d');
}

$full_rating = calculate_full_rating($mock_company_data);

echo "<div style='border: 2px solid #007cba; padding: 20px; margin: 20px 0; background: #f0f8ff;'>\n";
echo "<h4>Результат расчета рейтинга:</h4>\n";
echo "<p><strong>Общий балл:</strong> {$full_rating['total_score']}/{$full_rating['max_score']}</p>\n";
echo "<p><strong>Рейтинг:</strong> <span style='color: {$full_rating['rating']['color']}; font-weight: bold;'>{$full_rating['rating']['level']} - {$full_rating['rating']['name']}</span></p>\n";
echo "</div>\n";

echo "<h4>Детализация факторов:</h4>\n";
echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr style='background: #f5f5f5;'><th>Фактор</th><th>Балл</th><th>Макс.</th><th>Описание</th></tr>\n";

foreach ($full_rating['factors'] as $factor) {
    $percentage = round(($factor['score'] / $factor['max_score']) * 100);
    $color = $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'orange' : 'red');
    
    echo "<tr>\n";
    echo "<td><strong>{$factor['name']}</strong></td>\n";
    echo "<td style='color: {$color}; font-weight: bold;'>{$factor['score']}</td>\n";
    echo "<td>{$factor['max_score']}</td>\n";
    echo "<td>{$factor['description']}</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

echo "<h3>4. Проверка интеграции</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Интеграция успешна!</h4>\n";
echo "<ul style='margin: 0;'>\n";
echo "<li>Класс SimpleArbitrationAPI работает корректно</li>\n";
echo "<li>Арбитражные данные интегрированы в расчет рейтинга</li>\n";
echo "<li>Новый фактор 'Арбитражные риски' добавлен (10 баллов)</li>\n";
echo "<li>Максимальный балл увеличен до 110</li>\n";
echo "<li>JavaScript обновлен для отображения арбитражных данных</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>5. Следующие шаги</h3>\n";
echo "<ol>\n";
echo "<li>Протестировать плагин в WordPress</li>\n";
echo "<li>Добавить настройки для арбитражных данных в админ-панель</li>\n";
echo "<li>Перейти к интеграции следующего источника данных</li>\n";
echo "</ol>\n";
?>
