<?php
/**
 * Скрипт для скачивания благодарственных писем из галереи зеркального сайта
 */

// URL зеркального сайта
$mirror_url = 'https://xn--90amcbz4b.xn--p1ai/';

// Директория для сохранения
$download_dir = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/downloads/testimonials-mirror/';

// Очищаем директорию от предыдущих файлов
$files = glob($download_dir . '*');
foreach($files as $file) {
    if(is_file($file)) {
        unlink($file);
    }
}

echo "Поиск благодарственных писем в галерее зеркального сайта...\n\n";

// Получаем HTML страницы
$html = file_get_contents($mirror_url);

if ($html === false) {
    echo "Ошибка: не удалось загрузить страницу\n";
    exit;
}

// Ищем все изображения в галерее (по meta itemprop="image" content)
preg_match_all('/<meta itemprop="image" content="([^"]+)"/', $html, $meta_matches);

// Также ищем в data-original атрибутах
preg_match_all('/data-original="([^"]+)"/', $html, $data_matches);

// Ищем в background-image стилях
preg_match_all('/background-image:url\(["\']?([^"\']+)["\']?\)/', $html, $bg_matches);

$image_urls = [];

// Добавляем найденные URL
foreach ($meta_matches[1] as $url) {
    if (strpos($url, '.png') !== false || strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false) {
        $image_urls[] = $url;
    }
}

foreach ($data_matches[1] as $url) {
    if (strpos($url, '.png') !== false || strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false) {
        $image_urls[] = $url;
    }
}

foreach ($bg_matches[1] as $url) {
    if (strpos($url, '.png') !== false || strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false) {
        $image_urls[] = $url;
    }
}

// Убираем дубликаты и фильтруем только полноразмерные изображения
$image_urls = array_unique($image_urls);
$filtered_urls = [];

foreach ($image_urls as $url) {
    // Пропускаем миниатюры (resizeb/20x/)
    if (strpos($url, 'resizeb/20x/') !== false) {
        continue;
    }
    
    // Пропускаем пустые изображения
    if (strpos($url, '/empty/') !== false) {
        continue;
    }
    
    // Оставляем только полноразмерные изображения
    if (strpos($url, 'static.tildacdn.com') !== false && 
        (strpos($url, '.png') !== false || strpos($url, '.jpg') !== false || strpos($url, '.jpeg') !== false)) {
        $filtered_urls[] = $url;
    }
}

echo "Найдено потенциальных изображений: " . count($filtered_urls) . "\n\n";

// Скачиваем изображения
$downloaded_count = 0;
foreach ($filtered_urls as $index => $url) {
    echo "Скачиваем: {$url}\n";
    
    // Получаем заголовки для проверки размера
    $headers = get_headers($url, 1);
    $content_length = 0;
    
    if (isset($headers['Content-Length'])) {
        $content_length = is_array($headers['Content-Length']) ? 
                         end($headers['Content-Length']) : 
                         $headers['Content-Length'];
    }
    
    // Пропускаем маленькие файлы (менее 50KB)
    if ($content_length < 51200) {
        echo "Файл слишком маленький ({$content_length} байт), пропускаем\n";
        continue;
    }
    
    echo "Размер файла: " . round($content_length / 1024, 2) . " KB\n";
    
    // Получаем содержимое изображения
    $image_data = file_get_contents($url);
    
    if ($image_data === false) {
        echo "Ошибка скачивания\n";
        continue;
    }
    
    // Определяем расширение файла
    $extension = 'jpg';
    if (strpos($url, '.png') !== false) {
        $extension = 'png';
    } elseif (strpos($url, '.jpeg') !== false) {
        $extension = 'jpeg';
    }
    
    // Генерируем имя файла
    $filename = 'gallery_testimonial_' . ($index + 1) . '.' . $extension;
    $file_path = $download_dir . $filename;
    
    // Сохраняем файл
    if (file_put_contents($file_path, $image_data) !== false) {
        echo "Сохранено как: {$filename}\n";
        $downloaded_count++;
    } else {
        echo "Ошибка сохранения: {$filename}\n";
    }
    
    echo "---\n";
}

echo "\nСкачано файлов: {$downloaded_count}\n";
echo "Директория: {$download_dir}\n";
?>
