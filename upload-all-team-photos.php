<?php
/**
 * Скрипт для загрузки всех 8 фотографий команды в медиатеку WordPress
 * с правильными alt-тегами и описаниями
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');
require_once('wp-includes/functions.php');
require_once('wp-includes/formatting.php');
require_once('wp-admin/includes/file.php');
require_once('wp-admin/includes/media.php');
require_once('wp-admin/includes/image.php');

// Определяем данные команды
$team_members = [
    '01-anastasia-borisova.jpg' => [
        'name' => 'Анастасия Борисова',
        'position' => 'Генеральный директор',
        'description' => 'Генеральный директор ООО Бизнес-Финанс. Руководит компанией и обеспечивает стратегическое развитие бизнеса в области банковских гарантий и финансовых услуг.',
        'alt' => 'Анастасия Борисова - генеральный директор ООО Бизнес-Финанс'
    ],
    '02-elena-remizova.jpg' => [
        'name' => 'Елена Ремизова', 
        'position' => 'Руководитель отдела экспертизы и документарного сопровождения',
        'description' => 'Руководитель отдела экспертизы и документарного сопровождения. Отвечает за качество документооборота и экспертизу финансовых документов.',
        'alt' => 'Елена Ремизова - руководитель отдела экспертизы и документарного сопровождения'
    ],
    '03-tatyana-lopatina.jpg' => [
        'name' => 'Татьяна Лопатина',
        'position' => 'Финансовый консультант в области госзаказа, специалист по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа. Специализируется на работе с корпоративными клиентами и сопровождении государственных закупок.',
        'alt' => 'Татьяна Лопатина - финансовый консультант в области госзаказа'
    ],
    '04-anastasia-lokhova.jpg' => [
        'name' => 'Анастасия Лохова',
        'position' => 'Финансовый консультант в области госзаказа, специалист по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа. Эксперт по работе с корпоративными клиентами и сопровождению тендерных процедур.',
        'alt' => 'Анастасия Лохова - финансовый консультант в области госзаказа'
    ],
    '05-anzhelika-kulikova.jpg' => [
        'name' => 'Анжелика Куликова',
        'position' => 'Финансовый консультант в области госзаказа, специалист по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа. Специализируется на сопровождении корпоративных клиентов в государственных закупках.',
        'alt' => 'Анжелика Куликова - финансовый консультант в области госзаказа'
    ],
    '06-maria-belonogova.jpg' => [
        'name' => 'Мария Белоногова',
        'position' => 'Специалист отдела экспертизы и документарного сопровождения',
        'description' => 'Специалист отдела экспертизы и документарного сопровождения. Занимается подготовкой и проверкой документов для банковских гарантий.',
        'alt' => 'Мария Белоногова - специалист отдела экспертизы и документарного сопровождения'
    ],
    '07-evgeniya-nosova.jpg' => [
        'name' => 'Евгения Носова',
        'position' => 'Заместитель директора',
        'description' => 'Заместитель директора ООО Бизнес-Финанс. Отвечает за операционное управление и координацию работы отделов компании.',
        'alt' => 'Евгения Носова - заместитель директора ООО Бизнес-Финанс'
    ],
    '08-aleksandr-volokontsev.jpg' => [
        'name' => 'Александр Волоконцев',
        'position' => 'Финансовый консультант в области госзаказа, руководитель отдела по работе с корпоративными клиентами',
        'description' => 'Финансовый консультант в области госзаказа, руководитель отдела по работе с корпоративными клиентами. Эксперт по банковским гарантиям и государственным закупкам.',
        'alt' => 'Александр Волоконцев - финансовый консультант, руководитель отдела по работе с корпоративными клиентами'
    ]
];

// Путь к папке с фотографиями
$photos_dir = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/team-photos/';

echo "Начинаем загрузку всех 8 фотографий команды в медиатеку WordPress...\n\n";

$uploaded_count = 0;

foreach ($team_members as $filename => $member_data) {
    $file_path = $photos_dir . $filename;
    
    if (!file_exists($file_path)) {
        echo "✗ Файл не найден: $filename\n";
        continue;
    }
    
    // Проверяем, не загружено ли уже это изображение
    $existing_attachment = $wpdb->get_row($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment'",
        $member_data['name']
    ));
    
    if ($existing_attachment) {
        echo "⚠ Изображение для {$member_data['name']} уже существует (ID: {$existing_attachment->ID})\n";
        continue;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_array = [
        'name' => $filename,
        'tmp_name' => $file_path,
        'type' => 'image/jpeg',
        'error' => 0,
        'size' => filesize($file_path)
    ];
    
    // Загружаем файл в медиатеку
    $attachment_id = media_handle_sideload($file_array, 0, $member_data['name']);
    
    if (is_wp_error($attachment_id)) {
        echo "✗ Ошибка при загрузке {$filename}: " . $attachment_id->get_error_message() . "\n";
        continue;
    }
    
    // Обновляем метаданные
    $attachment_data = [
        'ID' => $attachment_id,
        'post_title' => $member_data['name'],
        'post_content' => $member_data['description'],
        'post_excerpt' => $member_data['position']
    ];
    
    wp_update_post($attachment_data);
    
    // Добавляем alt-тег
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $member_data['alt']);
    
    // Добавляем дополнительные метаданные
    update_post_meta($attachment_id, 'team_member_name', $member_data['name']);
    update_post_meta($attachment_id, 'team_member_position', $member_data['position']);
    
    echo "✓ Успешно загружено: {$member_data['name']} (ID: $attachment_id)\n";
    $uploaded_count++;
}

echo "\nЗагрузка завершена! Загружено новых фотографий: $uploaded_count из 8\n";
?>
