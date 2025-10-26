<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== РАЗМЕРЫ ИЗОБРАЖЕНИЙ ВОССТАНОВЛЕННЫХ СТАТЕЙ ===\n\n";

// Получаем все WebP изображения с размерами
$attachments = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image/webp',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
));

$total_size = 0;
$count = 0;

echo "ID\tНазвание\t\t\t\t\tРазмер (KB)\tРазмер (MB)\n";
echo str_repeat("=", 120) . "\n";

foreach ($attachments as $attachment) {
    $metadata = wp_get_attachment_metadata($attachment->ID);
    $file_size = isset($metadata['filesize']) ? $metadata['filesize'] : 0;
    
    if ($file_size > 0) {
        $size_kb = round($file_size / 1024, 2);
        $size_mb = round($file_size / (1024 * 1024), 2);
        
        $title = mb_substr($attachment->post_title, 0, 40);
        if (mb_strlen($attachment->post_title) > 40) {
            $title .= '...';
        }
        
        printf("%-6d %-45s %8.2f KB %8.2f MB\n", 
               $attachment->ID, 
               $title, 
               $size_kb, 
               $size_mb);
        
        $total_size += $file_size;
        $count++;
    }
}

echo str_repeat("=", 120) . "\n";
echo "ИТОГО:\n";
echo "Количество изображений: " . $count . "\n";
echo "Общий размер: " . round($total_size / (1024 * 1024), 2) . " MB\n";
echo "Средний размер: " . round(($total_size / $count) / (1024 * 1024), 2) . " MB\n";

// Дополнительная статистика по размерам
$size_ranges = array(
    '0-100 KB' => 0,
    '100-500 KB' => 0,
    '500 KB - 1 MB' => 0,
    '1-2 MB' => 0,
    '2+ MB' => 0
);

foreach ($attachments as $attachment) {
    $metadata = wp_get_attachment_metadata($attachment->ID);
    $file_size = isset($metadata['filesize']) ? $metadata['filesize'] : 0;
    
    if ($file_size > 0) {
        $size_kb = $file_size / 1024;
        
        if ($size_kb <= 100) {
            $size_ranges['0-100 KB']++;
        } elseif ($size_kb <= 500) {
            $size_ranges['100-500 KB']++;
        } elseif ($size_kb <= 1024) {
            $size_ranges['500 KB - 1 MB']++;
        } elseif ($size_kb <= 2048) {
            $size_ranges['1-2 MB']++;
        } else {
            $size_ranges['2+ MB']++;
        }
    }
}

echo "\n=== РАСПРЕДЕЛЕНИЕ ПО РАЗМЕРАМ ===\n";
foreach ($size_ranges as $range => $count) {
    echo $range . ": " . $count . " изображений\n";
}

echo "\n=== АНАЛИЗ ЗАВЕРШЕН ===\n";
?>

