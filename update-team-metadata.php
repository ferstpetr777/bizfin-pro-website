<?php
/**
 * Скрипт для обновления метаданных существующих фотографий команды
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');

// Определяем данные команды для обновления
$team_members = [
    'Татьяна Лопатина' => [
        'position' => 'Финансовый консультант в области госзаказа, специалист по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа. Специализируется на работе с корпоративными клиентами и сопровождении государственных закупок.',
        'alt' => 'Татьяна Лопатина - финансовый консультант в области госзаказа'
    ],
    'Евгения Носова' => [
        'position' => 'Заместитель директора',
        'description' => 'Заместитель директора ООО Бизнес-Финанс. Отвечает за операционное управление и координацию работы отделов компании.',
        'alt' => 'Евгения Носова - заместитель директора ООО Бизнес-Финанс'
    ],
    'Александр Волоконцев' => [
        'position' => 'Финансовый консультант в области госзаказа, руководитель отдела по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа, руководитель отдела по работе с корпоративными клиентами. Эксперт по банковским гарантиям и государственным закупкам.',
        'alt' => 'Александр Волоконцев - финансовый консультант, руководитель отдела по работе с корпоративными клиентами'
    ]
];

echo "Обновление метаданных существующих фотографий команды...\n";

foreach ($team_members as $name => $member_data) {
    // Находим существующую запись
    $attachment = $wpdb->get_row($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment'",
        $name
    ));
    
    if ($attachment) {
        // Обновляем метаданные
        $attachment_data = [
            'ID' => $attachment->ID,
            'post_excerpt' => $member_data['position'],
            'post_content' => $member_data['description']
        ];
        
        wp_update_post($attachment_data);
        
        // Обновляем alt-тег
        update_post_meta($attachment->ID, '_wp_attachment_image_alt', $member_data['alt']);
        
        // Добавляем дополнительные метаданные
        update_post_meta($attachment->ID, 'team_member_name', $name);
        update_post_meta($attachment->ID, 'team_member_position', $member_data['position']);
        
        echo "✓ Обновлено: {$name} (ID: {$attachment->ID})\n";
    } else {
        echo "✗ Не найдено: {$name}\n";
    }
}

echo "Обновление завершено!\n";
?>
