<?php
require_once('wp-config.php');

// Отключаем wptexturize для статей от 19 и 7 октября
add_filter('the_content', function($content) {
    global $post;
    
    if (!$post) {
        return $content;
    }
    
    $post_date = get_the_date('Y-m-d', $post->ID);
    
    // Проверяем, что статья от 19 или 7 октября
    if ($post_date === '2025-10-19' || $post_date === '2025-10-07') {
        // Отключаем wptexturize для этого контента
        remove_filter('the_content', 'wptexturize', 10);
        
        // Применяем остальные фильтры вручную
        $content = wpautop($content);
        $content = shortcode_unautop($content);
        $content = prepend_attachment($content);
        $content = wp_replace_insecure_home_url($content);
        $content = capital_P_dangit($content);
        $content = do_shortcode($content);
        $content = wp_filter_content_tags($content);
        $content = convert_smilies($content);
        
        return $content;
    }
    
    return $content;
}, 5); // Приоритет 5, чтобы выполниться до wptexturize

echo "Фильтр для отключения wptexturize добавлен\n";
