<?php
require_once('wp-config.php');

// ID статей от 19 октября 2025 года
$october_19_articles = [
    2527, 2526, 2525, 2524, 2523, 2522, 2520, 2519, 2518, 2517, 2516, 2515, 2513, 2512, 2510, 2508, 2506, 2505, 2504, 2503, 2502, 2501, 2499, 2498, 2497, 2496, 2495, 2494, 2493, 2492, 2490, 2489, 2487, 2486, 2485, 2484, 2482, 2481, 2478, 2477, 2476, 2474, 2473, 2470, 2468, 2466, 2465, 2463, 2461, 2460, 2456, 2455, 2454, 2452, 2450, 2449, 2448, 2445, 2444, 2442, 2441, 2440, 2439, 2437, 2436, 2435, 2434, 2433, 2432, 2430, 2429, 2428, 2426, 2425, 2423, 2422, 2421, 2420
];

// ID статей от 7 октября 2025 года
$october_7_articles = [2046];

// Объединяем все статьи
$all_articles = array_merge($october_19_articles, $october_7_articles);

echo "=== ПОИСК ПРАВИЛЬНЫХ РЕВИЗИЙ ДЛЯ НЕВОССТАНОВЛЕННЫХ СТАТЕЙ ===\n";
echo "Всего статей для анализа: " . count($all_articles) . "\n\n";

$articles_with_correct_revisions = [];
$articles_without_correct_revisions = [];

foreach ($all_articles as $index => $post_id) {
    echo "[" . ($index + 1) . "/" . count($all_articles) . "] Анализ статьи ID: $post_id\n";
    
    $current_post = get_post($post_id);
    if (!$current_post) {
        echo "  ❌ Статья не найдена\n";
        continue;
    }
    
    echo "  📄 Заголовок: " . substr($current_post->post_title, 0, 50) . "...\n";
    
    // Проверяем текущий контент
    $current_content = $current_post->post_content;
    $has_wrong_content = strpos($current_content, 'Требования к банковской гарантии на возврат аванса') !== false;
    
    if (!$has_wrong_content) {
        echo "  ✅ Статья уже имеет правильный контент\n";
        continue;
    }
    
    echo "  🔍 Поиск правильной ревизии...\n";
    
    // Получаем все ревизии
    $revisions = wp_get_post_revisions($post_id);
    $correct_revision = null;
    
    // Ищем ревизию с правильным контентом
    foreach ($revisions as $revision) {
        $revision_content = $revision->post_content;
        
        // Проверяем, что ревизия НЕ содержит неправильный контент
        $has_wrong_content_in_revision = strpos($revision_content, 'Требования к банковской гарантии на возврат аванса') !== false;
        
        // Проверяем, что ревизия содержит осмысленный контент
        $has_meaningful_content = strlen($revision_content) > 1000 && 
                                 !empty(trim(strip_tags($revision_content)));
        
        if (!$has_wrong_content_in_revision && $has_meaningful_content) {
            $correct_revision = $revision;
            break;
        }
    }
    
    if ($correct_revision) {
        $date = new DateTime($correct_revision->post_date);
        echo "  ✅ Найдена правильная ревизия (ID: {$correct_revision->ID}, дата: " . $date->format('d.m.Y H:i:s') . ")\n";
        echo "  📊 Длина контента: " . strlen($correct_revision->post_content) . " символов\n";
        
        $articles_with_correct_revisions[] = [
            'post_id' => $post_id,
            'revision_id' => $correct_revision->ID,
            'revision_date' => $correct_revision->post_date,
            'content_length' => strlen($correct_revision->post_content)
        ];
    } else {
        echo "  ❌ Правильная ревизия не найдена\n";
        $articles_without_correct_revisions[] = $post_id;
    }
    
    echo "\n";
    
    // Пауза каждые 10 статей
    if (($index + 1) % 10 == 0) {
        echo "⏸️ Пауза 1 секунда...\n";
        sleep(1);
    }
}

echo "=== ИТОГОВАЯ СТАТИСТИКА ===\n";
echo "✅ Статей с правильными ревизиями: " . count($articles_with_correct_revisions) . "\n";
echo "❌ Статей без правильных ревизий: " . count($articles_without_correct_revisions) . "\n\n";

echo "=== СТАТЬИ С НАЙДЕННЫМИ РЕВИЗИЯМИ ===\n";
foreach ($articles_with_correct_revisions as $article) {
    echo "ID {$article['post_id']}: Ревизия {$article['revision_id']} от {$article['revision_date']} ({$article['content_length']} символов)\n";
}

echo "\n=== СТАТЬИ БЕЗ ПРАВИЛЬНЫХ РЕВИЗИЙ ===\n";
foreach ($articles_without_correct_revisions as $post_id) {
    echo "ID $post_id\n";
}

echo "\n=== ЗАВЕРШЕНО ===\n";
?>

