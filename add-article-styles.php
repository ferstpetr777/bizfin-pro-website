<?php
/**
 * Добавление стилей к статье "Документы для банковской гарантии на возврат аванса"
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Находим статью
$post = get_page_by_path('dokumenty-dlya-bankovskoy-garantii-na-vozvrat-avansa', OBJECT, 'post');

if ($post) {
    // Добавляем CSS класс к контенту
    $content = $post->post_content;
    
    // Обертываем контент в div с классом bsag-article
    $new_content = '<div class="bsag-article">' . $content . '</div>';
    
    // Обновляем статью
    wp_update_post([
        'ID' => $post->ID,
        'post_content' => $new_content
    ]);
    
    echo "✅ Стили добавлены к статье ID: {$post->ID}\n";
    echo "🔗 URL: " . get_permalink($post->ID) . "\n";
    
    // Добавляем мета-поле для подключения стилей
    update_post_meta($post->ID, '_bsag_use_article_styles', true);
    
    echo "📊 Мета-поле для стилей добавлено\n";
    
} else {
    echo "❌ Статья не найдена\n";
}
?>
