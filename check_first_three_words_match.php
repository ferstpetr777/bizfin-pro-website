<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== ПРОВЕРКА СООТВЕТСТВИЯ ПЕРВЫХ ТРЁХ СЛОВ В ЗАГОЛОВКАХ СТАТЕЙ И ИЗОБРАЖЕНИЙ ===\n\n";

function getFirstThreeWords($title) {
    // Убираем лишние символы и разбиваем на слова
    $words = preg_split('/[\s,\.\-:;]+/', trim($title));
    $words = array_filter($words, function($word) {
        return !empty($word) && strlen($word) > 1;
    });
    return array_slice($words, 0, 3);
}

$exact_matches = 0;
$partial_matches = 0;
$no_thumbnail = 0;
$title_mismatches = 0;
$broken_images = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        $no_thumbnail++;
        echo "✗ Статья ID $post_id: '$post_title' - НЕТ главного изображения\n";
        continue;
    }
    
    // Проверяем, существует ли файл изображения
    $image_path = get_attached_file($thumbnail_id);
    if (!$image_path || !file_exists($image_path)) {
        $broken_images++;
        echo "✗ Статья ID $post_id: '$post_title' - файл изображения отсутствует (ID: $thumbnail_id)\n";
        continue;
    }
    
    // Получаем название изображения
    $image_title = get_the_title($thumbnail_id);
    
    // Получаем первые три слова
    $post_words = getFirstThreeWords($post_title);
    $image_words = getFirstThreeWords($image_title);
    
    // Сравниваем первые три слова
    $match_count = 0;
    $min_length = min(count($post_words), count($image_words));
    
    for ($i = 0; $i < $min_length; $i++) {
        if (strtolower($post_words[$i]) === strtolower($image_words[$i])) {
            $match_count++;
        }
    }
    
    if ($match_count >= 3) {
        $exact_matches++;
        echo "✓ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - ПОЛНОЕ СООТВЕТСТВИЕ (3/3)\n";
    } elseif ($match_count >= 2) {
        $partial_matches++;
        echo "~ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - ЧАСТИЧНОЕ СООТВЕТСТВИЕ ($match_count/3)\n";
    } else {
        $title_mismatches++;
        echo "✗ Статья ID $post_id: '$post_title' -> Изображение ID $thumbnail_id: '$image_title' - НЕ СООТВЕТСТВУЕТ ($match_count/3)\n";
    }
}

echo "\n=== ИТОГОВЫЕ РЕЗУЛЬТАТЫ ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Полных соответствий (3/3 слов): $exact_matches\n";
echo "Частичных соответствий (2/3 слов): $partial_matches\n";
echo "Несоответствий (0-1/3 слов): $title_mismatches\n";
echo "Статей без главного изображения: $no_thumbnail\n";
echo "Статей с отсутствующими файлами изображений: $broken_images\n";
echo "Процент полных соответствий: " . round(($exact_matches / count($posts)) * 100, 2) . "%\n";
echo "Процент частичных соответствий: " . round((($exact_matches + $partial_matches) / count($posts)) * 100, 2) . "%\n";
