<?php
require_once('wp-config.php');

// Получаем все опубликованные статьи
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields' => 'ids'
));

$updated_count = 0;
$skipped_count = 0;
$total_processed = 0;

echo "=== УДАЛЕНИЕ АЛФАВИТНЫХ БЛОКОВ ИЗ КОНТЕНТА СТАТЕЙ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    // Проверяем, есть ли алфавитный блок
    if (strpos($content, '<!-- wp:abp-v2/alphabet-menu') === false) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - нет алфавитного блока.\n";
        $skipped_count++;
        continue;
    }
    
    // Удаляем алфавитный блок из контента
    $new_content = preg_replace('/<!-- wp:abp-v2\/alphabet-menu.*?<!-- \/wp:abp-v2\/alphabet-menu -->/s', '', $content);
    
    // Убираем лишние переносы строк в начале
    $new_content = preg_replace('/^\s*\n\s*\n/', '', $new_content);
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "✓ Удален алфавитный блок из контента статьи ID $post_id ($post_date): '$post_title'\n";
    $updated_count++;
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Обновлено статей: $updated_count\n";
echo "Пропущено статей: $skipped_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
