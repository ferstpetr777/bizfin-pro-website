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

echo "=== ОБНОВЛЕНИЕ АЛФАВИТНЫХ БЛОКОВ НА ПОЛНУЮ ШИРИНУ ===\n\n";

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
    
    // Проверяем, уже ли есть alignfull
    if (strpos($content, 'alignfull') !== false) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - уже имеет alignfull.\n";
        $skipped_count++;
        continue;
    }
    
    // Обновляем блок на полную ширину
    $new_content = preg_replace(
        '/<!-- wp:abp-v2\/alphabet-menu \{"showSearch":true,"showTitle":true\} -->/',
        '<!-- wp:abp-v2/alphabet-menu {"showSearch":true,"showTitle":true,"align":"full"} -->',
        $content
    );
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "✓ Обновлен алфавитный блок на полную ширину в статье ID $post_id ($post_date): '$post_title'\n";
    $updated_count++;
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Обновлено статей: $updated_count\n";
echo "Пропущено статей: $skipped_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
