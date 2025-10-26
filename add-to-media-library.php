<?php
// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');
require_once('wp-includes/pluggable.php');

// Настройки базы данных
$db_name = 'bizfin_pro_r';
$db_user = 'bizfin_pro_r';
$db_password = ',=b$k0e0i9M#vbwH';
$db_host = 'localhost';

// Подключение к базе данных
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_error) {
    die('Ошибка подключения: ' . $mysqli->connect_error);
}

// Настройки файла
$filename = 'Гарантия на 20 млн ₽ за 36 часов.jpg';
$file_path = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/uploads/2025/10/' . $filename;
$file_url = 'https://bizfin-pro.ru/wp-content/uploads/2025/10/' . urlencode($filename);

// Получаем информацию о файле
$file_size = filesize($file_path);
$file_type = 'image/jpeg';
$upload_date = date('Y-m-d H:i:s');

// Получаем размеры изображения
$image_info = getimagesize($file_path);
$width = $image_info[0];
$height = $image_info[1];

// Генерируем уникальный ID для attachment
$attachment_id = time() + rand(1000, 9999);

// Добавляем запись в wp_posts
$post_title = 'Гарантия на 20 млн ₽ за 36 часов';
$post_name = sanitize_title($post_title);

$insert_post = "INSERT INTO wp_posts (
    post_author, 
    post_date, 
    post_date_gmt, 
    post_content, 
    post_title, 
    post_excerpt, 
    post_status, 
    comment_status, 
    ping_status, 
    post_password, 
    post_name, 
    to_ping, 
    pinged, 
    post_modified, 
    post_modified_gmt, 
    post_content_filtered, 
    post_parent, 
    guid, 
    menu_order, 
    post_type, 
    post_mime_type, 
    comment_count
) VALUES (
    1, 
    '$upload_date', 
    '$upload_date', 
    '', 
    '$post_title', 
    '', 
    'inherit', 
    'open', 
    'closed', 
    '', 
    '$post_name', 
    '', 
    '', 
    '$upload_date', 
    '$upload_date', 
    '', 
    0, 
    '$file_url', 
    0, 
    'attachment', 
    '$file_type', 
    0
)";

if ($mysqli->query($insert_post)) {
    $post_id = $mysqli->insert_id;
    echo "Запись добавлена в wp_posts с ID: $post_id\n";
    
    // Добавляем метаданные
    $meta_queries = [
        "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ($post_id, '_wp_attached_file', '2025/10/$filename')",
        "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ($post_id, '_wp_attachment_metadata', 'a:5:{s:5:\"width\";i:$width;s:6:\"height\";i:$height;s:4:\"file\";s:25:\"2025/10/$filename\";s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:12:{s:8:\"aperture\";i:0;s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";i:0;s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";i:0;s:3:\"iso\";i:0;s:13:\"shutter_speed\";i:0;s:5:\"title\";s:0:\"\";s:11:\"orientation\";i:0;s:8:\"keywords\";a:0:{}}}')",
        "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ($post_id, '_wp_attachment_image_alt', 'Гарантия на 20 млн ₽ за 36 часов')"
    ];
    
    foreach ($meta_queries as $query) {
        if ($mysqli->query($query)) {
            echo "Метаданные добавлены\n";
        } else {
            echo "Ошибка добавления метаданных: " . $mysqli->error . "\n";
        }
    }
    
    echo "Файл успешно добавлен в медиатеку WordPress!\n";
    echo "URL файла: $file_url\n";
    echo "ID в медиатеке: $post_id\n";
    
} else {
    echo "Ошибка добавления записи: " . $mysqli->error . "\n";
}

$mysqli->close();
?>

