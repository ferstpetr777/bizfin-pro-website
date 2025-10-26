<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== ПРОВЕРКА РАЗМЕРОВ ПОСЛЕ ДОПОЛНИТЕЛЬНОГО СЖАТИЯ ===\n\n";

// Получаем все WebP изображения
$attachments = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image/webp',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
));

$total_size = 0;
$count = 0;
$over_400kb = 0;
$under_400kb = 0;

echo "ID\tНазвание\t\t\t\t\tРазмер (KB)\tСтатус\n";
echo str_repeat("=", 100) . "\n";

foreach ($attachments as $attachment) {
    $metadata = wp_get_attachment_metadata($attachment->ID);
    $file_size = isset($metadata['filesize']) ? $metadata['filesize'] : 0;
    
    if ($file_size > 0) {
        $size_kb = round($file_size / 1024, 2);
        $status = $size_kb > 400 ? "❌ >400KB" : "✅ <400KB";
        
        if ($size_kb > 400) {
            $over_400kb++;
        } else {
            $under_400kb++;
        }
        
        $title = mb_substr($attachment->post_title, 0, 30);
        if (mb_strlen($attachment->post_title) > 30) {
            $title .= '...';
        }
        
        printf("%-6d %-35s %8.2f KB %s\n", 
               $attachment->ID, 
               $title, 
               $size_kb, 
               $status);
        
        $total_size += $file_size;
        $count++;
    }
}

echo str_repeat("=", 100) . "\n";
echo "ИТОГО:\n";
echo "Количество изображений: " . $count . "\n";
echo "Общий размер: " . round($total_size / (1024 * 1024), 2) . " MB\n";
echo "Средний размер: " . round(($total_size / $count) / 1024, 2) . " KB\n";
echo "Изображений <400KB: " . $under_400kb . " (" . round(($under_400kb / $count) * 100, 1) . "%)\n";
echo "Изображений >400KB: " . $over_400kb . " (" . round(($over_400kb / $count) * 100, 1) . "%)\n";

if ($over_400kb > 0) {
    echo "\n⚠️  ВНИМАНИЕ: " . $over_400kb . " изображений все еще больше 400KB!\n";
} else {
    echo "\n✅ ОТЛИЧНО: Все изображения меньше 400KB!\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>

