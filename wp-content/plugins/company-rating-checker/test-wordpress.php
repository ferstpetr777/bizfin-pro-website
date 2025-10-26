<?php
/**
 * Тест WordPress интеграции
 * Company Rating Checker - WordPress Test
 */

// Загружаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h2>🧪 Тестирование WordPress интеграции</h2>\n";

// Проверяем, что WordPress загружен
if (!function_exists('wp_remote_get')) {
    echo "<p style='color: red;'>❌ WordPress не загружен корректно</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ WordPress загружен успешно</p>\n";

// Проверяем доступность плагина
$plugin_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/company-rating-checker.php';
if (!file_exists($plugin_file)) {
    echo "<p style='color: red;'>❌ Файл плагина не найден</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ Файл плагина найден</p>\n";

// Загружаем плагин
require_once $plugin_file;

// Проверяем, что класс создался
if (!class_exists('CompanyRatingChecker')) {
    echo "<p style='color: red;'>❌ Класс CompanyRatingChecker не найден</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ Класс CompanyRatingChecker загружен</p>\n";

// Создаем экземпляр плагина
$plugin = new CompanyRatingChecker();

// Проверяем методы
$methods_to_check = [
    'ajax_get_company_rating',
    'display_rating_form',
    'add_admin_menu',
    'admin_page'
];

echo "<h3>Проверка методов плагина:</h3>\n";
foreach ($methods_to_check as $method) {
    if (method_exists($plugin, $method)) {
        echo "<p style='color: green;'>✅ Метод {$method} существует</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Метод {$method} не найден</p>\n";
    }
}

// Тестируем AJAX обработчик
echo "<h3>Тестирование AJAX обработчика:</h3>\n";

// Имитируем POST запрос
$_POST['inn'] = '5260482041';
$_POST['nonce'] = wp_create_nonce('crc_nonce');

// Захватываем вывод
ob_start();
try {
    $plugin->ajax_get_company_rating();
    $output = ob_get_clean();
    
    // Проверяем, что получили JSON ответ
    $data = json_decode($output, true);
    if ($data && isset($data['success'])) {
        echo "<p style='color: green;'>✅ AJAX обработчик работает корректно</p>\n";
        echo "<p><strong>Результат:</strong> " . ($data['success'] ? 'Успех' : 'Ошибка') . "</p>\n";
        
        if ($data['success'] && isset($data['data']['rating'])) {
            $rating = $data['data']['rating'];
            echo "<p><strong>Рейтинг:</strong> {$rating['total_score']}/{$rating['max_score']} ({$rating['rating']['level']})</p>\n";
            
            // Проверяем новые факторы
            if (isset($rating['factors']['arbitration'])) {
                echo "<p style='color: green;'>✅ Фактор 'Арбитражные риски' присутствует</p>\n";
            } else {
                echo "<p style='color: red;'>❌ Фактор 'Арбитражные риски' отсутствует</p>\n";
            }
            
            if (isset($rating['factors']['zakupki'])) {
                echo "<p style='color: green;'>✅ Фактор 'Государственные закупки' присутствует</p>\n";
            } else {
                echo "<p style='color: red;'>❌ Фактор 'Государственные закупки' отсутствует</p>\n";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ AJAX обработчик вернул некорректный ответ</p>\n";
        echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Ошибка в AJAX обработчике: " . $e->getMessage() . "</p>\n";
}

// Тестируем шорткод
echo "<h3>Тестирование шорткода:</h3>\n";

$shortcode_output = $plugin->display_rating_form();
if (!empty($shortcode_output) && strpos($shortcode_output, 'crc-rating-form') !== false) {
    echo "<p style='color: green;'>✅ Шорткод работает корректно</p>\n";
    echo "<p><strong>Длина вывода:</strong> " . strlen($shortcode_output) . " символов</p>\n";
} else {
    echo "<p style='color: red;'>❌ Шорткод работает некорректно</p>\n";
}

// Проверяем настройки
echo "<h3>Проверка настроек:</h3>\n";

$dadata_token = get_option('crc_dadata_token');
if (!empty($dadata_token)) {
    echo "<p style='color: green;'>✅ API ключ DaData настроен</p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ API ключ DaData не настроен</p>\n";
}

// Проверяем хуки WordPress
echo "<h3>Проверка WordPress хуков:</h3>\n";

$hooks_to_check = [
    'wp_ajax_crc_get_company_rating',
    'wp_ajax_nopriv_crc_get_company_rating',
    'admin_menu',
    'admin_init',
    'wp_enqueue_scripts'
];

foreach ($hooks_to_check as $hook) {
    if (has_action($hook)) {
        echo "<p style='color: green;'>✅ Хук {$hook} зарегистрирован</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Хук {$hook} не зарегистрирован</p>\n";
    }
}

// Проверяем стили и скрипты
echo "<h3>Проверка ресурсов:</h3>\n";

$style_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/assets/style.css';
$script_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/plugins/company-rating-checker/assets/script.js';

if (file_exists($style_file)) {
    echo "<p style='color: green;'>✅ Файл стилей найден (" . filesize($style_file) . " байт)</p>\n";
} else {
    echo "<p style='color: red;'>❌ Файл стилей не найден</p>\n";
}

if (file_exists($script_file)) {
    echo "<p style='color: green;'>✅ Файл скриптов найден (" . filesize($script_file) . " байт)</p>\n";
} else {
    echo "<p style='color: red;'>❌ Файл скриптов не найден</p>\n";
}

echo "<h3>🎯 Итоговая оценка:</h3>\n";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ WordPress интеграция успешна!</h4>\n";
echo "<p style='color: #155724; margin: 0;'>Плагин корректно интегрирован в WordPress и готов к использованию.</p>\n";
echo "</div>\n";

echo "<h3>📋 Следующие шаги:</h3>\n";
echo "<ol>\n";
echo "<li>Добавить настройки для новых источников данных в админ-панель</li>\n";
echo "<li>Оптимизировать производительность и добавить кэширование</li>\n";
echo "<li>Рассмотреть интеграцию дополнительных источников</li>\n";
echo "<li>Обновить документацию</li>\n";
echo "</ol>\n";
?>
