<?php
/**
 * Скрипт для публикации статьи "Документы для получения банковской гарантии"
 * через плагин BizFin SEO Article Generator
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

// Проверяем, что плагин активен
if (!class_exists('BizFin_SEO_Article_Generator')) {
    die('Плагин BizFin SEO Article Generator не активен');
}

// Создаем экземпляр плагина
$bsag = BizFin_SEO_Article_Generator::get_instance();

// Параметры статьи
$article_params = [
    'keyword' => 'документы для получения банковской гарантии',
    'title' => 'Документы для получения банковской гарантии: полный список и требования',
    'meta_description' => 'Полный список документов для получения банковской гарантии. Учредительные, финансовые, договорные документы. Образцы и шаблоны для скачивания.',
    'content_type' => 'informational',
    'target_audience' => 'accountants_lawyers',
    'word_count' => 2500,
    'cta_type' => 'download_templates',
    'modules' => [
        'interactive_list' => true,
        'table_of_contents' => true,
        'download_templates' => true,
        'faq' => true
    ]
];

// Читаем HTML контент
$html_content = file_get_contents('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/documents-bank-guarantee-article.html');

// Извлекаем контент между body тегами
$dom = new DOMDocument();
$dom->loadHTML($html_content);
$xpath = new DOMXPath($dom);
$body_content = $xpath->query('//body')->item(0);

// Конвертируем HTML в WordPress контент
$wp_content = '';
foreach ($body_content->childNodes as $node) {
    $wp_content .= $dom->saveHTML($node);
}

// Создаем пост
$post_data = [
    'post_title' => $article_params['title'],
    'post_content' => $wp_content,
    'post_status' => 'publish',
    'post_type' => 'post',
    'post_author' => 1, // ID администратора
    'meta_input' => [
        '_bsag_generated' => true,
        '_bsag_keyword' => $article_params['keyword'],
        '_bsag_content_type' => $article_params['content_type'],
        '_bsag_target_audience' => $article_params['target_audience'],
        '_bsag_word_count' => $article_params['word_count'],
        '_bsag_cta_type' => $article_params['cta_type'],
        '_bsag_modules' => json_encode($article_params['modules']),
        '_yoast_wpseo_title' => $article_params['title'],
        '_yoast_wpseo_metadesc' => $article_params['meta_description'],
        '_yoast_wpseo_focuskw' => $article_params['keyword']
    ]
];

// Вставляем пост
$post_id = wp_insert_post($post_data);

if ($post_id && !is_wp_error($post_id)) {
    echo "Статья успешно опубликована!\n";
    echo "ID поста: $post_id\n";
    echo "URL: " . get_permalink($post_id) . "\n";
    
    // Устанавливаем категорию
    $category_id = get_cat_ID('Банковские гарантии');
    if ($category_id) {
        wp_set_post_categories($post_id, [$category_id]);
    }
    
    // Устанавливаем теги
    $tags = ['документы', 'банковская гарантия', 'требования банка', 'список документов'];
    wp_set_post_tags($post_id, $tags);
    
    // Устанавливаем featured image (если есть)
    $image_url = '/wp-content/uploads/2024/10/documents-bank-guarantee.jpg';
    if (file_exists(ABSPATH . $image_url)) {
        $attachment_id = attachment_url_to_postid(home_url($image_url));
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
    
    echo "Категория и теги установлены\n";
    echo "Статья доступна в блоге: " . get_permalink($post_id) . "\n";
    
} else {
    echo "Ошибка при публикации статьи\n";
    if (is_wp_error($post_id)) {
        echo "Ошибка: " . $post_id->get_error_message() . "\n";
    }
}

// Запускаем интеграции плагина
if ($post_id) {
    // Имитируем событие генерации статьи
    do_action('bsag_article_generated', $post_id, $article_params);
    
    echo "Интеграции плагина запущены\n";
}

echo "Скрипт завершен\n";
?>

