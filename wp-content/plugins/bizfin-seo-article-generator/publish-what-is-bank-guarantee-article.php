<?php
/**
 * Публикация статьи "Что такое банковская гарантия"
 * Применение критериев матрицы плагина и инструкций пользователя
 */

// Загрузка WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) exit;

echo "=== Публикация статьи 'Что такое банковская гарантия' ===\n\n";

// 1. Загрузка контента статьи
$article_html_path = plugin_dir_path(__FILE__) . 'generated-article-what-is-bank-guarantee.html';
if (!file_exists($article_html_path)) {
    die("Ошибка: Файл статьи не найден по пути: " . $article_html_path . "\n");
}

$article_content = file_get_contents($article_html_path);

if (empty($article_content)) {
    die("Ошибка: Содержимое статьи пустое.\n");
}

echo "✓ Контент статьи загружен\n";

// Получаем данные из матрицы для ключевого слова
$main_plugin = BizFin_SEO_Article_Generator::get_instance();
$seo_matrix = $main_plugin->get_seo_matrix();
$keyword_data = $seo_matrix['keywords']['что такое банковская гарантия'] ?? [];

// Извлекаем HTML контент из файла (убираем DOCTYPE, html, head, body теги)
$dom = new DOMDocument();
@$dom->loadHTML($article_content);

// Извлекаем контент из body
$body = $dom->getElementsByTagName('body')->item(0);
if ($body) {
    $innerHTML = '';
    foreach ($body->childNodes as $child) {
        $innerHTML .= $dom->saveHTML($child);
    }
    $article_content = $innerHTML;
}

// Извлекаем title и meta description из head
$head = $dom->getElementsByTagName('head')->item(0);
$title = '';
$meta_description = '';

if ($head) {
    $title_tags = $head->getElementsByTagName('title');
    if ($title_tags->length > 0) {
        $title = $title_tags->item(0)->textContent;
    }
    
    $meta_tags = $head->getElementsByTagName('meta');
    foreach ($meta_tags as $meta) {
        if ($meta->getAttribute('name') === 'description') {
            $meta_description = $meta->getAttribute('content');
            break;
        }
    }
}

// Если не удалось извлечь из HTML, используем значения по умолчанию
if (empty($title)) {
    $title = 'Что такое банковская гарантия: простыми словами для бизнеса';
}
if (empty($meta_description)) {
    $meta_description = 'Банковская гарантия поможет вашему бизнесу участвовать в тендерах и снижать риски. Разберём, что это такое простыми словами, как работает и когда нужна.';
}

$focus_keyword = 'что такое банковская гарантия';

echo "✓ SEO данные извлечены:\n";
echo "  - Заголовок: " . $title . "\n";
echo "  - Описание: " . $meta_description . "\n";
echo "  - Ключевое слово: " . $focus_keyword . "\n\n";

// 2. Создание или обновление поста
$post_title = $title;
$post_content = $article_content;
$post_status = 'publish';
$post_name = sanitize_title($focus_keyword); // URL slug

$post_data = array(
    'post_title'    => $post_title,
    'post_content'  => $post_content,
    'post_status'   => $post_status,
    'post_type'     => 'post',
    'post_author'   => 1, // ID автора (например, admin)
    'post_name'     => $post_name,
);

// Проверяем, существует ли уже статья с таким заголовком
$existing_post = get_page_by_title($post_title, OBJECT, 'post');

if ($existing_post) {
    $post_data['ID'] = $existing_post->ID;
    $post_id = wp_update_post($post_data);
    echo "✓ Пост обновлен с ID: " . $post_id . "\n";
} else {
    $post_id = wp_insert_post($post_data);
    echo "✓ Пост создан с ID: " . $post_id . "\n";
}

if (is_wp_error($post_id)) {
    die("Ошибка при создании/обновлении поста: " . $post_id->get_error_message() . "\n");
}

// 3. Установка категории
$category_name = 'Банковские гарантии';
$category_id = get_cat_ID($category_name);
if (!$category_id) {
    $category_id = wp_create_category($category_name);
}
if ($category_id && !is_wp_error($category_id)) {
    wp_set_post_categories($post_id, array($category_id), false);
    echo "✓ Категория '{$category_name}' установлена\n";
} else {
    echo "❌ Ошибка при установке категории: " . ($category_id ? $category_id->get_error_message() : 'Категория не найдена/создана') . "\n";
}

// 4. Установка тегов
$tags = ['банковская гарантия', 'что такое банковская гарантия', 'принципал', 'бенефициар', 'гарант', 'тендеры', 'бизнес'];
wp_set_post_tags($post_id, $tags, false);
echo "✓ Теги установлены: " . implode(', ', $tags) . "\n";

// 5. Установка Yoast SEO метаданных
if (class_exists('WPSEO_Options')) {
    update_post_meta($post_id, '_yoast_wpseo_title', $title);
    update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
    update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
    update_post_meta($post_id, '_yoast_wpseo_canonical', get_permalink($post_id));
    echo "✓ Yoast SEO метаданные установлены\n";
} else {
    echo "❌ Yoast SEO не активен или не найден\n";
}

// 6. Установка мета-полей для нашего плагина
update_post_meta($post_id, '_bsag_generated_article', true);
update_post_meta($post_id, '_bsag_article_data', json_encode($keyword_data));
update_post_meta($post_id, '_bsag_keyword', $focus_keyword);
update_post_meta($post_id, '_bsag_intent', 'informational');
update_post_meta($post_id, '_bsag_target_audience', 'beginners');
update_post_meta($post_id, '_bsag_word_count', str_word_count(strip_tags($post_content)));

echo "✓ Мета-поля плагина установлены\n";

// 7. Запуск интеграций через наш плагин
echo "\n=== Запуск интеграций ===\n";

// Запускаем событие генерации статьи
do_action('bsag_article_generated', $post_id, [
    'content' => $post_content,
    'keyword' => $focus_keyword,
    'title' => $title,
    'meta_description' => $meta_description,
    'keyword_data' => $keyword_data
]);

// Обновляем пост для запуска всех хуков
wp_update_post(['ID' => $post_id]);

echo "✓ Интеграции запущены\n";

// 8. Проверка результата
echo "\n=== Проверка результата ===\n";
$post = get_post($post_id);
if ($post) {
    echo "✓ Статья успешно опубликована!\n";
    echo "📊 Статистика:\n";
    echo "- ID статьи: " . $post_id . "\n";
    echo "- URL: " . get_permalink($post_id) . "\n";
    echo "- Статус: " . $post->post_status . "\n";
    echo "- Автор: " . get_the_author_meta('display_name', $post->post_author) . "\n";
    echo "- Дата публикации: " . $post->post_date . "\n";
    echo "- Количество слов: " . str_word_count(strip_tags($post->post_content)) . "\n";
    echo "- SEO заголовок: " . (get_post_meta($post_id, '_yoast_wpseo_title', true) ? '✓ Установлен' : '❌ Не установлен') . "\n";
    echo "- SEO описание: " . (get_post_meta($post_id, '_yoast_wpseo_metadesc', true) ? '✓ Установлено' : '❌ Не установлено') . "\n";
    echo "- Ключевое слово: " . (get_post_meta($post_id, '_yoast_wpseo_focuskw', true) ? '✓ Установлено' : '❌ Не установлено') . "\n";
    
    // Проверяем миниатюру
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        echo "- Миниатюра: ✓ Установлена (ID: {$thumbnail_id})\n";
    } else {
        echo "- Миниатюра: ❌ Не установлена\n";
    }
    
    echo "\n🎉 Статья готова к просмотру!\n";
    echo "🔗 Перейдите по ссылке: " . get_permalink($post_id) . "\n";
    
} else {
    echo "❌ Ошибка: Статья не найдена после публикации\n";
}

echo "\n=== Публикация завершена ===\n";

