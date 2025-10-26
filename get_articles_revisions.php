<?php
require_once('wp-config.php');

// ID статей от 19 октября 2025 года
$october_19_articles = [
    2527, 2526, 2525, 2524, 2523, 2522, 2521, 2520, 2519, 2518, 2517, 2516, 2515, 2514, 2513, 2512, 2511, 2510, 2509, 2508, 2507, 2506, 2505, 2504, 2503, 2502, 2501, 2500, 2499, 2498, 2497, 2496, 2495, 2494, 2493, 2492, 2491, 2490, 2489, 2488, 2487, 2486, 2485, 2484, 2483, 2482, 2481, 2480, 2479, 2478, 2477, 2476, 2475, 2474, 2473, 2472, 2471, 2470, 2469, 2468, 2467, 2466, 2465, 2464, 2463, 2462, 2461, 2460, 2459, 2458, 2457, 2456, 2455, 2454, 2453, 2452, 2451, 2450, 2449, 2448, 2447, 2446, 2445, 2444, 2443, 2442, 2441, 2440, 2439, 2438, 2437, 2436, 2435, 2434, 2433, 2432, 2431, 2430, 2429, 2428, 2427, 2426, 2425, 2423, 2422, 2421, 2420
];

// ID статей от 7 октября 2025 года
$october_7_articles = [2060, 2046];

echo "=== АНАЛИЗ РЕВИЗИЙ СТАТЕЙ ===\n\n";

// Функция для получения ревизий статьи
function get_post_revisions($post_id) {
    $revisions = wp_get_post_revisions($post_id);
    $revisions_data = [];
    
    if ($revisions) {
        foreach ($revisions as $revision) {
            $revisions_data[] = [
                'id' => $revision->ID,
                'date' => $revision->post_date,
                'date_gmt' => $revision->post_date_gmt,
                'modified' => $revision->post_modified,
                'modified_gmt' => $revision->post_modified_gmt,
                'author' => $revision->post_author
            ];
        }
    }
    
    return $revisions_data;
}

// Обработка статей от 19 октября
echo "📅 СТАТЬИ ОТ 19 ОКТЯБРЯ 2025 ГОДА:\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($october_19_articles as $index => $post_id) {
    $post_title = get_the_title($post_id);
    $revisions = get_post_revisions($post_id);
    
    echo "ID: $post_id\n";
    echo "Заголовок: " . substr($post_title, 0, 80) . (strlen($post_title) > 80 ? "..." : "") . "\n";
    echo "Количество ревизий: " . count($revisions) . "\n";
    
    if (!empty($revisions)) {
        echo "Ревизии:\n";
        foreach ($revisions as $i => $revision) {
            $date = new DateTime($revision['date']);
            $formatted_date = $date->format('d.m.Y H:i:s');
            echo "  " . ($i + 1) . ". ID ревизии: {$revision['id']} | Дата: $formatted_date\n";
        }
    } else {
        echo "Ревизии: Нет ревизий\n";
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

// Обработка статей от 7 октября
echo "📅 СТАТЬИ ОТ 7 ОКТЯБРЯ 2025 ГОДА:\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($october_7_articles as $index => $post_id) {
    $post_title = get_the_title($post_id);
    $revisions = get_post_revisions($post_id);
    
    echo "ID: $post_id\n";
    echo "Заголовок: " . substr($post_title, 0, 80) . (strlen($post_title) > 80 ? "..." : "") . "\n";
    echo "Количество ревизий: " . count($revisions) . "\n";
    
    if (!empty($revisions)) {
        echo "Ревизии:\n";
        foreach ($revisions as $i => $revision) {
            $date = new DateTime($revision['date']);
            $formatted_date = $date->format('d.m.Y H:i:s');
            echo "  " . ($i + 1) . ". ID ревизии: {$revision['id']} | Дата: $formatted_date\n";
        }
    } else {
        echo "Ревизии: Нет ревизий\n";
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

// Общая статистика
echo "📊 ОБЩАЯ СТАТИСТИКА РЕВИЗИЙ:\n";
echo str_repeat("=", 50) . "\n";

$total_revisions_19 = 0;
$total_revisions_7 = 0;

foreach ($october_19_articles as $post_id) {
    $revisions = get_post_revisions($post_id);
    $total_revisions_19 += count($revisions);
}

foreach ($october_7_articles as $post_id) {
    $revisions = get_post_revisions($post_id);
    $total_revisions_7 += count($revisions);
}

echo "Всего статей от 19 октября: " . count($october_19_articles) . "\n";
echo "Всего ревизий у статей от 19 октября: $total_revisions_19\n";
echo "Среднее количество ревизий на статью (19 октября): " . round($total_revisions_19 / count($october_19_articles), 2) . "\n\n";

echo "Всего статей от 7 октября: " . count($october_7_articles) . "\n";
echo "Всего ревизий у статей от 7 октября: $total_revisions_7\n";
echo "Среднее количество ревизий на статью (7 октября): " . round($total_revisions_7 / count($october_7_articles), 2) . "\n\n";

echo "ОБЩИЙ ИТОГ:\n";
echo "Всего статей: " . (count($october_19_articles) + count($october_7_articles)) . "\n";
echo "Всего ревизий: " . ($total_revisions_19 + $total_revisions_7) . "\n";
echo "Общее среднее количество ревизий на статью: " . round(($total_revisions_19 + $total_revisions_7) / (count($october_19_articles) + count($october_7_articles)), 2) . "\n";
?>

