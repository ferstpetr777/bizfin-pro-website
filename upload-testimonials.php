<?php
/**
 * Скрипт для загрузки благодарственных писем в медиатеку WordPress
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Массив файлов и их описаний
$testimonials = [
    '1.png' => [
        'alt' => 'Благодарственное письмо от ООО «МПА-М»',
        'description' => 'ООО «МПА-М»

«ООО МПА-М выражает благодарность компании ООО Бизнес Финанс, а также, в частности, Ремизовой Альбине за квалифицированную помощь в организации слаженной работы, а также за оперативное решение поставленных задач.

Надеемся на дальнейшее плодотворное сотрудничество и желаем Вам успешного развития и достижения новых целей»'
    ],
    '2.png' => [
        'alt' => 'Благодарственное письмо от ООО «Кубань-ГазГеоНефтедобыча»',
        'description' => 'ООО «Кубань-ГазГеоНефтедобыча»

[Полный текст благодарственного письма будет добавлен]'
    ],
    '3.png' => [
        'alt' => 'Благодарственное письмо от ООО «ИНТЕКС»',
        'description' => 'ООО «ИНТЕКС»

[Полный текст благодарственного письма будет добавлен]'
    ],
    '4.png' => [
        'alt' => 'Благодарственное письмо от ИП Болалайкин Александр Валериевич',
        'description' => 'ИП Болалайкин Александр Валериевич

[Полный текст благодарственного письма будет добавлен]'
    ],
    '5.png' => [
        'alt' => 'Благодарственное письмо от ИП Михайлов Вячеслав Игоревич',
        'description' => 'ИП Михайлов Вячеслав Игоревич

[Полный текст благодарственного письма будет добавлен]'
    ],
    '6.png' => [
        'alt' => 'Благодарственное письмо от ООО ПрофБетон',
        'description' => 'ООО ПрофБетон

[Полный текст благодарственного письма будет добавлен]'
    ]
];

// Директория с файлами
$source_dir = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/downloads/testimonials/';

echo "Начинаем загрузку благодарственных писем...\n\n";

foreach ($testimonials as $filename => $data) {
    $file_path = $source_dir . $filename;
    
    if (!file_exists($file_path)) {
        echo "Файл {$filename} не найден, пропускаем...\n";
        continue;
    }
    
    echo "Загружаем файл: {$filename}\n";
    
    // Проверяем, не загружен ли уже этот файл
    $existing_attachment = get_posts([
        'post_type' => 'attachment',
        'meta_query' => [
            [
                'key' => '_wp_attached_file',
                'value' => basename($filename),
                'compare' => 'LIKE'
            ]
        ]
    ]);
    
    if (!empty($existing_attachment)) {
        echo "Файл {$filename} уже загружен, пропускаем...\n";
        continue;
    }
    
    // Подготавливаем данные для загрузки
    $upload_dir = wp_upload_dir();
    $file_array = [
        'name' => $filename,
        'tmp_name' => $file_path,
        'type' => 'image/png',
        'error' => 0,
        'size' => filesize($file_path)
    ];
    
    // Загружаем файл
    $attachment_id = media_handle_sideload($file_array, 0);
    
    if (is_wp_error($attachment_id)) {
        echo "Ошибка при загрузке {$filename}: " . $attachment_id->get_error_message() . "\n";
        continue;
    }
    
    // Обновляем метаданные
    wp_update_attachment_metadata($attachment_id, [
        'width' => 0, // WordPress автоматически определит
        'height' => 0,
        'file' => $upload_dir['subdir'] . '/' . $filename
    ]);
    
    // Обновляем alt-текст и описание
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $data['alt']);
    wp_update_post([
        'ID' => $attachment_id,
        'post_content' => $data['description'],
        'post_excerpt' => $data['alt']
    ]);
    
    echo "Файл {$filename} успешно загружен (ID: {$attachment_id})\n";
    echo "Alt-текст: {$data['alt']}\n\n";
}

echo "Загрузка завершена!\n";
?>
