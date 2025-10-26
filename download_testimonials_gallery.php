<?php
/**
 * Скрипт для скачивания всех изображений из галереи благодарственных писем
 * с сайта https://xn--90amcbz4b.xn--p1ai/
 */

echo "Скачивание галереи благодарственных писем с сайта bizfin.рф...\n\n";

$site_url = 'https://xn--90amcbz4b.xn--p1ai/';
$download_dir = __DIR__ . '/downloads/testimonials-gallery/';

// Создаем директорию для скачивания
if (!file_exists($download_dir)) {
    mkdir($download_dir, 0755, true);
    echo "Создана директория: $download_dir\n";
}

// Получаем содержимое страницы
$html = file_get_contents($site_url);
if ($html === false) {
    die("Не удалось получить содержимое страницы: $site_url\n");
}

echo "Страница загружена успешно\n";

// Создаем DOM объект
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Ищем все изображения в галерее
$image_nodes = $xpath->query("//img[@src]");

echo "Найдено изображений: " . $image_nodes->length . "\n\n";

$downloaded_count = 0;
$file_counter = 1;

foreach ($image_nodes as $node) {
    $src = $node->getAttribute('src');
    $alt = $node->getAttribute('alt');
    
    // Пропускаем логотипы и служебные изображения
    if (strpos($src, 'logo') !== false || 
        strpos($src, 'phone') !== false || 
        strpos($src, 'icon') !== false ||
        strpos($src, 'button') !== false ||
        strpos($src, 'arrow') !== false) {
        continue;
    }
    
    // Получаем расширение файла
    $extension = pathinfo($src, PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = 'jpg'; // По умолчанию jpg
    }
    
    $filename = 'testimonial_gallery_' . sprintf('%02d', $file_counter) . '.' . $extension;
    $filepath = $download_dir . $filename;
    
    echo "Скачиваем: $src\n";
    
    // Скачиваем изображение
    $image_data = @file_get_contents($src);
    
    if ($image_data === false || strlen($image_data) < 1000) {
        echo "Файл не удалось скачать или он слишком маленький (" . strlen($image_data) . " байт), пропускаем\n";
        continue;
    }
    
    // Сохраняем файл
    if (file_put_contents($filepath, $image_data)) {
        echo "✅ Сохранено как: $filename (" . round(strlen($image_data) / 1024, 2) . " KB)\n";
        
        // Сохраняем информацию о файле
        $info_file = $download_dir . 'testimonial_gallery_' . sprintf('%02d', $file_counter) . '_info.txt';
        $info_content = "URL: $src\n";
        $info_content .= "Alt: $alt\n";
        $info_content .= "Размер: " . round(strlen($image_data) / 1024, 2) . " KB\n";
        $info_content .= "Дата скачивания: " . date('Y-m-d H:i:s') . "\n";
        
        file_put_contents($info_file, $info_content);
        
        $downloaded_count++;
        $file_counter++;
    } else {
        echo "❌ Ошибка сохранения файла: $filename\n";
    }
    
    echo "---\n";
}

echo "\n✅ Скачивание завершено!\n";
echo "Скачано файлов: $downloaded_count\n";
echo "Директория: $download_dir\n";

// Создаем общий файл с информацией
$summary_file = $download_dir . 'gallery_summary.txt';
$summary_content = "Галерея благодарственных писем с сайта bizfin.рф\n";
$summary_content .= "Дата скачивания: " . date('Y-m-d H:i:s') . "\n";
$summary_content .= "Всего скачано файлов: $downloaded_count\n";
$summary_content .= "URL сайта: $site_url\n\n";

file_put_contents($summary_file, $summary_content);

echo "Создан файл с общей информацией: gallery_summary.txt\n";
?>
