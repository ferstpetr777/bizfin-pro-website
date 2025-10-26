<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== АНАЛИЗ ДУБЛИРОВАНИЯ ИЗОБРАЖЕНИЙ ===\n\n";

// Получаем проблемную статью для детального анализа
$post_id = 3055; // ID статьи "Банки, выдающие банковские гарантии на возврат аванса"
$post = get_post($post_id);

if (!$post) {
    echo "Статья не найдена!\n";
    exit;
}

echo "Анализируем статью: " . $post->post_title . " (ID: " . $post_id . ")\n";
echo "URL: " . get_permalink($post_id) . "\n\n";

$content = $post->post_content;

// Ищем все изображения в контенте
preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);

echo "=== ИЗОБРАЖЕНИЯ В КОНТЕНТЕ ===\n";
echo "Найдено изображений: " . count($matches[1]) . "\n\n";

foreach ($matches[1] as $index => $src) {
    echo ($index + 1) . ". " . $src . "\n";
    
    $parsed_url = parse_url($src);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : $src;
    $full_path = ABSPATH . ltrim($path, '/');
    
    if (file_exists($full_path)) {
        echo "   ✅ Файл существует\n";
    } else {
        echo "   ❌ Файл не найден\n";
        
        // Проверяем WebP версию
        $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $full_path);
        if (file_exists($webp_path)) {
            echo "   ✅ WebP файл найден: " . str_replace(ABSPATH, '', $webp_path) . "\n";
        } else {
            echo "   ❌ WebP файл тоже не найден\n";
        }
    }
    echo "\n";
}

// Анализируем featured image
echo "=== FEATURED IMAGE ===\n";
$thumbnail_id = get_post_thumbnail_id($post_id);
if ($thumbnail_id) {
    echo "Featured image ID: " . $thumbnail_id . "\n";
    
    $file_path = get_attached_file($thumbnail_id);
    echo "Файл: " . $file_path . "\n";
    
    if (file_exists($file_path)) {
        echo "✅ Featured image файл существует\n";
    } else {
        echo "❌ Featured image файл не найден\n";
        
        // Проверяем WebP версию
        $webp_path = str_replace(array('.png', '.jpg', '.jpeg'), '.webp', $file_path);
        if (file_exists($webp_path)) {
            echo "✅ WebP файл для featured image найден: " . str_replace(ABSPATH, '', $webp_path) . "\n";
        } else {
            echo "❌ WebP файл для featured image тоже не найден\n";
        }
    }
} else {
    echo "Featured image не установлена\n";
}

echo "\n=== АНАЛИЗ ПРИЧИН ДУБЛИРОВАНИЯ ===\n";

// Проверяем, есть ли дублирующиеся URL
$unique_urls = array_unique($matches[1]);
$duplicates = count($matches[1]) - count($unique_urls);

echo "Уникальных URL: " . count($unique_urls) . "\n";
echo "Дублирующихся URL: " . $duplicates . "\n";

if ($duplicates > 0) {
    echo "\nДублирующиеся изображения:\n";
    $url_counts = array_count_values($matches[1]);
    foreach ($url_counts as $url => $count) {
        if ($count > 1) {
            echo "- " . $url . " (встречается " . $count . " раз)\n";
        }
    }
}

// Проверяем, есть ли placeholder изображения
$placeholder_count = 0;
foreach ($matches[1] as $src) {
    if (strpos($src, 'placeholder-image.jpg') !== false) {
        $placeholder_count++;
    }
}

echo "\nPlaceholder изображений: " . $placeholder_count . "\n";

// Анализируем возможные причины
echo "\n=== ВОЗМОЖНЫЕ ПРИЧИНЫ ДУБЛИРОВАНИЯ ===\n";

if ($duplicates > 0) {
    echo "1. ❌ Дублирующиеся URL в контенте статьи\n";
}

if ($placeholder_count > 0) {
    echo "2. ⚠️  Использование placeholder изображений\n";
}

// Проверяем, были ли множественные обновления контента
$revisions = wp_get_post_revisions($post_id);
echo "3. 📝 Количество ревизий статьи: " . count($revisions) . "\n";

if (count($revisions) > 5) {
    echo "   ⚠️  Много ревизий - возможно, множественные правки контента\n";
}

// Проверяем, есть ли проблемы с кодировкой URL
echo "\n=== ПРОВЕРКА КОДИРОВКИ URL ===\n";
foreach ($matches[1] as $index => $src) {
    if (strpos($src, '%') !== false) {
        echo "URL с кодировкой: " . $src . "\n";
        echo "Декодированный: " . urldecode($src) . "\n";
    }
}

echo "\n=== РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ ===\n";
echo "1. Удалить дублирующиеся изображения из контента\n";
echo "2. Заменить битые PNG ссылки на рабочие WebP\n";
echo "3. Исправить featured image\n";
echo "4. Убрать placeholder изображения\n";
echo "5. Проверить кодировку URL\n";

echo "\n=== АНАЛИЗ ЗАВЕРШЕН ===\n";
?>

