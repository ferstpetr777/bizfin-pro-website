<?php
/**
 * Скрипт для публикации статьи "Виды банковских гарантий"
 */

// Подключаем WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

echo "=== Публикация статьи 'Виды банковских гарантий' ===\n\n";

// Читаем HTML контент статьи
$article_content = file_get_contents(__DIR__ . '/generated-article-types-bank-guarantees.html');

if (!$article_content) {
    die("❌ Не удалось загрузить контент статьи\n");
}

echo "✓ Контент статьи загружен\n";

// Извлекаем основные элементы из HTML
$title = 'Виды банковских гарантий: полное руководство для руководителей проектов';
$meta_description = 'Подробное руководство по видам банковских гарантий: обеспечение заявки, исполнение контракта, возврат аванса. Сравнительная таблица, условия, риски и практические рекомендации для руководителей проектов.';
$keywords = 'виды банковских гарантий, обеспечение заявки, исполнение контракта, возврат аванса, гарантийные обязательства, таможенные платежи, независимая гарантия';

// Извлекаем основной контент (убираем HTML структуру)
$content_start = strpos($article_content, '<article class="bank-guarantee-types-article">');
$content_end = strpos($article_content, '</article>');
if ($content_start !== false && $content_end !== false) {
    $article_body = substr($article_content, $content_start, $content_end - $content_start + 9);
} else {
    $article_body = $article_content;
}

// Создаем пост
$post_data = [
    'post_title' => $title,
    'post_content' => $article_body,
    'post_status' => 'publish',
    'post_type' => 'post',
    'post_author' => 1,
    'post_excerpt' => $meta_description,
    'meta_input' => [
        '_bsag_generated_article' => true,
        '_bsag_keyword' => 'виды банковских гарантий',
        '_bsag_article_type' => 'informational',
        '_bsag_target_audience' => 'project_managers',
        '_bsag_word_count' => 2500,
        '_bsag_seo_optimized' => true,
        '_yoast_wpseo_title' => $title . ' | BizFin Pro',
        '_yoast_wpseo_metadesc' => $meta_description,
        '_yoast_wpseo_focuskw' => 'виды банковских гарантий',
        '_yoast_wpseo_canonical' => 'https://bizfin-pro.ru/vidy-bankovskih-garantij/',
        '_yoast_wpseo_opengraph-title' => $title,
        '_yoast_wpseo_opengraph-description' => $meta_description,
        '_yoast_wpseo_twitter-title' => $title,
        '_yoast_wpseo_twitter-description' => $meta_description
    ]
];

// Вставляем пост
$post_id = wp_insert_post($post_data);

if (is_wp_error($post_id)) {
    die("❌ Ошибка создания поста: " . $post_id->get_error_message() . "\n");
}

echo "✓ Пост создан с ID: " . $post_id . "\n";

// Устанавливаем категорию
$category_id = wp_create_category('Банковские гарантии');
wp_set_post_categories($post_id, [$category_id]);

echo "✓ Категория установлена\n";

// Устанавливаем теги
$tags = ['банковские гарантии', 'финансы', 'бизнес', 'руководство', 'проекты'];
wp_set_post_tags($post_id, $tags);

echo "✓ Теги установлены\n";

// Устанавливаем slug
wp_update_post([
    'ID' => $post_id,
    'post_name' => 'vidy-bankovskih-garantij'
]);

echo "✓ Slug установлен\n";

// Получаем URL статьи
$article_url = get_permalink($post_id);
echo "✓ URL статьи: " . $article_url . "\n";

// Проверяем, что статья опубликована
$post = get_post($post_id);
if ($post && $post->post_status === 'publish') {
    echo "✅ Статья успешно опубликована!\n";
    echo "📊 Статистика:\n";
    echo "- ID статьи: " . $post_id . "\n";
    echo "- URL: " . $article_url . "\n";
    echo "- Статус: " . $post->post_status . "\n";
    echo "- Автор: " . get_the_author_meta('display_name', $post->post_author) . "\n";
    echo "- Дата публикации: " . $post->post_date . "\n";
    
    // Проверяем контент
    $word_count = str_word_count(strip_tags($post->post_content));
    echo "- Количество слов: " . $word_count . "\n";
    
    // Проверяем метаданные
    $yoast_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
    $yoast_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    
    echo "- SEO заголовок: " . ($yoast_title ? "✓ Установлен" : "❌ Не установлен") . "\n";
    echo "- SEO описание: " . ($yoast_desc ? "✓ Установлено" : "❌ Не установлено") . "\n";
    
    echo "\n🎉 Статья готова к просмотру!\n";
    echo "🔗 Перейдите по ссылке: " . $article_url . "\n";
    
} else {
    echo "❌ Ошибка публикации статьи\n";
}

echo "\n=== Публикация завершена ===\n";

