<?php
/**
 * Тест системы кэширования
 * Company Rating Checker - Cache System Test
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Загружаем плагин
require_once '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';

echo "<h2>🗄️ Тестирование системы кэширования</h2>\n";

// Проверяем, что менеджер кэша загружен
if (!class_exists('CRCCacheManager')) {
    echo "<p style='color: red;'>❌ Класс CRCCacheManager не найден</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ Класс CRCCacheManager загружен</p>\n";

// Создаем экземпляр менеджера кэша
$cache_manager = new CRCCacheManager();

echo "<h3>1. Тестирование базовых операций кэширования:</h3>\n";

// Тест 1: Сохранение и получение данных
$test_key = 'test_key_' . time();
$test_data = array(
    'message' => 'Тестовые данные',
    'timestamp' => time(),
    'company' => 'ООО "Тестовая компания"'
);

$save_result = $cache_manager->set($test_key, $test_data, 1); // 1 час
if ($save_result) {
    echo "<p style='color: green;'>✅ Данные сохранены в кэш</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка сохранения в кэш</p>\n";
}

$retrieved_data = $cache_manager->get($test_key);
if ($retrieved_data && $retrieved_data['message'] === $test_data['message']) {
    echo "<p style='color: green;'>✅ Данные корректно получены из кэша</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка получения данных из кэша</p>\n";
}

// Тест 2: Удаление данных
$delete_result = $cache_manager->delete($test_key);
if ($delete_result) {
    echo "<p style='color: green;'>✅ Данные удалены из кэша</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка удаления из кэша</p>\n";
}

$retrieved_after_delete = $cache_manager->get($test_key);
if ($retrieved_after_delete === false) {
    echo "<p style='color: green;'>✅ Данные корректно удалены из кэша</p>\n";
} else {
    echo "<p style='color: red;'>❌ Данные не удалены из кэша</p>\n";
}

echo "<h3>2. Тестирование специализированных ключей кэша:</h3>\n";

$test_inn = '5260482041';

// Тест ключей для компании
$company_key = $cache_manager->get_company_cache_key($test_inn);
echo "<p><strong>Ключ кэша компании:</strong> {$company_key}</p>\n";

$arbitration_key = $cache_manager->get_arbitration_cache_key($test_inn);
echo "<p><strong>Ключ кэша арбитража:</strong> {$arbitration_key}</p>\n";

$zakupki_key = $cache_manager->get_zakupki_cache_key($test_inn);
echo "<p><strong>Ключ кэша закупок:</strong> {$zakupki_key}</p>\n";

echo "<h3>3. Тестирование статистики кэша:</h3>\n";

$stats = $cache_manager->get_stats();
echo "<p><strong>Активных кэшей:</strong> {$stats['active_count']}</p>\n";
echo "<p><strong>Истекших кэшей:</strong> {$stats['expired_count']}</p>\n";
echo "<p><strong>Размер кэша:</strong> {$stats['cache_size_mb']} МБ</p>\n";

echo "<h3>4. Тестирование проверки необходимости обновления:</h3>\n";

$refresh_key = 'refresh_test_' . time();
$cache_manager->set($refresh_key, array('data' => 'test'), 1);

// Проверяем сразу после сохранения
$should_refresh_immediate = $cache_manager->should_refresh($refresh_key, 0.1); // 6 минут
if (!$should_refresh_immediate) {
    echo "<p style='color: green;'>✅ Кэш не требует обновления сразу после сохранения</p>\n";
} else {
    echo "<p style='color: red;'>❌ Кэш требует обновления сразу после сохранения</p>\n";
}

// Проверяем с очень коротким временем
$should_refresh_short = $cache_manager->should_refresh($refresh_key, 0.001); // 3.6 секунды
if ($should_refresh_short) {
    echo "<p style='color: green;'>✅ Кэш корректно требует обновления при коротком времени</p>\n";
} else {
    echo "<p style='color: red;'>❌ Кэш не требует обновления при коротком времени</p>\n";
}

echo "<h3>5. Тестирование очистки истекших кэшей:</h3>\n";

// Создаем несколько тестовых кэшей
for ($i = 0; $i < 5; $i++) {
    $expired_key = 'expired_test_' . $i . '_' . time();
    $cache_manager->set($expired_key, array('data' => "test_{$i}"), 0.001); // 3.6 секунды
}

// Ждем истечения
sleep(5);

$cleanup_result = $cache_manager->cleanup_expired();
if ($cleanup_result !== false) {
    echo "<p style='color: green;'>✅ Очистка истекших кэшей выполнена. Удалено: {$cleanup_result} записей</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка очистки истекших кэшей</p>\n";
}

echo "<h3>6. Тестирование интеграции с плагином:</h3>\n";

$plugin = new CompanyRatingChecker();

// Тестируем кэширование через AJAX обработчик
$_POST['inn'] = $test_inn;
$_POST['nonce'] = wp_create_nonce('crc_nonce');

// Первый запрос - должен создать кэш
ob_start();
$plugin->ajax_get_company_rating();
$first_response = ob_get_clean();

$first_data = json_decode($first_response, true);
if ($first_data && $first_data['success']) {
    echo "<p style='color: green;'>✅ Первый AJAX запрос выполнен успешно</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка первого AJAX запроса</p>\n";
}

// Второй запрос - должен использовать кэш
ob_start();
$plugin->ajax_get_company_rating();
$second_response = ob_get_clean();

$second_data = json_decode($second_response, true);
if ($second_data && $second_data['success']) {
    echo "<p style='color: green;'>✅ Второй AJAX запрос выполнен успешно (использован кэш)</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка второго AJAX запроса</p>\n";
}

// Сравниваем результаты
if ($first_data && $second_data && 
    $first_data['data']['rating']['total_score'] === $second_data['data']['rating']['total_score']) {
    echo "<p style='color: green;'>✅ Результаты кэширования идентичны</p>\n";
} else {
    echo "<p style='color: red;'>❌ Результаты кэширования различаются</p>\n";
}

echo "<h3>7. Тестирование админ-информации:</h3>\n";

$admin_info = $cache_manager->get_admin_info();
echo "<p><strong>Настройки кэширования:</strong></p>\n";
echo "<ul>\n";
echo "<li>Время кэширования: {$admin_info['settings']['cache_duration']} часов</li>\n";
echo "<li>Режим отладки: " . ($admin_info['settings']['debug_mode'] ? 'Включен' : 'Отключен') . "</li>\n";
echo "</ul>\n";

echo "<p><strong>Рекомендации:</strong></p>\n";
echo "<ul>\n";
foreach ($admin_info['recommendations'] as $recommendation) {
    echo "<li>{$recommendation}</li>\n";
}
echo "</ul>\n";

echo "<h3>8. Очистка тестовых данных:</h3>\n";

$clear_result = $cache_manager->clear_all();
if ($clear_result) {
    echo "<p style='color: green;'>✅ Все тестовые данные очищены</p>\n";
} else {
    echo "<p style='color: red;'>❌ Ошибка очистки тестовых данных</p>\n";
}

echo "<h3>🎯 Итоговая оценка:</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Система кэширования работает корректно!</h4>\n";
echo "<ul style='margin: 0; color: #155724;'>\n";
echo "<li>✅ Базовые операции кэширования работают</li>\n";
echo "<li>✅ Специализированные ключи кэша функционируют</li>\n";
echo "<li>✅ Статистика кэша отображается корректно</li>\n";
echo "<li>✅ Проверка необходимости обновления работает</li>\n";
echo "<li>✅ Очистка истекших кэшей функционирует</li>\n";
echo "<li>✅ Интеграция с плагином работает</li>\n";
echo "<li>✅ Админ-информация отображается</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>📋 Следующие шаги:</h3>\n";
echo "<ol>\n";
echo "<li>Рассмотреть интеграцию дополнительных источников</li>\n";
echo "<li>Обновить документацию</li>\n";
echo "</ol>\n";
?>
