<?php
/**
 * Test Script: Garantolog.ru Parser Test
 * Description: Тестирует парсинг данных с garantolog.ru/kalkulyator-garantii/
 * Version:     1.0.0
 * Author:      AI Assistant
 */

// Load WordPress environment
require_once('wp-load.php');

echo "<h1>🔍 Тест парсера Garantolog.ru</h1>";

// Test URL
$url = 'https://garantolog.ru/kalkulyator-garantii/';

echo "<h2>1. Тест подключения к garantolog.ru</h2>";
echo "<p>URL: <a href='" . esc_url($url) . "' target='_blank'>" . esc_html($url) . "</a></p>";

$response = wp_remote_get($url, [
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1'
    ]
]);

if (is_wp_error($response)) {
    echo "<p style='color: red;'>❌ Ошибка HTTP запроса: " . esc_html($response->get_error_message()) . "</p>";
    exit;
}

$html = wp_remote_retrieve_body($response);
$status_code = wp_remote_retrieve_response_code($response);

echo "<p style='color: green;'>✅ HTTP запрос успешен (статус: " . $status_code . ")</p>";
echo "<p>Размер HTML: " . strlen($html) . " байт</p>";

echo "<h2>2. Анализ структуры HTML</h2>";

// Ищем ключевые элементы
$patterns = [
    'JSON данные' => [
        '/window\.__INITIAL_STATE__\s*=\s*({.*?});/s',
        '/window\.__APP_DATA__\s*=\s*({.*?});/s',
        '/window\.__BANKS_DATA__\s*=\s*({.*?});/s',
        '/var\s+banksData\s*=\s*({.*?});/s'
    ],
    'Таблицы с банками' => [
        '/<table[^>]*>.*?банк.*?<\/table>/ius',
        '/<div[^>]*class="[^"]*bank[^"]*"[^>]*>.*?<\/div>/ius'
    ],
    'Минимальные ставки' => [
        '/минимальная\s+ставка[^>]*>.*?(\d+[,.]\d+)\s*%/iu',
        '/от\s+(\d+[,.]\d+)\s*%/iu',
        '/1[,.]76\s*%/iu'
    ],
    'API endpoints' => [
        '/\/api\/[^"\']*rates[^"\']*/iu',
        '/\/api\/[^"\']*banks[^"\']*/iu',
        '/\/api\/[^"\']*tariffs[^"\']*/iu'
    ]
];

foreach ($patterns as $category => $category_patterns) {
    echo "<h3>" . $category . ":</h3>";
    $found = false;
    
    foreach ($category_patterns as $pattern) {
        if (preg_match_all($pattern, $html, $matches)) {
            $found = true;
            echo "<p style='color: green;'>✅ Найдено " . count($matches[0]) . " совпадений</p>";
            
            // Показываем первые несколько примеров
            for ($i = 0; $i < min(3, count($matches[0])); $i++) {
                $preview = substr($matches[0][$i], 0, 200);
                echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 5px; font-size: 12px;'>" . esc_html($preview) . "...</pre>";
            }
            break;
        }
    }
    
    if (!$found) {
        echo "<p style='color: orange;'>⚠️ Не найдено</p>";
    }
}

echo "<h2>3. Поиск банковских данных</h2>";

// Ищем банки в HTML
$bank_patterns = [
    'Названия банков' => '/([А-ЯЁ][а-яё\s]+(?:банк|Банк|БАНК)[а-яё\s]*)/iu',
    'Процентные ставки' => '/(\d+[,.]\d+)\s*%/u',
    'Структурированные данные' => '/<[^>]*>([^<]{3,50}(?:банк|Банк)[^<]{0,50})<\/[^>]*>.*?(\d+[,.]\d+)\s*%.*?(\d+[,.]\d+)\s*%.*?(\d+[,.]\d+)\s*%.*?(\d+[,.]\d+)\s*%/iu'
];

foreach ($bank_patterns as $type => $pattern) {
    if (preg_match_all($pattern, $html, $matches)) {
        echo "<h3>" . $type . " (найдено " . count($matches[0]) . "):</h3>";
        
        // Показываем уникальные результаты
        $unique_matches = array_unique($matches[0]);
        $count = 0;
        foreach ($unique_matches as $match) {
            if ($count >= 10) break; // Ограничиваем вывод
            echo "<span style='background: #e8f4fd; padding: 2px 6px; margin: 2px; border-radius: 3px; display: inline-block; font-size: 12px;'>" . esc_html(trim($match)) . "</span>";
            $count++;
        }
        echo "<br><br>";
    }
}

echo "<h2>4. Тест парсинга через плагин</h2>";

if (class_exists('BFCalc_Live_Rates')) {
    $plugin = new BFCalc_Live_Rates();
    
    // Принудительно очищаем кеш
    delete_transient('bfcalc_live_rates_v1');
    
    echo "<p>Запуск fetch_and_cache() с новым URL...</p>";
    $result = $plugin->fetch_and_cache();
    
    if ($result && !empty($result['per_bank'])) {
        echo "<p style='color: green;'>✅ Парсинг успешен!</p>";
        echo "<p>Найдено банков: " . count($result['per_bank']) . "</p>";
        echo "<p>Обновлено: " . $result['updated'] . "</p>";
        
        echo "<h3>Примеры найденных банков:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Банк</th><th>44-ФЗ (участие)</th><th>44-ФЗ (исполнение)</th><th>44-ФЗ (гарантия)</th><th>44-ФЗ (аванс)</th></tr>";
        
        $count = 0;
        foreach ($result['per_bank'] as $bank) {
            if ($count >= 5) break;
            echo "<tr>";
            echo "<td>" . esc_html($bank['name']) . "</td>";
            echo "<td>" . (isset($bank['44fz']['participation']) ? esc_html($bank['44fz']['participation']) . '%' : 'N/A') . "</td>";
            echo "<td>" . (isset($bank['44fz']['performance']) ? esc_html($bank['44fz']['performance']) . '%' : 'N/A') . "</td>";
            echo "<td>" . (isset($bank['44fz']['warranty']) ? esc_html($bank['44fz']['warranty']) . '%' : 'N/A') . "</td>";
            echo "<td>" . (isset($bank['44fz']['advance']) ? esc_html($bank['44fz']['advance']) . '%' : 'N/A') . "</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
        
        // Проверяем минимальную ставку
        $min_rate = null;
        foreach ($result['per_bank'] as $bank) {
            if (isset($bank['44fz']['participation'])) {
                if ($min_rate === null || $bank['44fz']['participation'] < $min_rate) {
                    $min_rate = $bank['44fz']['participation'];
                }
            }
        }
        
        if ($min_rate !== null) {
            echo "<p><strong>Минимальная ставка участия (44-ФЗ): " . $min_rate . "%</strong></p>";
            if ($min_rate <= 1.76) {
                echo "<p style='color: green;'>✅ Найдена ставка 1,76% или ниже!</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Минимальная ставка выше 1,76%</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Парсинг не удался, используются fallback данные</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>❌ Плагин BFCalc_Live_Rates не найден</p>";
}

echo "<h2>5. Рекомендации</h2>";
echo "<ul>";
echo "<li>✅ Если найдены JSON данные - можно извлечь точные ставки</li>";
echo "<li>✅ Если найдены минимальные ставки 1,76% - источник актуален</li>";
echo "<li>⚠️ Если парсинг не работает - нужно анализировать JavaScript загрузку</li>";
echo "<li>💡 Возможно, данные загружаются через AJAX после загрузки страницы</li>";
echo "</ul>";

echo "<h2>Тест завершен</h2>";
?>
