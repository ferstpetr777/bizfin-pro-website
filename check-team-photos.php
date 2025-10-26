<?php
/**
 * Скрипт для проверки загруженных фотографий команды
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');

// Список имен команды для проверки
$team_names = [
    'Анастасия Борисова',
    'Елена Ремизова', 
    'Татьяна Лопатина',
    'Анастасия Лохова',
    'Анжелика Куликова',
    'Мария Белоногова',
    'Евгения Носова',
    'Александр Волоконцев'
];

echo "Проверка загруженных фотографий команды:\n";
echo "==========================================\n\n";

foreach ($team_names as $name) {
    // Получаем информацию о медиафайле
    $attachment = $wpdb->get_row($wpdb->prepare(
        "SELECT ID, post_title, post_excerpt, post_content, guid FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment'",
        $name
    ));
    
    if ($attachment) {
        // Получаем alt-тег
        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        
        echo "✓ {$name}\n";
        echo "  ID: {$attachment->ID}\n";
        echo "  Должность: {$attachment->post_excerpt}\n";
        echo "  Alt-тег: {$alt_text}\n";
        echo "  URL: {$attachment->guid}\n";
        echo "  Описание: " . substr(strip_tags($attachment->post_content), 0, 100) . "...\n\n";
    } else {
        echo "✗ {$name} - НЕ НАЙДЕНО\n\n";
    }
}

echo "Проверка завершена!\n";
?>
