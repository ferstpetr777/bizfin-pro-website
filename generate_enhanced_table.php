<?php
require_once('wp-config.php');

// Получаем все статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

echo "=== РАСШИРЕННАЯ ТАБЛИЦА СООТВЕТСТВИЙ СТАТЕЙ И ИЗОБРАЖЕНИЙ ===\n\n";

// Заголовок таблицы
printf("%-5s | %-60s | %-5s | %-60s | %-10s | %-8s | %-12s\n", "ID", "НАЗВАНИЕ СТАТЬИ", "IMG", "НАЗВАНИЕ ИЗОБРАЖЕНИЯ", "СООТВЕТСТВИЕ", "ФОРМАТ", "РАЗМЕР");
echo str_repeat("-", 180) . "\n";

$good_matches = 0;
$partial_matches = 0;
$no_matches = 0;
$no_images = 0;

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
    
    if (!$thumbnail_id) {
        printf("%-5d | %-60s | %-5s | %-60s | %-10s | %-8s | %-12s\n", 
            $post_id, 
            substr($post_title, 0, 60), 
            "НЕТ", 
            "НЕТ ИЗОБРАЖЕНИЯ", 
            "НЕТ",
            "НЕТ",
            "НЕТ"
        );
        $no_images++;
        continue;
    }
    
    $image_title = get_the_title($thumbnail_id);
    
    // Получаем информацию о файле изображения
    $image_path = get_attached_file($thumbnail_id);
    $image_format = "НЕТ";
    $image_size = "НЕТ";
    
    if ($image_path && file_exists($image_path)) {
        $image_format = strtoupper(pathinfo($image_path, PATHINFO_EXTENSION));
        $file_size = filesize($image_path);
        
        if ($file_size < 1024) {
            $image_size = $file_size . " B";
        } elseif ($file_size < 1024 * 1024) {
            $image_size = round($file_size / 1024, 1) . " KB";
        } else {
            $image_size = round($file_size / (1024 * 1024), 1) . " MB";
        }
    }
    
    // Проверяем соответствие первых 3 слов
    $post_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($post_title)), 0, 3);
    $image_words = array_slice(preg_split('/[\s,\.\-:;]+/', strtolower($image_title)), 0, 3);
    
    $match_score = 0;
    $min_length = min(count($post_words), count($image_words));
    
    for ($i = 0; $i < $min_length; $i++) {
        if ($post_words[$i] === $image_words[$i]) {
            $match_score++;
        }
    }
    
    $match_status = "";
    if ($match_score >= 3) {
        $match_status = "ОТЛИЧНО";
        $good_matches++;
    } elseif ($match_score >= 2) {
        $match_status = "ХОРОШО";
        $partial_matches++;
    } else {
        $match_status = "ПЛОХО";
        $no_matches++;
    }
    
    printf("%-5d | %-60s | %-5d | %-60s | %-10s | %-8s | %-12s\n", 
        $post_id, 
        substr($post_title, 0, 60), 
        $thumbnail_id, 
        substr($image_title, 0, 60), 
        $match_status,
        $image_format,
        $image_size
    );
}

echo "\n" . str_repeat("=", 180) . "\n";
echo "=== ИТОГОВАЯ СТАТИСТИКА ===\n";
echo "Всего статей: " . count($posts) . "\n";
echo "Отличных соответствий (3/3): $good_matches\n";
echo "Хороших соответствий (2/3): $partial_matches\n";
echo "Плохих соответствий (0-1/3): $no_matches\n";
echo "Без изображений: $no_images\n";
echo "Процент отличных соответствий: " . round(($good_matches / count($posts)) * 100, 2) . "%\n";
echo "Процент хороших и отличных: " . round((($good_matches + $partial_matches) / count($posts)) * 100, 2) . "%\n";
