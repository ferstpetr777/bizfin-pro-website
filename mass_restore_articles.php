<?php
require_once('wp-config.php');

// ID статей от 19 октября 2025 года
$october_19_articles = [
    2527, 2526, 2525, 2524, 2523, 2522, 2521, 2520, 2519, 2518, 2517, 2516, 2515, 2514, 2513, 2512, 2511, 2510, 2509, 2508, 2507, 2506, 2505, 2504, 2503, 2502, 2501, 2500, 2499, 2498, 2497, 2496, 2495, 2494, 2493, 2492, 2491, 2490, 2489, 2488, 2487, 2486, 2485, 2484, 2483, 2482, 2481, 2480, 2479, 2478, 2477, 2476, 2475, 2474, 2473, 2472, 2471, 2470, 2469, 2468, 2467, 2466, 2465, 2464, 2463, 2462, 2461, 2460, 2459, 2458, 2457, 2456, 2455, 2454, 2453, 2452, 2451, 2450, 2449, 2448, 2447, 2446, 2445, 2444, 2443, 2442, 2441, 2440, 2439, 2438, 2437, 2436, 2435, 2434, 2433, 2432, 2431, 2430, 2429, 2428, 2427, 2426, 2425, 2423, 2422, 2421, 2420
];

// ID статей от 7 октября 2025 года
$october_7_articles = [2060, 2046];

// Объединяем все статьи
$all_articles = array_merge($october_19_articles, $october_7_articles);

echo "=== МАССОВОЕ ВОССТАНОВЛЕНИЕ СТАТЕЙ ===\n";
echo "Всего статей для восстановления: " . count($all_articles) . "\n";
echo "Статьи от 19 октября: " . count($october_19_articles) . "\n";
echo "Статьи от 7 октября: " . count($october_7_articles) . "\n\n";

$success_count = 0;
$error_count = 0;
$skipped_count = 0;
$results = [];

foreach ($all_articles as $index => $post_id) {
    echo "[" . ($index + 1) . "/" . count($all_articles) . "] Обработка статьи ID: $post_id\n";
    
    try {
        // Получаем текущую статью
        $current_post = get_post($post_id);
        if (!$current_post) {
            echo "  ❌ Статья не найдена\n";
            $error_count++;
            $results[] = "ID $post_id: Статья не найдена";
            continue;
        }
        
        echo "  📄 Заголовок: " . substr($current_post->post_title, 0, 50) . "...\n";
        echo "  📊 Текущая длина: " . strlen($current_post->post_content) . " символов\n";
        
        // Находим ревизию от 21 октября
        $revisions = wp_get_post_revisions($post_id);
        $oct21_revision = null;
        
        foreach ($revisions as $revision) {
            $date = new DateTime($revision->post_date);
            if ($date->format('Y-m-d') == '2025-10-21') {
                $oct21_revision = $revision;
                break;
            }
        }
        
        if (!$oct21_revision) {
            echo "  ⚠️ Ревизия от 21 октября не найдена, пропускаем\n";
            $skipped_count++;
            $results[] = "ID $post_id: Ревизия от 21 октября не найдена";
            continue;
        }
        
        echo "  🔍 Найдена ревизия от 21 октября (ID: {$oct21_revision->ID})\n";
        echo "  📊 Длина ревизии: " . strlen($oct21_revision->post_content) . " символов\n";
        
        // Анализируем структуру текущей статьи
        $current_content = $current_post->post_content;
        $intro_section_end = strpos($current_content, '</section>');
        
        $new_content = '';
        if ($intro_section_end !== false) {
            // Сохраняем структуру до конца intro-section
            $structure_part = substr($current_content, 0, $intro_section_end);
            $new_content = $structure_part . "\n\n" . $oct21_revision->post_content;
        } else {
            // Если структура не найдена, заменяем весь контент
            $new_content = $oct21_revision->post_content;
        }
        
        // Обновляем статью
        $update_result = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content
        ));
        
        if ($update_result && !is_wp_error($update_result)) {
            echo "  ✅ Успешно восстановлена! Новая длина: " . strlen($new_content) . " символов\n";
            $success_count++;
            $results[] = "ID $post_id: Успешно восстановлена";
        } else {
            echo "  ❌ Ошибка при обновлении\n";
            if (is_wp_error($update_result)) {
                echo "  🔍 Ошибка: " . $update_result->get_error_message() . "\n";
            }
            $error_count++;
            $results[] = "ID $post_id: Ошибка при обновлении";
        }
        
    } catch (Exception $e) {
        echo "  ❌ Исключение: " . $e->getMessage() . "\n";
        $error_count++;
        $results[] = "ID $post_id: Исключение - " . $e->getMessage();
    }
    
    echo "\n";
    
    // Небольшая пауза каждые 10 статей
    if (($index + 1) % 10 == 0) {
        echo "⏸️ Пауза 2 секунды...\n";
        sleep(2);
    }
}

// Очищаем кеш
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "🧹 Кеш очищен\n";
}

echo "\n=== ИТОГОВАЯ СТАТИСТИКА ===\n";
echo "✅ Успешно восстановлено: $success_count\n";
echo "❌ Ошибок: $error_count\n";
echo "⚠️ Пропущено: $skipped_count\n";
echo "📊 Всего обработано: " . count($all_articles) . "\n\n";

echo "=== ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ ===\n";
foreach ($results as $result) {
    echo $result . "\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";
?>

