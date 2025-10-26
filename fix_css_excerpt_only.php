<?php
require_once('wp-config.php');

// Получаем все статьи, которые содержат CSS код в excerpt
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ CSS КОДА ТОЛЬКО В EXCERPT ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_excerpt = get_post_field('post_excerpt', $post_id);
    
    $needs_fix = false;
    $new_excerpt = $post_excerpt;
    
    // Проверяем excerpt на наличие CSS
    if (strpos($post_excerpt, '.intro {') !== false || 
        strpos($post_excerpt, '.toc {') !== false ||
        strpos($post_excerpt, 'border-radius:') !== false ||
        strpos($post_excerpt, 'border-left:') !== false ||
        strpos($post_excerpt, 'box-shadow:') !== false ||
        strpos($post_excerpt, 'var(—orange)') !== false) {
        
        // Удаляем CSS код из excerpt
        $new_excerpt = preg_replace('/\.intro\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/\.toc\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/border-radius:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/border-left:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/box-shadow:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/var\(—orange\)/', '', $new_excerpt);
        $new_excerpt = preg_replace('/border:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/solid[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/#e2e8f0/', '', $new_excerpt);
        $new_excerpt = preg_replace('/12px/', '', $new_excerpt);
        $new_excerpt = preg_replace('/4px/', '', $new_excerpt);
        $new_excerpt = preg_replace('/1px/', '', $new_excerpt);
        $new_excerpt = preg_replace('/2px/', '', $new_excerpt);
        
        // Убираем лишние пробелы и переносы
        $new_excerpt = preg_replace('/\s+/', ' ', $new_excerpt);
        $new_excerpt = trim($new_excerpt);
        
        $needs_fix = true;
    }
    
    if ($needs_fix) {
        // Обновляем только excerpt
        wp_update_post(array(
            'ID' => $post_id,
            'post_excerpt' => $new_excerpt,
        ));
        
        echo "✔ Исправлен excerpt статьи ID $post_id: '$post_title'\n";
        $fixed_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Исправлено excerpt: $fixed_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
