<?php
/**
 * Скрипт для обработки существующих постов и добавления мета-данных первой буквы
 */

require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

function mb_first_letter(string $str): ?string {
    $str = trim($str);
    if ($str === '') return null;
    
    // Уберем кавычки/скобки/пробелы в начале
    $str = preg_replace('/^[\s"\':«»\(\)\[\]\-]+/u', '', $str);
    if ($str === '') return null;
    
    $first = mb_substr($str, 0, 1, 'UTF-8');
    $first = mb_strtoupper($first, 'UTF-8');
    
    // Нормализуем латиницу к A–Z; кириллица остаётся, Ё — отдельно
    return $first;
}

echo "Обработка существующих постов...\n";

$posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids'
]);

$processed = 0;
$errors = 0;

foreach ($posts as $post_id) {
    $title = get_the_title($post_id);
    if (!$title) {
        echo "Пост {$post_id}: нет заголовка\n";
        $errors++;
        continue;
    }
    
    $first_letter = mb_first_letter($title);
    if (!$first_letter) {
        echo "Пост {$post_id}: не удалось определить первую букву для '{$title}'\n";
        $errors++;
        continue;
    }
    
    update_post_meta($post_id, 'abp_first_letter', $first_letter);
    echo "Пост {$post_id}: '{$title}' -> первая буква '{$first_letter}'\n";
    $processed++;
}

echo "\nОбработка завершена:\n";
echo "Обработано постов: {$processed}\n";
echo "Ошибок: {$errors}\n";

// Проверяем результаты
echo "\nПроверка результатов:\n";
global $wpdb;
$results = $wpdb->get_results("
    SELECT pm.meta_value AS letter, COUNT(*) AS count
    FROM {$wpdb->postmeta} pm
    INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE pm.meta_key = 'abp_first_letter' AND p.post_status = 'publish' AND p.post_type = 'post'
    GROUP BY pm.meta_value
    ORDER BY pm.meta_value
", ARRAY_A);

foreach ($results as $row) {
    echo "Буква '{$row['letter']}': {$row['count']} статей\n";
}
?>



