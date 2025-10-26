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

echo "=== ТОЧНОЕ ИСПРАВЛЕНИЕ CSS КОДА В КОНТЕНТЕ СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post_excerpt = get_post_field('post_excerpt', $post_id);
    
    $needs_fix = false;
    $new_content = $post_content;
    $new_excerpt = $post_excerpt;
    
    // Проверяем контент на наличие CSS
    if (strpos($post_content, '.container {') !== false || 
        strpos($post_content, 'h1 {') !== false ||
        strpos($post_content, 'h2 {') !== false ||
        strpos($post_content, 'font-size:') !== false ||
        strpos($post_content, 'margin-bottom:') !== false) {
        
        // Удаляем CSS код более точно
        $new_content = preg_replace('/<title>.*?<\/title>/s', '', $new_content);
        $new_content = preg_replace('/\.container\s*\{[^}]*\}/', '', $new_content);
        $new_content = preg_replace('/h[1-6]\s*\{[^}]*\}/', '', $new_content);
        $new_content = preg_replace('/font-size:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/margin-[^;]*;/', '', $new_content);
        $new_content = preg_replace('/font-weight:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/line-height:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/color:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/background:[^;]*;/', '', $new_content);
        $new_content = preg_replace('/padding:[^;]*;/', '', $new_content);
        
        // Убираем лишние пробелы и переносы
        $new_content = preg_replace('/\s+/', ' ', $new_content);
        $new_content = trim($new_content);
        
        $needs_fix = true;
    }
    
    // Проверяем excerpt на наличие CSS
    if (strpos($post_excerpt, '.container {') !== false || 
        strpos($post_excerpt, 'h1 {') !== false ||
        strpos($post_excerpt, 'h2 {') !== false ||
        strpos($post_excerpt, 'font-size:') !== false ||
        strpos($post_excerpt, 'margin-bottom:') !== false) {
        
        // Удаляем CSS код из excerpt
        $new_excerpt = preg_replace('/<title>.*?<\/title>/s', '', $new_excerpt);
        $new_excerpt = preg_replace('/\.container\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/h[1-6]\s*\{[^}]*\}/', '', $new_excerpt);
        $new_excerpt = preg_replace('/font-size:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/margin-[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/font-weight:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/line-height:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/color:[^;]*;/', '', $new_excerpt);
        $new_excerpt = preg_replace('/background:[^;]*;/', '', $new_excerpt);
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
