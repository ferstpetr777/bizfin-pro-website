<?php
/**
 * Оптимизация изображений с помощью ABP Image Generator
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "🖼️ ОПТИМИЗАЦИЯ ИЗОБРАЖЕНИЙ\n";
echo "===========================\n\n";

// Проверяем наличие плагина ABP Image Generator
$plugin_path = 'wp-content/plugins/bizfin-seo-article-generator/';
if (!file_exists($plugin_path)) {
    echo "❌ Плагин ABP Image Generator не найден в $plugin_path\n";
    echo "🔍 Ищем плагин...\n";
    
    $possible_paths = [
        'wp-content/plugins/bizfin-seo-article-generator/',
        'wp-content/plugins/abp-image-generator/',
        'wp-content/plugins/image-generator/'
    ];
    
    $found_plugin = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $plugin_path = $path;
            $found_plugin = true;
            echo "✅ Найден плагин в: $path\n";
            break;
        }
    }
    
    if (!$found_plugin) {
        echo "❌ Плагин ABP Image Generator не найден\n";
        echo "📋 Доступные плагины:\n";
        $plugins = glob('wp-content/plugins/*', GLOB_ONLYDIR);
        foreach ($plugins as $plugin) {
            echo "   • " . basename($plugin) . "\n";
        }
        exit(1);
    }
}

echo "✅ Плагин найден: $plugin_path\n\n";

// Получаем все изображения из медиабиблиотеки
echo "📊 Анализ изображений в медиабиблиотеке:\n";
$images = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => -1,
    'post_status' => 'inherit'
]);

echo "   • Всего изображений: " . count($images) . "\n";

// Анализируем размеры изображений
$large_images = [];
$total_size = 0;

foreach ($images as $image) {
    $file_path = get_attached_file($image->ID);
    if ($file_path && file_exists($file_path)) {
        $file_size = filesize($file_path);
        $total_size += $file_size;
        
        // Изображения больше 1MB считаем большими
        if ($file_size > 1024 * 1024) {
            $large_images[] = [
                'id' => $image->ID,
                'title' => $image->post_title,
                'size' => $file_size,
                'path' => $file_path
            ];
        }
    }
}

echo "   • Общий размер: " . round($total_size / 1024 / 1024, 2) . " MB\n";
echo "   • Больших изображений (>1MB): " . count($large_images) . "\n\n";

if (empty($large_images)) {
    echo "✅ Все изображения уже оптимизированы!\n";
    exit(0);
}

echo "🔧 ОПТИМИЗАЦИЯ БОЛЬШИХ ИЗОБРАЖЕНИЙ:\n";
echo "====================================\n\n";

$optimized_count = 0;
$total_saved = 0;

foreach ($large_images as $image_data) {
    echo "📸 Обрабатываем: " . $image_data['title'] . "\n";
    echo "   • Текущий размер: " . round($image_data['size'] / 1024 / 1024, 2) . " MB\n";
    
    // Создаем оптимизированную версию
    $original_path = $image_data['path'];
    $path_info = pathinfo($original_path);
    $optimized_path = $path_info['dirname'] . '/' . $path_info['filename'] . '_optimized.' . $path_info['extension'];
    
    // Используем ImageMagick или GD для сжатия
    if (extension_loaded('imagick')) {
        $imagick = new Imagick($original_path);
        
        // Устанавливаем качество сжатия
        $imagick->setImageCompressionQuality(85);
        
        // Уменьшаем размер если больше 1920px
        $geometry = $imagick->getImageGeometry();
        if ($geometry['width'] > 1920 || $geometry['height'] > 1920) {
            $imagick->resizeImage(1920, 1920, Imagick::FILTER_LANCZOS, 1, true);
        }
        
        $imagick->writeImage($optimized_path);
        $imagick->clear();
        $imagick->destroy();
        
    } elseif (extension_loaded('gd')) {
        $image_info = getimagesize($original_path);
        $mime_type = $image_info['mime'];
        
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($original_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($original_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($original_path);
                break;
            default:
                echo "   ⚠️ Неподдерживаемый формат: $mime_type\n";
                continue 2;
        }
        
        if (!$source) {
            echo "   ❌ Ошибка загрузки изображения\n";
            continue;
        }
        
        // Уменьшаем размер если нужно
        $width = imagesx($source);
        $height = imagesy($source);
        
        if ($width > 1920 || $height > 1920) {
            $ratio = min(1920 / $width, 1920 / $height);
            $new_width = $width * $ratio;
            $new_height = $height * $ratio;
            
            $resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }
        
        // Сохраняем с качеством 85%
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($source, $optimized_path, 85);
                break;
            case 'image/png':
                imagepng($source, $optimized_path, 8);
                break;
            case 'image/gif':
                imagegif($source, $optimized_path);
                break;
        }
        
        imagedestroy($source);
    } else {
        echo "   ❌ Не установлены ImageMagick или GD\n";
        continue;
    }
    
    if (file_exists($optimized_path)) {
        $new_size = filesize($optimized_path);
        $saved = $image_data['size'] - $new_size;
        $saved_percent = round(($saved / $image_data['size']) * 100, 1);
        
        echo "   ✅ Оптимизировано: " . round($new_size / 1024 / 1024, 2) . " MB\n";
        echo "   💾 Сэкономлено: " . round($saved / 1024 / 1024, 2) . " MB ($saved_percent%)\n";
        
        // Заменяем оригинал оптимизированной версией
        if (copy($optimized_path, $original_path)) {
            unlink($optimized_path);
            $optimized_count++;
            $total_saved += $saved;
            echo "   🔄 Оригинал заменен\n";
        } else {
            echo "   ⚠️ Не удалось заменить оригинал\n";
        }
    } else {
        echo "   ❌ Ошибка создания оптимизированного изображения\n";
    }
    
    echo "\n";
}

echo "📊 РЕЗУЛЬТАТЫ ОПТИМИЗАЦИИ:\n";
echo "===========================\n";
echo "✅ Обработано изображений: $optimized_count\n";
echo "💾 Общая экономия: " . round($total_saved / 1024 / 1024, 2) . " MB\n";
echo "📈 Процент экономии: " . round(($total_saved / $total_size) * 100, 1) . "%\n\n";

// Создаем WebP версии для современных браузеров
echo "🌐 СОЗДАНИЕ WEBP ВЕРСИЙ:\n";
echo "=========================\n";

$webp_count = 0;
foreach ($images as $image) {
    $file_path = get_attached_file($image->ID);
    if ($file_path && file_exists($file_path)) {
        $path_info = pathinfo($file_path);
        $webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
        
        if (!file_exists($webp_path)) {
            if (extension_loaded('imagick')) {
                $imagick = new Imagick($file_path);
                $imagick->setImageFormat('webp');
                $imagick->setImageCompressionQuality(85);
                $imagick->writeImage($webp_path);
                $imagick->clear();
                $imagick->destroy();
                $webp_count++;
            }
        }
    }
}

echo "✅ Создано WebP версий: $webp_count\n\n";

// Обновляем sitemap изображений
echo "🗺️ ОБНОВЛЕНИЕ SITEMAP ИЗОБРАЖЕНИЙ:\n";
echo "===================================\n";

$sitemap_images_content = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

foreach ($images as $image) {
    $file_path = get_attached_file($image->ID);
    if ($file_path && file_exists($file_path)) {
        $image_url = wp_get_attachment_url($image->ID);
        $image_title = $image->post_title ?: $image->post_name;
        
        $sitemap_images_content .= '
  <url>
    <loc>' . $image_url . '</loc>
    <image:image>
      <image:loc>' . $image_url . '</image:loc>
      <image:title>' . htmlspecialchars($image_title) . '</image:title>
      <image:caption></image:caption>
    </image:image>
  </url>';
    }
}

$sitemap_images_content .= '
</urlset>';

file_put_contents('sitemap-images.xml', $sitemap_images_content);
echo "✅ Sitemap изображений обновлен\n\n";

echo "🎯 РЕКОМЕНДАЦИИ:\n";
echo "1. Настроить автоматическую оптимизацию новых изображений\n";
echo "2. Использовать WebP формат для современных браузеров\n";
echo "3. Настроить lazy loading для изображений\n";
echo "4. Рассмотреть использование CDN\n";
echo "5. Регулярно проверять размеры изображений\n\n";

echo "✅ Оптимизация изображений завершена!\n";
?>

