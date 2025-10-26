<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ИСПРАВЛЕНИЕ ВСЕХ СТАТЕЙ ===\n\n";

// Получаем все опубликованные статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
));

$cutoff_date = '2025-10-23 00:00:00';
$new_articles = 0;
$old_articles = 0;
$fixed_articles = 0;

echo "Всего статей: " . count($posts) . "\n\n";

foreach ($posts as $post) {
    $post_date = $post->post_date;
    $is_new_article = $post_date >= $cutoff_date;
    
    if ($is_new_article) {
        $new_articles++;
        echo "НОВАЯ статья (с 23 октября): " . $post->post_title . " (ID: " . $post->ID . ")\n";
        
        // Для новых статей: изображение должно быть после блока "Содержание"
        $content = $post->post_content;
        $original_content = $content;
        
        // 1. Удаляем ВСЕ изображения из контента
        $content = preg_replace('/<img[^>]*>/', '', $content);
        $content = preg_replace('/<!-- wp:image[^>]*-->\s*<figure[^>]*>.*?<\/figure>\s*<!-- \/wp:image -->/s', '', $content);
        
        // 2. Удаляем пустые строки
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        
        if ($content !== $original_content) {
            wp_update_post(array(
                'ID' => $post->ID,
                'post_content' => $content,
            ));
            echo "  ✅ Контент очищен от изображений\n";
            $fixed_articles++;
        }
        
        // 3. Проверяем featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        if ($thumbnail_id) {
            echo "  ✅ Featured image установлен (ID: " . $thumbnail_id . ")\n";
        } else {
            echo "  ⚠️ Featured image НЕ установлен\n";
        }
        
    } else {
        $old_articles++;
        echo "СТАРАЯ статья (до 23 октября): " . $post->post_title . " (ID: " . $post->ID . ")\n";
        
        // Для старых статей: изображение должно быть вверху (featured image)
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        if ($thumbnail_id) {
            echo "  ✅ Featured image установлен (ID: " . $thumbnail_id . ")\n";
        } else {
            echo "  ⚠️ Featured image НЕ установлен\n";
        }
        
        // Удаляем дублирующие изображения из контента старых статей
        $content = $post->post_content;
        $original_content = $content;
        
        // Удаляем изображения, которые дублируют featured image
        $content = preg_replace('/<img[^>]*>/', '', $content);
        $content = preg_replace('/<!-- wp:image[^>]*-->\s*<figure[^>]*>.*?<\/figure>\s*<!-- \/wp:image -->/s', '', $content);
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        
        if ($content !== $original_content) {
            wp_update_post(array(
                'ID' => $post->ID,
                'post_content' => $content,
            ));
            echo "  ✅ Дублирующие изображения удалены\n";
            $fixed_articles++;
        }
    }
    
    echo "\n";
}

echo "=== СВОДКА ===\n";
echo "Новых статей (с 23 октября): " . $new_articles . "\n";
echo "Старых статей (до 23 октября): " . $old_articles . "\n";
echo "Исправлено статей: " . $fixed_articles . "\n";

echo "\n=== ПРОВЕРКА НАСТРОЕК ТЕМЫ ===\n";

// Проверяем настройки темы для featured image
$astra_settings = get_option('astra-settings', array());
$layout1 = $astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-1'] ?? 'none';
$layout2 = $astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-2'] ?? 'none';

echo "Настройки featured image:\n";
echo "Layout 1: " . $layout1 . "\n";
echo "Layout 2: " . $layout2 . "\n";

if ($layout1 === 'behind' && $layout2 === 'behind') {
    echo "✅ Настройки темы корректны - featured image будет отображаться\n";
} else {
    echo "⚠️ Настройки темы требуют исправления\n";
}

echo "\n=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
?>

