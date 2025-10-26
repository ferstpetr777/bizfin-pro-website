<?php
require_once('wp-config.php');

// Массив совпадений из предыдущего анализа (статья ID => изображение ID)
$matches = array(
    array('post_id' => 2523, 'image_id' => 2632),
    array('post_id' => 2522, 'image_id' => 2631),
    array('post_id' => 2521, 'image_id' => 2705),
    array('post_id' => 2520, 'image_id' => 2737),
    array('post_id' => 2519, 'image_id' => 2736),
    array('post_id' => 2512, 'image_id' => 2719),
    array('post_id' => 2507, 'image_id' => 2681),
    array('post_id' => 2506, 'image_id' => 2702),
    array('post_id' => 2503, 'image_id' => 2689),
    array('post_id' => 2501, 'image_id' => 2680),
    array('post_id' => 2500, 'image_id' => 2670),
    array('post_id' => 2499, 'image_id' => 2718),
    array('post_id' => 2498, 'image_id' => 2710),
    array('post_id' => 2497, 'image_id' => 2706),
    array('post_id' => 2496, 'image_id' => 2700),
    array('post_id' => 2494, 'image_id' => 2694),
    array('post_id' => 2493, 'image_id' => 2676),
    array('post_id' => 2492, 'image_id' => 2667),
    array('post_id' => 2491, 'image_id' => 2663),
    array('post_id' => 2488, 'image_id' => 2727),
    array('post_id' => 2487, 'image_id' => 2703),
    array('post_id' => 2485, 'image_id' => 2696),
    array('post_id' => 2484, 'image_id' => 2687),
    array('post_id' => 2483, 'image_id' => 2722),
    array('post_id' => 2482, 'image_id' => 2726),
    array('post_id' => 2481, 'image_id' => 2711),
    array('post_id' => 2480, 'image_id' => 2742),
    array('post_id' => 2479, 'image_id' => 2740),
    array('post_id' => 2475, 'image_id' => 2657),
    array('post_id' => 2473, 'image_id' => 2745),
    array('post_id' => 2472, 'image_id' => 2746),
    array('post_id' => 2468, 'image_id' => 2714),
    array('post_id' => 2467, 'image_id' => 2683),
    array('post_id' => 2466, 'image_id' => 2674),
    array('post_id' => 2465, 'image_id' => 2691),
    array('post_id' => 2464, 'image_id' => 2748),
    array('post_id' => 2461, 'image_id' => 2715),
    array('post_id' => 2460, 'image_id' => 2666),
    array('post_id' => 2459, 'image_id' => 2678),
    array('post_id' => 2457, 'image_id' => 2724),
    array('post_id' => 2456, 'image_id' => 2720),
    array('post_id' => 2455, 'image_id' => 2716),
    array('post_id' => 2454, 'image_id' => 2669),
    array('post_id' => 2453, 'image_id' => 2664),
    array('post_id' => 2452, 'image_id' => 2658),
    array('post_id' => 2450, 'image_id' => 2649),
    array('post_id' => 2449, 'image_id' => 2717),
    array('post_id' => 2448, 'image_id' => 2713),
    array('post_id' => 2446, 'image_id' => 2668),
    array('post_id' => 2445, 'image_id' => 2661),
    array('post_id' => 2443, 'image_id' => 2660),
    array('post_id' => 2442, 'image_id' => 2721),
    array('post_id' => 2440, 'image_id' => 2655),
    array('post_id' => 2438, 'image_id' => 2648),
    array('post_id' => 2437, 'image_id' => 2693),
    array('post_id' => 2436, 'image_id' => 2650),
    array('post_id' => 2434, 'image_id' => 2675),
    array('post_id' => 2432, 'image_id' => 2673),
    array('post_id' => 2431, 'image_id' => 2699),
    array('post_id' => 2430, 'image_id' => 2653)
);

$fixed_count = 0;
$already_correct_count = 0;
$not_found_count = 0;
$total_processed = 0;

echo "=== ПРИНУДИТЕЛЬНАЯ ЗАМЕНА ФАВИКОНОВ НА ПРАВИЛЬНЫЕ ИЗОБРАЖЕНИЯ ===\n\n";

foreach ($matches as $match) {
    $post_id = $match['post_id'];
    $correct_image_id = $match['image_id'];
    
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $current_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    // Проверяем, что текущее изображение - это фавикон
    $current_image_file = get_attached_file($current_thumbnail_id);
    $is_favicon = false;
    
    if ($current_image_file && stripos($current_image_file, 'cropped-Бизнес-Финанс-—-фавикон') !== false) {
        $is_favicon = true;
    }
    
    if (!$is_favicon) {
        echo "⚠️  Статья ID $post_id ($post_date): '$post_title' - не имеет фавикон, пропускаем.\n";
        continue;
    }
    
    // ПРИНУДИТЕЛЬНО устанавливаем правильное изображение
    set_post_thumbnail($post_id, $correct_image_id);
    
    // Проверяем, что замена прошла успешно
    $new_thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    if ($new_thumbnail_id == $correct_image_id) {
        echo "✔ ПРИНУДИТЕЛЬНО заменен фавикон на правильное изображение для статьи ID $post_id ($post_date): '$post_title' (Изображение ID: $correct_image_id)\n";
        $fixed_count++;
    } else {
        echo "✗ ОШИБКА: не удалось заменить фавикон для статьи ID $post_id ($post_date): '$post_title'\n";
        $not_found_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПРИНУДИТЕЛЬНОЙ ЗАМЕНЫ ===\n";
echo "Заменено фавиконов: $fixed_count\n";
echo "Уже имели правильные изображения: $already_correct_count\n";
echo "Ошибки замены: $not_found_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш после всех операций
wp_cache_flush();
echo "Кэш очищен.\n";
?>
