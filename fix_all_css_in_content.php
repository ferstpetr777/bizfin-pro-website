<?php
require_once('wp-config.php');

// Получаем все статьи, которые содержат CSS код в контенте
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$fixed_count = 0;
$total_processed = 0;

echo "=== ИСПРАВЛЕНИЕ ВСЕГО CSS КОДА В КОНТЕНТЕ СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post_excerpt = get_post_field('post_excerpt', $post_id);
    
    $needs_fix = false;
    $new_content = $post_content;
    $new_excerpt = $post_excerpt;
    
    // Проверяем контент на наличие CSS
    if (strpos($post_content, ':root') !== false || 
        strpos($post_content, '—orange:') !== false ||
        strpos($post_content, '—text:') !== false ||
        strpos($post_content, 'body {') !== false ||
        strpos($post_content, 'font-family:') !== false ||
        strpos($post_content, 'line-height:') !== false) {
        
        // Удаляем все виды CSS кода из контента
        $new_content = preg_replace('/:root\s*\{[^}]*\}/', '', $new_content);
        $new_content = preg_replace('/body\s*\{[^}]*\}/', '', $new_content);
        $new_content = preg_replace('/—orange[^;]*;/', '', $new_content);
        $new_content = preg_replace('/—text[^;]*;/', '', $new_content);
        $new_content = preg_replace('/—text-muted[^;]*;/', '', $new_content);
        $new_content = preg_replace('/—orange-2[^;]*;/', '', $new_content);
        $new_content = preg_replace('/font-family:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/line-height:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/color:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/background:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/margin:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/padding:[^;]*;/', '', $new_content);
        
        // Убираем лишние пробелы и переносы
        $new_content = preg_replace('/\s+/', ' ', $new_content);
        $new_content = trim($new_content);
        
        $needs_fix = true;
    }
    
    // Проверяем excerpt на наличие CSS
    if (strpos($post_excerpt, ':root') !== false || 
        strpos($post_excerpt, '—orange:') !== false ||
        strpos($post_excerpt, '—text:') !== false ||
        strpos($post_excerpt, 'body {') !== false ||
        strpos($post_excerpt, 'font-family:') !== false ||
        strpos($post_excerpt, 'line-height:') !== false) {
        
        // Удаляем все виды CSS кода из excerpt
        $new_excerpt = preg_replace('/:root\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/body\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/—orange[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/—text[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/—text-muted[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/—orange-2[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/font-family:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/line-height:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/color:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/background:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/margin:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/padding:[^;]*;/', '', $new_excerpt);
        
        // Убираем лишние пробелы и переносы
        $new_excerpt = preg_replace('/\s+/', ' ', $new_excerpt);
        $new_excerpt = trim($new_excerpt);
        
        $needs_fix = true;
    }
    
    if ($needs_fix) {
        // Обновляем статью
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content,
            'post_excerpt' => $new_excerpt,
        ));
        
        echo "✔ Исправлена статья ID $post_id: '$post_title'\n";
        $fixed_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Исправлено статей: $fixed_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
