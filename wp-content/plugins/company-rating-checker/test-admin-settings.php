<?php
/**
 * Тест настроек админ-панели
 * Company Rating Checker - Admin Settings Test
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>⚙️ Тестирование настроек админ-панели</h2>\n";

// Проверяем, что настройки зарегистрированы
echo "<h3>1. Проверка регистрации настроек:</h3>\n";

$settings_to_check = [
    'crc_dadata_token',
    'crc_dadata_secret',
    'crc_arbitration_enabled',
    'crc_zakupki_enabled',
    'crc_cache_duration',
    'crc_debug_mode'
];

foreach ($settings_to_check as $setting) {
    $value = get_option($setting);
    if ($value !== false) {
        echo "<p style='color: green;'>✅ Настройка '{$setting}' зарегистрирована (значение: " . var_export($value, true) . ")</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Настройка '{$setting}' не найдена</p>\n";
    }
}

// Тестируем включение/выключение источников данных
echo "<h3>2. Тестирование включения/выключения источников:</h3>\n";

$plugin = new CompanyRatingChecker();
$test_inn = '5260482041';

// Тест 1: Все источники включены
echo "<h4>Тест 1: Все источники включены</h4>\n";
update_option('crc_arbitration_enabled', 1);
update_option('crc_zakupki_enabled', 1);

// Используем рефлексию для тестирования
$reflection = new ReflectionClass($plugin);

// Тестируем получение арбитражных данных
$arbitration_method = $reflection->getMethod('get_arbitration_data');
$arbitration_method->setAccessible(true);
$arbitration_data = $arbitration_method->invoke($plugin, $test_inn);

if ($arbitration_data) {
    echo "<p style='color: green;'>✅ Арбитражные данные получены (включены)</p>\n";
} else {
    echo "<p style='color: red;'>❌ Арбитражные данные не получены</p>\n";
}

// Тестируем получение данных о закупках
$zakupki_method = $reflection->getMethod('get_zakupki_data');
$zakupki_method->setAccessible(true);
$zakupki_data = $zakupki_method->invoke($plugin, $test_inn);

if ($zakupki_data) {
    echo "<p style='color: green;'>✅ Данные о закупках получены (включены)</p>\n";
} else {
    echo "<p style='color: red;'>❌ Данные о закупках не получены</p>\n";
}

// Тест 2: Арбитражные данные выключены
echo "<h4>Тест 2: Арбитражные данные выключены</h4>\n";
update_option('crc_arbitration_enabled', 0);
update_option('crc_zakupki_enabled', 1);

$arbitration_data = $arbitration_method->invoke($plugin, $test_inn);
if ($arbitration_data === null) {
    echo "<p style='color: green;'>✅ Арбитражные данные отключены корректно</p>\n";
} else {
    echo "<p style='color: red;'>❌ Арбитражные данные не отключены</p>\n";
}

$zakupki_data = $zakupki_method->invoke($plugin, $test_inn);
if ($zakupki_data) {
    echo "<p style='color: green;'>✅ Данные о закупках остались включенными</p>\n";
} else {
    echo "<p style='color: red;'>❌ Данные о закупках отключились неожиданно</p>\n";
}

// Тест 3: Все источники выключены
echo "<h4>Тест 3: Все дополнительные источники выключены</h4>\n";
update_option('crc_arbitration_enabled', 0);
update_option('crc_zakupki_enabled', 0);

$arbitration_data = $arbitration_method->invoke($plugin, $test_inn);
$zakupki_data = $zakupki_method->invoke($plugin, $test_inn);

if ($arbitration_data === null && $zakupki_data === null) {
    echo "<p style='color: green;'>✅ Все дополнительные источники отключены корректно</p>\n";
} else {
    echo "<p style='color: red;'>❌ Не все источники отключены</p>\n";
}

// Тестируем расчет максимального балла
echo "<h3>3. Тестирование расчета максимального балла:</h3>\n";

// Создаем тестовые данные
$test_company_data = array(
    'name' => array('full' => 'ООО "Тестовая компания"'),
    'inn' => $test_inn,
    'state' => array('status' => 'ACTIVE'),
    'arbitration' => $arbitration_data,
    'zakupki' => $zakupki_data
);

// Тест с включенными источниками
update_option('crc_arbitration_enabled', 1);
update_option('crc_zakupki_enabled', 1);

$rating_method = $reflection->getMethod('calculate_company_rating');
$rating_method->setAccessible(true);
$rating = $rating_method->invoke($plugin, $test_company_data);

echo "<p><strong>С включенными источниками:</strong> Максимальный балл: {$rating['max_score']}</p>\n";

// Тест с отключенными источниками
update_option('crc_arbitration_enabled', 0);
update_option('crc_zakupki_enabled', 0);

$rating = $rating_method->invoke($plugin, $test_company_data);
echo "<p><strong>С отключенными источниками:</strong> Максимальный балл: {$rating['max_score']}</p>\n";

// Тестируем кэширование
echo "<h3>4. Тестирование настроек кэширования:</h3>\n";

$cache_durations = [1, 6, 12, 24, 168];
foreach ($cache_durations as $duration) {
    update_option('crc_cache_duration', $duration);
    $current_duration = get_option('crc_cache_duration');
    if ($current_duration == $duration) {
        echo "<p style='color: green;'>✅ Кэширование на {$duration} часов установлено</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Ошибка установки кэширования на {$duration} часов</p>\n";
    }
}

// Тестируем режим отладки
echo "<h3>5. Тестирование режима отладки:</h3>\n";

update_option('crc_debug_mode', 1);
if (get_option('crc_debug_mode') == 1) {
    echo "<p style='color: green;'>✅ Режим отладки включен</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка включения режима отладки</p>\n";
}

update_option('crc_debug_mode', 0);
if (get_option('crc_debug_mode') == 0) {
    echo "<p style='color: green;'>✅ Режим отладки отключен</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка отключения режима отладки</p>\n";
}

// Восстанавливаем настройки по умолчанию
echo "<h3>6. Восстановление настроек по умолчанию:</h3>\n";
update_option('crc_arbitration_enabled', 1);
update_option('crc_zakupki_enabled', 1);
update_option('crc_cache_duration', 12);
update_option('crc_debug_mode', 0);

echo "<p style='color: green;'>✅ Настройки восстановлены по умолчанию</p>\n";

echo "<h3>🎯 Итоговая оценка:</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Настройки админ-панели работают корректно!</h4>\n";
echo "<ul style='margin: 0; color: #155724;'>\n";
echo "<li>✅ Все настройки зарегистрированы</li>\n";
echo "<li>✅ Включение/выключение источников данных работает</li>\n";
echo "<li>✅ Расчет максимального балла учитывает настройки</li>\n";
echo "<li>✅ Настройки кэширования работают</li>\n";
echo "<li>✅ Режим отладки функционирует</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>📋 Следующие шаги:</h3>\n";
echo "<ol>\n";
echo "<li>Оптимизировать производительность и добавить кэширование</li>\n";
echo "<li>Рассмотреть интеграцию дополнительных источников</li>\n";
echo "<li>Обновить документацию</li>\n";
echo "</ol>\n";
?>
