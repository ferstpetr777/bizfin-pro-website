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

echo "=== ДОБАВЛЕНИЕ АЛФАВИТНОГО БЛОКА В СТАТЬИ ===\n\n";

foreach ($posts as $post_id) {
    $total_processed++;
    $post_title = get_the_title($post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    $content = get_post_field('post_content', $post_id);
    
    // Проверяем, есть ли уже алфавитный блок
    if (strpos($content, '<!-- wp:abp-v2/alphabet-menu') !== false) {
        echo "Пропущена статья ID $post_id ($post_date): '$post_title' - уже имеет алфавитный блок.\n";
        $skipped_count++;
        continue;
    }
    
    // Создаем алфавитный блок
    $alphabet_block = '<!-- wp:abp-v2/alphabet-menu {"showSearch":true,"showTitle":true} -->
<div class="wp-block-abp-v2-alphabet-menu">[abp_v2_alphabet_menu showSearch="1" showTitle="1"]</div>
<!-- /wp:abp-v2/alphabet-menu -->';
    
    // Добавляем блок в начало контента
    $new_content = $alphabet_block . "\n\n" . $content;
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "✓ Добавлен алфавитный блок в статью ID $post_id ($post_date): '$post_title'\n";
    $updated_count++;
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Обновлено статей: $updated_count\n";
echo "Пропущено статей (уже имеют блок): $skipped_count\n";
echo "Всего обработано статей: $total_processed\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен.\n";
