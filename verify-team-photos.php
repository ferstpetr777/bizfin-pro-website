<?php
/**
 * Скрипт для проверки всех загруженных фотографий команды
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

echo "Проверка всех 8 фотографий команды в медиатеке:\n";
echo "===============================================\n\n";

$found_count = 0;

foreach ($team_names as $name) {
    // Получаем информацию о медиафайле
    $attachment = $wpdb->get_row($wpdb->prepare(
        "SELECT ID, post_title, post_excerpt, post_content, guid, post_date FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment' ORDER BY post_date DESC LIMIT 1",
        $name
    ));
    
    if ($attachment) {
        // Получаем alt-тег
        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        
        echo "✅ {$name}\n";
        echo "   ID: {$attachment->ID}\n";
        echo "   Дата: {$attachment->post_date}\n";
        echo "   Должность: {$attachment->post_excerpt}\n";
        echo "   Alt-тег: {$alt_text}\n";
        echo "   URL: {$attachment->guid}\n";
        echo "   Описание: " . substr(strip_tags($attachment->post_content), 0, 80) . "...\n\n";
        
        $found_count++;
    } else {
        echo "❌ {$name} - НЕ НАЙДЕНО\n\n";
    }
}

echo "===============================================\n";
echo "Найдено фотографий: $found_count из 8\n";

if ($found_count == 8) {
    echo "✅ ВСЕ ФОТОГРАФИИ КОМАНДЫ ЗАГРУЖЕНЫ!\n";
} else {
    echo "⚠️  НЕ ВСЕ ФОТОГРАФИИ НАЙДЕНЫ!\n";
}
?>
