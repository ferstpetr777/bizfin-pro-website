<?php
require_once('wp-config.php');

// Получаем все статьи от 19 и 7 октября
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$fixed_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ ФОРМАТИРОВАНИЯ СТАТЕЙ (ВЕРСИЯ 2) ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    $needs_fix = false;
    $new_content = $content;
    
    // 1. Удаляем мета-описание из текста
    if (strpos($content, 'Мета-описание:') !== false) {
        $new_content = preg_replace('/Мета-описание:.*?(?=\n\n|\n[А-Я]|$)/s', '', $new_content);
        $needs_fix = true;
    }
    
    // 2. Удаляем ключевые слова из текста
    if (strpos($content, 'Ключевые слова:') !== false) {
        $new_content = preg_replace('/Ключевые слова:.*?(?=\n\n|\n[А-Я]|$)/s', '', $new_content);
        $needs_fix = true;
    }
    
    // 3. Заменяем длинные тире на обычные дефисы (включая HTML-сущности)
    if (strpos($content, '—') !== false || strpos($content, '&#8212;') !== false) {
        $new_content = str_replace('—', '-', $new_content);
        $new_content = str_replace('&#8212;', '-', $new_content);
        $needs_fix = true;
    }
    
    // 4. Очищаем лишние пробелы и переносы
    $new_content = preg_replace('/\n{3,}/', "\n\n", $new_content);
    $new_content = trim($new_content);
    
    if ($needs_fix) {
        // Обновляем статью
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content,
        ));
        
        echo "✓ Исправлено форматирование статьи ID $post_id ($post_date): '$post_title'\n";
        $fixed_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено статей: $fixed_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
