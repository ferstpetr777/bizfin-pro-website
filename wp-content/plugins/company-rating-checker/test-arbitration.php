<?php
/**
 * Тестовый скрипт для получения данных об арбитражных делах
 * Company Rating Checker - Arbitration Cases Test
 */

// Настройки
$test_inn = '5260482041'; // Тестовый ИНН для проверки
$api_key = ''; // API ключ (будет добавлен позже)

echo "<h2>Тестирование подключения к арбитражным делам</h2>\n";
echo "<p>Тестовый ИНН: {$test_inn}</p>\n";

/**
 * Функция для получения данных об арбитражных делах
 * Пока используем бесплатные источники
 */
function get_arbitration_data($inn) {
    $results = array();
    
    // Попытка 1: Поиск через открытые данные арбитражных судов
    $results['open_data'] = get_arbitration_open_data($inn);
    
    // Попытка 2: Поиск через альтернативные источники
    $results['alternative'] = get_arbitration_alternative($inn);
    
    return $results;
}

/**
 * Получение данных через открытые источники
 */
function get_arbitration_open_data($inn) {
    echo "<h3>1. Поиск через открытые данные арбитражных судов</h3>\n";
    
    // URL для поиска в открытых данных (пример)
    $search_url = "https://kad.arbitr.ru/";
    
    // Параметры поиска
    $search_params = array(
        'inn' => $inn,
        'page' => 1,
        'count' => 10
    );
    
    echo "<p>URL поиска: {$search_url}</p>\n";
    echo "<p>Параметры: " . json_encode($search_params, JSON_UNESCAPED_UNICODE) . "</p>\n";
    
    // Попытка получить данные
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'header' => array(
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: application/json, text/html, */*',
                'Accept-Language: ru-RU,ru;q=0.9,en;q=0.8'
            ),
            'timeout' => 30
        )
    ));
    
    try {
        // Пока что просто проверяем доступность
        $response = @file_get_contents($search_url, false, $context);
        
        if ($response === false) {
            echo "<p style='color: orange;'>⚠️ Не удалось получить данные напрямую (возможно, требуется POST запрос)</p>\n";
            return array('status' => 'error', 'message' => 'Прямой доступ недоступен');
        } else {
            echo "<p style='color: green;'>✅ Соединение с сервером установлено</p>\n";
            echo "<p>Размер ответа: " . strlen($response) . " байт</p>\n";
            return array('status' => 'success', 'data' => 'Соединение установлено');
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>\n";
        return array('status' => 'error', 'message' => $e->getMessage());
    }
}

/**
 * Альтернативный метод поиска
 */
function get_arbitration_alternative($inn) {
    echo "<h3>2. Альтернативный поиск</h3>\n";
    
    // Попытка найти данные через другие источники
    $alternative_sources = array(
        'https://sudact.ru/',
        'https://rospravosudie.com/',
        'https://sudrf.ru/'
    );
    
    $results = array();
    
    foreach ($alternative_sources as $source) {
        echo "<p>Проверка источника: {$source}</p>\n";
        
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ),
                'timeout' => 10
            )
        ));
        
        $response = @file_get_contents($source, false, $context);
        
        if ($response !== false) {
            echo "<p style='color: green;'>✅ Источник доступен</p>\n";
            $results[$source] = array('status' => 'available', 'size' => strlen($response));
        } else {
            echo "<p style='color: red;'>❌ Источник недоступен</p>\n";
            $results[$source] = array('status' => 'unavailable');
        }
    }
    
    return $results;
}

/**
 * Тестирование cURL для более сложных запросов
 */
function test_curl_arbitration($inn) {
    echo "<h3>3. Тестирование cURL</h3>\n";
    
    if (!function_exists('curl_init')) {
        echo "<p style='color: red;'>❌ cURL не доступен</p>\n";
        return false;
    }
    
    echo "<p style='color: green;'>✅ cURL доступен</p>\n";
    
    // Тестовый запрос к API арбитражных судов
    $url = "https://kad.arbitr.ru/";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ Ошибка cURL: {$error}</p>\n";
        return false;
    }
    
    echo "<p style='color: green;'>✅ HTTP код: {$http_code}</p>\n";
    echo "<p>Размер ответа: " . strlen($response) . " байт</p>\n";
    
    if ($http_code == 200) {
        echo "<p style='color: green;'>✅ Успешное подключение к API арбитражных судов</p>\n";
        return true;
    }
    
    return false;
}

/**
 * Анализ полученных данных
 */
function analyze_arbitration_data($data) {
    echo "<h3>4. Анализ данных</h3>\n";
    
    if (empty($data)) {
        echo "<p style='color: orange;'>⚠️ Данные не получены</p>\n";
        return;
    }
    
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>\n";
}

// Запуск тестирования
echo "<hr>\n";
$arbitration_data = get_arbitration_data($test_inn);

echo "<hr>\n";
test_curl_arbitration($test_inn);

echo "<hr>\n";
analyze_arbitration_data($arbitration_data);

echo "<hr>\n";
echo "<h3>5. Рекомендации</h3>\n";
echo "<ul>\n";
echo "<li>Для получения данных об арбитражных делах рекомендуется использовать платные API (OfData, DaMIA)</li>\n";
echo "<li>Бесплатные источники имеют ограничения и могут требовать парсинг HTML</li>\n";
echo "<li>Необходимо получить API ключи для полноценной работы</li>\n";
echo "<li>Рекомендуется кэширование результатов для снижения нагрузки</li>\n";
echo "</ul>\n";

echo "<h3>6. Следующие шаги</h3>\n";
echo "<ol>\n";
echo "<li>Получить API ключ от одного из провайдеров</li>\n";
echo "<li>Создать полноценный класс для работы с API</li>\n";
echo "<li>Интегрировать в основной плагин</li>\n";
echo "<li>Добавить обработку ошибок и кэширование</li>\n";
echo "</ol>\n";
?>
