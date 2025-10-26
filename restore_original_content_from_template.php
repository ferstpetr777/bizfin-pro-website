<?php
require_once('wp-config.php');

// Получаем шаблонную статью с полным контентом
$template_post = get_post(3034); // "Требования к банковской гарантии"
if (!$template_post) {
    echo "❌ Шаблонная статья не найдена\n";
    exit;
}

echo "=== ВОССТАНОВЛЕНИЕ КОНТЕНТА ИЗ ШАБЛОНА ===\n";
echo "Шаблонная статья: {$template_post->post_title}\n";
echo "Длина контента шаблона: " . strlen($template_post->post_content) . " символов\n\n";

// Получаем все статьи от 19 и 7 октября
$october_19_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 19,
        ),
    ),
    'fields' => 'ids'
));

$october_7_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 7,
        ),
    ),
    'fields' => 'ids'
));

$all_posts = array_merge($october_19_posts, $october_7_posts);

echo "Всего статей для восстановления: " . count($all_posts) . "\n\n";

$restored_count = 0;

foreach ($all_posts as $post_id) {
    $post_title = get_the_title($post_id);
    
    echo "=== ВОССТАНОВЛЕНИЕ СТАТЬИ ID $post_id ===\n";
    echo "Заголовок: $post_title\n";
    
    // Создаем новый контент на основе шаблона
    $new_content = create_article_content($post_title, $template_post->post_content);
    
    // Обновляем статью
    try {
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content,
        ));
        
        echo "✅ Статья восстановлена\n";
        echo "Длина нового контента: " . strlen($new_content) . " символов\n";
        $restored_count++;
    } catch (Exception $e) {
        echo "❌ Ошибка при восстановлении: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "=== РЕЗУЛЬТАТ ===\n";
echo "Восстановлено статей: $restored_count\n";
echo "Всего обработано: " . count($all_posts) . "\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен\n";

function create_article_content($title, $template_content) {
    // Извлекаем основную структуру из шаблона
    $content = $template_content;
    
    // Заменяем заголовок в контенте
    $content = preg_replace('/<h1[^>]*>.*?<\/h1>/', '<h1>' . esc_html($title) . '</h1>', $content);
    
    // Заменяем мета-описание
    $content = preg_replace('/<meta name="description" content="[^"]*"/', '<meta name="description" content="' . esc_attr($title) . ': полное руководство с подробным описанием, практическими примерами и рекомендациями."', $content);
    
    // Адаптируем контент под тему статьи
    $content = adapt_content_for_topic($content, $title);
    
    return $content;
}

function adapt_content_for_topic($content, $title) {
    // Извлекаем ключевые слова из заголовка
    $keywords = extract_keywords_from_title($title);
    
    // Заменяем общие фразы на специфичные для темы
    $content = str_replace('банковская гарантия', $keywords['main_term'], $content);
    $content = str_replace('Банковская гарантия', ucfirst($keywords['main_term']), $content);
    $content = str_replace('БАНКОВСКАЯ ГАРАНТИЯ', strtoupper($keywords['main_term']), $content);
    
    return $content;
}

function extract_keywords_from_title($title) {
    // Извлекаем основные термины из заголовка
    $keywords = array();
    
    // Убираем номер статьи
    $clean_title = preg_replace('/\s*#\d+\s*$/', '', $title);
    
    // Извлекаем первое существительное как основной термин
    if (preg_match('/^([^:]+):/', $clean_title, $matches)) {
        $keywords['main_term'] = trim($matches[1]);
    } else {
        $keywords['main_term'] = $clean_title;
    }
    
    return $keywords;
}
