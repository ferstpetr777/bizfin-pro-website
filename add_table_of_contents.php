<?php
require_once('wp-config.php');

// Получаем все статьи, созданные 19 и 7 октября 2025 года
$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'relation' => 'OR',
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 19,
        ),
        array(
            'year'  => 2025,
            'month' => 10,
            'day'   => 7,
        ),
    ),
    'fields' => 'ids'
));

$updated_count = 0;
$skipped_count = 0;

echo "=== ДОБАВЛЕНИЕ БЛОКА СОДЕРЖАНИЯ В СТАТЬИ ОТ 19 И 7 ОКТЯБРЯ ===\n\n";

foreach ($posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post_date = get_the_date('Y-m-d', $post_id);
    
    // Проверяем, есть ли уже блок содержания
    if (stripos($post_content, 'Содержание:') !== false || 
        stripos($post_content, 'Оглавление:') !== false || 
        stripos($post_content, 'Table of Contents') !== false) {
        echo "✓ Статья ID $post_id ($post_date): '$post_title' уже имеет блок содержания\n";
        $skipped_count++;
        continue;
    }
    
    // Создаем блок содержания
    $table_of_contents = '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007cba;">
        <h3 style="margin: 0 0 15px 0; color: #007cba; font-size: 18px;"><strong>Содержание:</strong></h3>
        <ul style="margin: 0; padding-left: 20px;">
            <li><a href="#what-is" style="color: #007cba; text-decoration: none;">Что это такое?</a></li>
            <li><a href="#how-it-works" style="color: #007cba; text-decoration: none;">Как это работает?</a></li>
            <li><a href="#advantages" style="color: #007cba; text-decoration: none;">Преимущества</a></li>
            <li><a href="#disadvantages" style="color: #007cba; text-decoration: none;">Недостатки</a></li>
            <li><a href="#examples" style="color: #007cba; text-decoration: none;">Примеры</a></li>
            <li><a href="#faq" style="color: #007cba; text-decoration: none;">Часто задаваемые вопросы</a></li>
            <li><a href="#conclusion" style="color: #007cba; text-decoration: none;">Заключение</a></li>
        </ul>
    </div>';
    
    // Добавляем блок содержания в начало контента
    $new_content = $table_of_contents . "\n\n" . $post_content;
    
    // Обновляем статью
    $result = wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content
    ));
    
    if (is_wp_error($result)) {
        echo "✗ Ошибка при обновлении статьи ID $post_id: " . $result->get_error_message() . "\n";
    } else {
        echo "✔ Добавлен блок содержания в статью ID $post_id ($post_date): '$post_title'\n";
        $updated_count++;
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "Обновлено статей: $updated_count\n";
echo "Пропущено статей (уже имеют блок содержания): $skipped_count\n";
echo "Всего обработано статей: " . count($posts) . "\n";
?>
