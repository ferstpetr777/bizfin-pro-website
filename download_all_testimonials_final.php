<?php
/**
 * Финальный скрипт для скачивания всех доступных изображений благодарственных писем
 */

echo "Скачивание всех доступных изображений благодарственных писем...\n\n";

$download_dir = __DIR__ . '/downloads/testimonials-final/';

// Создаем директорию для скачивания
if (!file_exists($download_dir)) {
    mkdir($download_dir, 0755, true);
    echo "Создана директория: $download_dir\n";
}

// Все найденные URL изображений
$all_testimonial_images = [
    // Первый набор
    'https://static.tildacdn.com/tild6636-3535-4338-b262-373137323334/SBI-Bank-1.png',
    'https://static.tildacdn.com/tild3263-3162-4530-b036-333061343736/--1.png',
    'https://static.tildacdn.com/tild6266-6335-4161-a635-343161396432/-1.png',
    'https://static.tildacdn.com/tild6664-3033-4035-a561-643031323336/-1.png',
    'https://static.tildacdn.com/tild3435-6362-4336-b130-313761376466/-1.png',
    'https://static.tildacdn.com/tild3565-3262-4063-a261-393663633161/-1.png',
    
    // Дополнительные URL из предыдущих попыток
    'https://static.tildacdn.com/tild6266-6335-4161-a635-343161396432/--1.png',
    'https://static.tildacdn.com/tild6664-3033-4035-a561-643031323336/--1.png',
    'https://static.tildacdn.com/tild3435-6362-4336-b130-313761376466/--1.png',
    'https://static.tildacdn.com/tild3565-3262-4063-a261-393663633161/--1.png',
];

$downloaded_count = 0;
$file_counter = 1;

foreach ($all_testimonial_images as $url) {
    // Получаем расширение файла
    $extension = pathinfo($url, PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = 'png';
    }
    
    $filename = 'testimonial_' . sprintf('%02d', $file_counter) . '.' . $extension;
    $filepath = $download_dir . $filename;
    
    echo "Скачиваем: $url\n";
    
    // Скачиваем изображение
    $image_data = @file_get_contents($url);
    
    if ($image_data === false || strlen($image_data) < 1000) {
        echo "❌ Файл не удалось скачать или он слишком маленький (" . strlen($image_data) . " байт), пропускаем\n";
        $file_counter++;
        continue;
    }
    
    // Сохраняем файл
    if (file_put_contents($filepath, $image_data)) {
        echo "✅ Сохранено как: $filename (" . round(strlen($image_data) / 1024, 2) . " KB)\n";
        
        // Сохраняем информацию о файле
        $info_file = $download_dir . 'info_' . $filename . '.txt';
        $info_content = "URL: $url\n";
        $info_content .= "Описание: Благодарственное письмо от клиента\n";
        $info_content .= "Размер: " . round(strlen($image_data) / 1024, 2) . " KB\n";
        $info_content .= "Дата скачивания: " . date('Y-m-d H:i:s') . "\n";
        
        file_put_contents($info_file, $info_content);
        
        $downloaded_count++;
    } else {
        echo "❌ Ошибка сохранения файла: $filename\n";
    }
    
    echo "---\n";
    $file_counter++;
    
    // Небольшая пауза между запросами
    sleep(1);
}

echo "\n✅ Скачивание завершено!\n";
echo "Скачано файлов: $downloaded_count\n";
echo "Директория: $download_dir\n";

// Создаем общий файл с информацией
$summary_file = $download_dir . 'download_summary.txt';
$summary_content = "Все доступные изображения благодарственных писем\n";
$summary_content .= "Дата скачивания: " . date('Y-m-d H:i:s') . "\n";
$summary_content .= "Всего скачано файлов: $downloaded_count\n";
$summary_content .= "URL сайта: https://xn--90amcbz4b.xn--p1ai/\n\n";

$summary_content .= "Скачанные файлы:\n";
for ($i = 1; $i <= $downloaded_count; $i++) {
    $filename = 'testimonial_' . sprintf('%02d', $i) . '.png';
    $summary_content .= "- $filename\n";
}

file_put_contents($summary_file, $summary_content);

echo "Создан файл с общей информацией: download_summary.txt\n";
?>
