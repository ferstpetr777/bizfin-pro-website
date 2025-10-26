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
$not_found_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ МЕТАДАННЫХ ИЗОБРАЖЕНИЙ ===\n\n";

foreach ($matches as $match) {
    $post_id = $match['post_id'];
    $image_id = $match['image_id'];
    
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    
    // Получаем текущие метаданные изображения
    $current_file = get_attached_file($image_id);
    $current_guid = get_post_field('guid', $image_id);
    
    // Проверяем, указывает ли файл на фавикон
    if ($current_file && stripos($current_file, 'cropped-Бизнес-Финанс-—-фавикон') !== false) {
        echo "⚠️  Изображение ID $image_id для статьи '$post_title' указывает на фавикон: $current_file\n";
        
        // Ищем правильное изображение по названию статьи
        $correct_images = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'title' => $post_title,
            'fields' => 'ids'
        ));
        
        if (!empty($correct_images)) {
            $correct_image_id = $correct_images[0];
            $correct_file = get_attached_file($correct_image_id);
            $correct_guid = get_post_field('guid', $correct_image_id);
            
            if ($correct_file && file_exists($correct_file) && stripos($correct_file, 'cropped-Бизнес-Финанс-—-фавикон') === false) {
                // Копируем метаданные с правильного изображения
                $correct_metadata = wp_get_attachment_metadata($correct_image_id);
                $correct_file_meta = get_post_meta($correct_image_id, '_wp_attached_file', true);
                
                // Обновляем метаданные проблемного изображения
                update_post_meta($image_id, '_wp_attached_file', $correct_file_meta);
                wp_update_post(array(
                    'ID' => $image_id,
                    'guid' => $correct_guid
                ));
                wp_update_attachment_metadata($image_id, $correct_metadata);
                
                echo "✔ Исправлены метаданные для изображения ID $image_id (скопированы с ID $correct_image_id)\n";
                $fixed_count++;
            } else {
                echo "✗ Не найдено правильное изображение для статьи '$post_title'\n";
                $not_found_count++;
            }
        } else {
            echo "✗ Не найдено изображение с названием '$post_title'\n";
            $not_found_count++;
        }
    } else {
        echo "✓ Изображение ID $image_id для статьи '$post_title' уже корректно\n";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено метаданных: $fixed_count\n";
echo "Не найдено правильных изображений: $not_found_count\n";
echo "Всего обработано: $total_processed\n";

// Очищаем кэш после всех операций
wp_cache_flush();
echo "Кэш очищен.\n";
?>
