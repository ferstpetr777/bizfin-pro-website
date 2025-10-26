<?php
// Скрипт для принудительного удаления медиафайла из базы данных
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');

// ID медиафайла для удаления
$media_id = 593;

echo "Попытка удаления медиафайла с ID: $media_id\n";

// Подключение к базе данных
$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

// Проверяем, существует ли запись
$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $media_id));

if (!$post) {
    echo "Медиафайл с ID $media_id не найден в базе данных.\n";
    exit;
}

echo "Найден медиафайл: " . $post->post_title . " (тип: " . $post->post_mime_type . ")\n";

// Удаляем связанные метаданные
$meta_deleted = $wpdb->delete($wpdb->postmeta, array('post_id' => $media_id));
echo "Удалено метаданных: $meta_deleted\n";

// Удаляем связи с постами
$relationships_deleted = $wpdb->delete($wpdb->posts, array('post_parent' => $media_id, 'post_type' => 'attachment'));
echo "Удалено связанных записей: $relationships_deleted\n";

// Удаляем основную запись
$post_deleted = $wpdb->delete($wpdb->posts, array('ID' => $media_id));
echo "Удалена основная запись: $post_deleted\n";

if ($post_deleted) {
    echo "Медиафайл успешно удален из базы данных!\n";
} else {
    echo "Ошибка при удалении медиафайла из базы данных.\n";
}

// Проверяем, остались ли еще записи
$remaining = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID = %d", $media_id));
echo "Оставшихся записей с ID $media_id: $remaining\n";
?>
