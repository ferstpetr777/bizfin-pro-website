<?php
require_once('wp-config.php');

// Список ID статей для проверки
$article_ids = array(2526, 2525, 2524, 2523, 2522, 2521, 2520, 2519, 2518, 2517, 2516, 2515, 2514, 2513, 2512, 2511, 2510, 2509, 2508, 2507, 2506, 2505, 2504, 2503, 2502, 2501, 2500, 2499, 2498, 2497, 2496, 2495, 2494, 2493, 2492, 2491, 2490, 2489, 2488, 2487, 2486, 2485, 2484, 2483, 2482, 2481, 2480, 2479, 2478, 2477, 2476, 2475, 2474, 2473, 2472, 2471, 2470, 2469, 2468, 2467, 2466, 2465, 2464, 2463, 2462, 2461, 2460, 2459, 2458, 2457, 2456, 2455, 2454, 2453, 2452, 2451, 2450, 2449, 2448, 2447, 2446, 2445, 2444, 2443, 2442, 2441, 2440, 2439, 2437, 2436, 2435, 2434, 2433, 2432, 2431, 2430, 2429, 2428, 2427, 2426, 2425, 2423, 2421, 2420, 2060, 2046);

echo "=== ТАБЛИЦА СТАТЕЙ ИЗ АРХИВНОЙ БД ===\n\n";

$archive_sql_path = '/tmp/bizfin_restore/bizfin-pro.ru/bizfin-pro.ru_database_backup_20251023_130034.sql';

if (!file_exists($archive_sql_path)) {
    echo "❌ Файл архива не найден: $archive_sql_path\n";
    exit;
}

echo "ID\tЗаголовок\t\t\t\tРазмер контента\n";
echo "================================================================================\n";

$found_count = 0;
$not_found_count = 0;

foreach ($article_ids as $article_id) {
    // Ищем статью в архиве по ID
    $grep_command = "grep -A 5 \"INSERT INTO.*wp_posts.*$article_id\" $archive_sql_path | head -10";
    $output = shell_exec($grep_command);
    
    if (empty($output)) {
        echo "$article_id\tНЕ НАЙДЕНА В АРХИВЕ\t\t\t-\n";
        $not_found_count++;
        continue;
    }
    
    // Извлекаем заголовок и контент из SQL записи
    if (preg_match('/post_title\',\s*\'([^\']*(?:\\.[^\']*)*)\'/', $output, $title_matches)) {
        $title = $title_matches[1];
        // Декодируем экранированные символы в заголовке
        $title = str_replace('\\n', "\n", $title);
        $title = str_replace('\\r', "\r", $title);
        $title = str_replace('\\t', "\t", $title);
        $title = str_replace('\\"', '"', $title);
        $title = str_replace("\\'", "'", $title);
        $title = str_replace('\\\\', '\\', $title);
    } else {
        $title = "НЕ УДАЛОСЬ ИЗВЛЕЧЬ";
    }
    
    if (preg_match('/post_content\',\s*\'([^\']*(?:\\.[^\']*)*)\'/', $output, $content_matches)) {
        $content = $content_matches[1];
        // Декодируем экранированные символы в контенте
        $content = str_replace('\\n', "\n", $content);
        $content = str_replace('\\r', "\r", $content);
        $content = str_replace('\\t', "\t", $content);
        $content = str_replace('\\"', '"', $content);
        $content = str_replace("\\'", "'", $content);
        $content = str_replace('\\\\', '\\', $content);
        $content_size = strlen($content);
    } else {
        $content_size = 0;
    }
    
    // Обрезаем заголовок для таблицы
    $display_title = mb_substr($title, 0, 40);
    if (mb_strlen($title) > 40) {
        $display_title .= "...";
    }
    
    echo "$article_id\t$display_title\t\t$content_size\n";
    $found_count++;
}

echo "\n=== СТАТИСТИКА ===\n";
echo "Всего статей для проверки: " . count($article_ids) . "\n";
echo "Найдено в архиве: $found_count\n";
echo "Не найдено в архиве: $not_found_count\n";
echo "Процент найденных: " . round(($found_count / count($article_ids)) * 100, 2) . "%\n";

