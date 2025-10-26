<?php
/**
 * Скрипт для настройки и запуска сжатия изображений
 * Smart Image Compressor Plugin Setup
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== НАСТРОЙКА SMART IMAGE COMPRESSOR ===\n\n";

// 1. Устанавливаем настройки плагина
$default_options = array(
    'max_file_size' => 400, // KB - максимальный размер файла
    'quality' => 85,        // Качество сжатия (1-100)
    'format' => 'webp',     // Формат выходного файла
    'auto_compress' => true, // Автоматическое сжатие при загрузке
    'preserve_metadata' => true, // Сохранение метаданных
    'backup_originals' => false // Резервные копии оригиналов
);

// Сохраняем настройки
update_option('sic_options', $default_options);
echo "✓ Настройки плагина установлены:\n";
echo "  - Максимальный размер файла: {$default_options['max_file_size']} KB\n";
echo "  - Качество сжатия: {$default_options['quality']}%\n";
echo "  - Формат: {$default_options['format']}\n";
echo "  - Автоматическое сжатие: " . ($default_options['auto_compress'] ? 'Включено' : 'Отключено') . "\n";
echo "  - Сохранение метаданных: " . ($default_options['preserve_metadata'] ? 'Включено' : 'Отключено') . "\n\n";

// 2. Получаем статистику изображений
$image_stats = $wpdb->get_results("
    SELECT 
        COUNT(*) as total_images,
        SUM(CASE WHEN post_mime_type LIKE 'image/%' THEN 1 ELSE 0 END) as image_count,
        AVG(CASE WHEN post_mime_type LIKE 'image/%' THEN LENGTH(post_content) ELSE 0 END) as avg_size
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment' 
    AND post_mime_type LIKE 'image/%'
");

$total_images = $image_stats[0]->image_count;
echo "=== СТАТИСТИКА ИЗОБРАЖЕНИЙ ===\n";
echo "Всего изображений в медиабиблиотеке: {$total_images}\n\n";

// 3. Получаем список изображений для сжатия
$images_to_compress = $wpdb->get_results("
    SELECT ID, post_title, post_mime_type, post_content
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment' 
    AND post_mime_type LIKE 'image/%'
    AND post_mime_type NOT LIKE 'image/webp'
    ORDER BY ID DESC
    LIMIT 50
");

echo "=== ИЗОБРАЖЕНИЯ ДЛЯ СЖАТИЯ ===\n";
echo "Найдено изображений для сжатия: " . count($images_to_compress) . "\n\n";

if (count($images_to_compress) > 0) {
    echo "Начинаем сжатие изображений...\n\n";
    
    $compressed_count = 0;
    $total_savings = 0;
    $errors = 0;
    
    foreach ($images_to_compress as $image) {
        $attachment_id = $image->ID;
        $file_path = get_attached_file($attachment_id);
        
        if (!$file_path || !file_exists($file_path)) {
            echo "⚠️  Пропущено: {$image->post_title} (файл не найден)\n";
            continue;
        }
        
        $original_size = filesize($file_path);
        $original_size_kb = round($original_size / 1024);
        
        // Проверяем, нужно ли сжимать
        if ($original_size_kb <= $default_options['max_file_size']) {
            echo "⏭️  Пропущено: {$image->post_title} (уже оптимальный размер: {$original_size_kb} KB)\n";
            continue;
        }
        
        echo "🔄 Сжимаем: {$image->post_title} (размер: {$original_size_kb} KB)... ";
        
        // Создаем экземпляр плагина для сжатия
        if (class_exists('SmartImageCompressor')) {
            $compressor = new SmartImageCompressor();
            $result = $compressor->compress_image($attachment_id);
            
            if ($result && $result['status'] == 'success') {
                $savings = $result['savings'];
                $total_savings += $savings;
                $compressed_count++;
                echo "✓ Сжато! Экономия: {$savings} KB\n";
            } else {
                $errors++;
                echo "✗ Ошибка: " . ($result['message'] ?? 'Неизвестная ошибка') . "\n";
            }
        } else {
            echo "✗ Ошибка: Класс SmartImageCompressor не найден\n";
            $errors++;
        }
    }
    
    echo "\n=== РЕЗУЛЬТАТЫ СЖАТИЯ ===\n";
    echo "Обработано изображений: {$compressed_count}\n";
    echo "Общая экономия места: {$total_savings} KB (" . round($total_savings / 1024, 2) . " MB)\n";
    echo "Ошибок: {$errors}\n\n";
}

// 4. Проверяем поддержку WebP
echo "=== ПРОВЕРКА ПОДДЕРЖКИ WEBP ===\n";
if (function_exists('imagewebp')) {
    echo "✓ WebP поддерживается сервером\n";
} else {
    echo "⚠️  WebP не поддерживается, будет использоваться JPEG\n";
}

// 5. Проверяем права на запись
$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['basedir'];

if (is_writable($upload_path)) {
    echo "✓ Права на запись в папку загрузок: OK\n";
} else {
    echo "⚠️  Нет прав на запись в папку загрузок\n";
}

echo "\n=== НАСТРОЙКА ЗАВЕРШЕНА ===\n";
echo "Плагин Smart Image Compressor настроен и готов к работе!\n";
echo "Доступ к настройкам: /wp-admin/admin.php?page=smart-image-compressor\n";
echo "Статистика: /wp-admin/admin.php?page=sic-statistics\n";
?>

