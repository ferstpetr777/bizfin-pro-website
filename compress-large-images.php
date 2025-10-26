<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ДОПОЛНИТЕЛЬНОЕ СЖАТИЕ ИЗОБРАЖЕНИЙ БОЛЬШЕ 400 KB ===\n\n";

// Получаем все WebP изображения
$attachments = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image/webp',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
));

$target_size_kb = 400;
$target_size_bytes = $target_size_kb * 1024;
$compressed_count = 0;
$total_saved = 0;

echo "Целевой размер: " . $target_size_kb . " KB\n";
echo "Проверяем " . count($attachments) . " изображений...\n\n";

foreach ($attachments as $attachment) {
    $metadata = wp_get_attachment_metadata($attachment->ID);
    $file_size = isset($metadata['filesize']) ? $metadata['filesize'] : 0;
    
    if ($file_size > $target_size_bytes) {
        $file_path = get_attached_file($attachment->ID);
        
        if (file_exists($file_path)) {
            $original_size = $file_size;
            $original_size_kb = round($original_size / 1024, 2);
            
            echo "Сжимаем: " . $attachment->post_title . " (ID: " . $attachment->ID . ")\n";
            echo "  Исходный размер: " . $original_size_kb . " KB\n";
            
            // Функция сжатия изображения
            $result = compress_image_to_target_size($file_path, $target_size_bytes);
            
            if ($result['success']) {
                $new_size_kb = round($result['new_size'] / 1024, 2);
                $saved_kb = round(($original_size - $result['new_size']) / 1024, 2);
                
                echo "  ✓ Новый размер: " . $new_size_kb . " KB\n";
                echo "  ✓ Сэкономлено: " . $saved_kb . " KB\n";
                
                // Обновляем метаданные
                $metadata['filesize'] = $result['new_size'];
                wp_update_attachment_metadata($attachment->ID, $metadata);
                
                $compressed_count++;
                $total_saved += ($original_size - $result['new_size']);
            } else {
                echo "  ✗ Ошибка сжатия: " . $result['error'] . "\n";
            }
            echo "\n";
        } else {
            echo "✗ Файл не найден: " . $file_path . "\n\n";
        }
    }
}

echo "=== РЕЗУЛЬТАТЫ СЖАТИЯ ===\n";
echo "Сжато изображений: " . $compressed_count . "\n";
echo "Общая экономия: " . round($total_saved / (1024 * 1024), 2) . " MB\n";
echo "Средняя экономия на изображение: " . round(($total_saved / $compressed_count) / 1024, 2) . " KB\n";

function compress_image_to_target_size($file_path, $target_size_bytes) {
    // Получаем информацию об изображении
    $image_info = getimagesize($file_path);
    if (!$image_info) {
        return array('success' => false, 'error' => 'Не удалось получить информацию об изображении');
    }
    
    $width = $image_info[0];
    $height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // Создаем изображение из файла
    switch ($mime_type) {
        case 'image/webp':
            $source = imagecreatefromwebp($file_path);
            break;
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            break;
        default:
            return array('success' => false, 'error' => 'Неподдерживаемый формат изображения');
    }
    
    if (!$source) {
        return array('success' => false, 'error' => 'Не удалось создать изображение из файла');
    }
    
    // Пробуем разные уровни качества
    $qualities = array(85, 80, 75, 70, 65, 60, 55, 50, 45, 40, 35, 30, 25, 20);
    $best_quality = 85;
    $best_size = filesize($file_path);
    
    foreach ($qualities as $quality) {
        // Создаем временный файл для тестирования
        $temp_file = $file_path . '.temp_' . $quality;
        
        // Сохраняем с текущим качеством
        if (imagewebp($source, $temp_file, $quality)) {
            $temp_size = filesize($temp_file);
            
            if ($temp_size <= $target_size_bytes && $temp_size < $best_size) {
                $best_size = $temp_size;
                $best_quality = $quality;
            }
            
            // Удаляем временный файл
            unlink($temp_file);
        }
    }
    
    // Если даже с минимальным качеством размер больше целевого, уменьшаем размер
    if ($best_size > $target_size_bytes) {
        $scale_factor = sqrt($target_size_bytes / $best_size);
        $new_width = intval($width * $scale_factor);
        $new_height = intval($height * $scale_factor);
        
        // Создаем новое изображение с уменьшенными размерами
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // Сохраняем прозрачность для PNG
        if ($mime_type == 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // Сохраняем сжатое изображение
        if (imagewebp($resized, $file_path, 80)) {
            $final_size = filesize($file_path);
            imagedestroy($resized);
            imagedestroy($source);
            
            return array('success' => true, 'new_size' => $final_size);
        } else {
            imagedestroy($resized);
            imagedestroy($source);
            return array('success' => false, 'error' => 'Не удалось сохранить сжатое изображение');
        }
    } else {
        // Сохраняем с лучшим качеством
        if (imagewebp($source, $file_path, $best_quality)) {
            $final_size = filesize($file_path);
            imagedestroy($source);
            return array('success' => true, 'new_size' => $final_size);
        } else {
            imagedestroy($source);
            return array('success' => false, 'error' => 'Не удалось сохранить сжатое изображение');
        }
    }
}

echo "\n=== СЖАТИЕ ЗАВЕРШЕНО ===\n";
?>

