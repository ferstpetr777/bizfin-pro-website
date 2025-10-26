<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

// Получаем все изображения
$images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ИТОГОВЫЙ ОТЧЕТ ПО СОПОСТАВЛЕНИЮ СТАТЕЙ И ИЗОБРАЖЕНИЙ ===\n\n";

$matched = 0;
$unmatched = 0;
$matches = array();
$unmatched_articles = array();

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d H:i:s', $post_id);
    $found_match = false;
    
    foreach ($images as $image_id) {
        $image_title = get_the_title($image_id);
        
        if ($post_title === $image_title) {
            $matches[] = array(
                'post_id' => $post_id,
                'image_id' => $image_id,
                'title' => $post_title,
                'date' => $post_date
            );
            $matched++;
            $found_match = true;
            break;
        }
    }
    
    if (!$found_match) {
        $unmatched_articles[] = array(
            'post_id' => $post_id,
            'title' => $post_title,
            'date' => $post_date
        );
        $unmatched++;
    }
}

echo "=== ОБЩАЯ СТАТИСТИКА ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Всего изображений: " . count($images) . "\n";
echo "Найдено точных совпадений: $matched\n";
echo "Не найдено совпадений: $unmatched\n";
$percentage = round(($matched / count($posts)) * 100, 2);
echo "Процент совпадений: $percentage%\n\n";

echo "=== СТАТИСТИКА ПО ДАТАМ ===\n";
$dates_stats = array();
foreach ($matches as $match) {
    $date = substr($match['date'], 0, 10);
    if (!isset($dates_stats[$date])) {
        $dates_stats[$date] = 0;
    }
    $dates_stats[$date]++;
}

foreach ($dates_stats as $date => $count) {
    echo "$date: $count совпадений\n";
}

echo "\n=== СТАТЬИ БЕЗ ТОЧНЫХ СООТВЕТСТВИЙ ===\n";
foreach ($unmatched_articles as $article) {
    echo "ID {$article['post_id']} ({$article['date']}): '{$article['title']}'\n";
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Для SEO оптимизации необходимо найти изображения с точными названиями для $unmatched статей\n";
echo "2. Особое внимание уделить статьям, созданным до 23 октября 2025 года\n";
echo "3. Проверить наличие изображений в медиатеке с похожими названиями\n";
echo "4. При необходимости создать новые изображения с точными названиями статей\n";
