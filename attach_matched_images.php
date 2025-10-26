<?php
require_once('wp-config.php');

// Массив совпадений из предыдущего анализа
$matches = array(
    array('post_id' => 3172, 'image_id' => 3177),
    array('post_id' => 3169, 'image_id' => 3170),
    array('post_id' => 3160, 'image_id' => 3161),
    array('post_id' => 3157, 'image_id' => 3158),
    array('post_id' => 3153, 'image_id' => 3154),
    array('post_id' => 3152, 'image_id' => 3181),
    array('post_id' => 3144, 'image_id' => 3145),
    array('post_id' => 3138, 'image_id' => 3140),
    array('post_id' => 3135, 'image_id' => 3136),
    array('post_id' => 3127, 'image_id' => 3184),
    array('post_id' => 3126, 'image_id' => 3128),
    array('post_id' => 3122, 'image_id' => 3123),
    array('post_id' => 3111, 'image_id' => 3112),
    array('post_id' => 3109, 'image_id' => 3187),
    array('post_id' => 3102, 'image_id' => 3128),
    array('post_id' => 3097, 'image_id' => 3098),
    array('post_id' => 3081, 'image_id' => 3082),
    array('post_id' => 3072, 'image_id' => 3073),
    array('post_id' => 3068, 'image_id' => 3070),
    array('post_id' => 3064, 'image_id' => 3075),
    array('post_id' => 3057, 'image_id' => 3059),
    array('post_id' => 3055, 'image_id' => 3194),
    array('post_id' => 3048, 'image_id' => 3050),
    array('post_id' => 3039, 'image_id' => 3050),
    array('post_id' => 3028, 'image_id' => 3031),
    array('post_id' => 3027, 'image_id' => 3029),
    array('post_id' => 3017, 'image_id' => 3018),
    array('post_id' => 3016, 'image_id' => 3197),
    array('post_id' => 3007, 'image_id' => 3008),
    array('post_id' => 2998, 'image_id' => 2999),
    array('post_id' => 2991, 'image_id' => 2992),
    array('post_id' => 2986, 'image_id' => 2988),
    array('post_id' => 2975, 'image_id' => 3200),
    array('post_id' => 2967, 'image_id' => 2971),
    array('post_id' => 2966, 'image_id' => 3200),
    array('post_id' => 2960, 'image_id' => 2961),
    array('post_id' => 2953, 'image_id' => 2957),
    array('post_id' => 2948, 'image_id' => 2951),
    array('post_id' => 2947, 'image_id' => 2949),
    array('post_id' => 2944, 'image_id' => 2945),
    array('post_id' => 2943, 'image_id' => 3205),
    array('post_id' => 2940, 'image_id' => 2941),
    array('post_id' => 2934, 'image_id' => 2936),
    array('post_id' => 2931, 'image_id' => 2935),
    array('post_id' => 2928, 'image_id' => 2929),
    array('post_id' => 2923, 'image_id' => 2924),
    array('post_id' => 2911, 'image_id' => 2912),
    array('post_id' => 2907, 'image_id' => 2908),
    array('post_id' => 2904, 'image_id' => 2905),
    array('post_id' => 2894, 'image_id' => 2898),
    array('post_id' => 2887, 'image_id' => 2896),
    array('post_id' => 2877, 'image_id' => 2878),
    array('post_id' => 2872, 'image_id' => 2875),
    array('post_id' => 2871, 'image_id' => 2873),
    array('post_id' => 2863, 'image_id' => 2866),
    array('post_id' => 2859, 'image_id' => 2860),
    array('post_id' => 2852, 'image_id' => 2853),
    array('post_id' => 2849, 'image_id' => 2850),
    array('post_id' => 2843, 'image_id' => 2847),
    array('post_id' => 2833, 'image_id' => 2837),
    array('post_id' => 2830, 'image_id' => 2831),
    array('post_id' => 2827, 'image_id' => 2828),
    array('post_id' => 2816, 'image_id' => 2817),
    array('post_id' => 2798, 'image_id' => 2818),
    array('post_id' => 2476, 'image_id' => 2695),
    array('post_id' => 2474, 'image_id' => 2652),
    array('post_id' => 2470, 'image_id' => 2731),
    array('post_id' => 2463, 'image_id' => 2672),
    array('post_id' => 2439, 'image_id' => 2633),
    array('post_id' => 2435, 'image_id' => 2656),
    array('post_id' => 2433, 'image_id' => 2679),
    array('post_id' => 2429, 'image_id' => 2662),
    array('post_id' => 2428, 'image_id' => 2684),
    array('post_id' => 2426, 'image_id' => 2707),
    array('post_id' => 2425, 'image_id' => 2753),
    array('post_id' => 2423, 'image_id' => 2701),
    array('post_id' => 2422, 'image_id' => 2723),
    array('post_id' => 2420, 'image_id' => 2659),
    array('post_id' => 2067, 'image_id' => 2758)
);

$attached_count = 0;
$already_attached = 0;
$errors = array();

echo "=== ПРИВЯЗКА НАЙДЕННЫХ ИЗОБРАЖЕНИЙ К СТАТЬЯМ ===\n\n";

foreach ($matches as $match) {
    $post_id = $match['post_id'];
    $image_id = $match['image_id'];
    
    $post_title = get_the_title($post_id);
    $image_title = get_the_title($image_id);
    
    // Проверяем, есть ли уже главное изображение
    $current_thumbnail = get_post_meta($post_id, '_thumbnail_id', true);
    
    if ($current_thumbnail && $current_thumbnail == $image_id) {
        echo "✓ Статья ID $post_id уже имеет правильное изображение ID $image_id\n";
        $already_attached++;
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($image_id);
    if (!$image_path || !file_exists($image_path)) {
        echo "✗ Файл изображения не найден для ID $image_id: $image_title\n";
        $errors[] = "Файл не найден для изображения ID $image_id";
        continue;
    }
    
    // Привязываем изображение к статье
    $result = update_post_meta($post_id, '_thumbnail_id', $image_id);
    
    if ($result !== false) {
        echo "✔ Привязано: Статья ID $post_id '$post_title' -> Изображение ID $image_id '$image_title'\n";
        $attached_count++;
    } else {
        echo "✗ Ошибка привязки: Статья ID $post_id -> Изображение ID $image_id\n";
        $errors[] = "Ошибка привязки для статьи ID $post_id";
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ПРИВЯЗКИ ===\n";
echo "Успешно привязано: $attached_count\n";
echo "Уже имели правильные изображения: $already_attached\n";
echo "Ошибок: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n=== ОШИБКИ ===\n";
    foreach ($errors as $error) {
        echo "$error\n";
    }
}
