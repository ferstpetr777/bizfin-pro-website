<?php
require_once('wp-config.php');

// Исправляем конкретную статью
$post_id = 2863; // ID статьи "Условия получения банковской гарантии"

echo "=== ИСПРАВЛЕНИЕ СТАТЬИ ID $post_id ===\n\n";

// Получаем текущие данные
$post_title = get_the_title($post_id);
$post_content = get_post_field('post_content', $post_id);
$post_excerpt = get_post_field('post_excerpt', $post_id);
$featured_image_id = get_post_thumbnail_id($post_id);

echo "Текущий заголовок: $post_title\n";
echo "Featured Image ID: $featured_image_id\n\n";

// Исправляем путь к изображению
if ($featured_image_id) {
    $current_file = get_attached_file($featured_image_id);
    echo "Текущий путь к файлу: $current_file\n";
    
    // Исправляем дублированный путь
    $corrected_file = str_replace('/wp-content/uploads/wp-content/uploads/', '/wp-content/uploads/', $current_file);
    
    if ($corrected_file !== $current_file) {
        update_post_meta($featured_image_id, '_wp_attached_file', $corrected_file);
        echo "Исправлен путь к файлу: $corrected_file\n";
        
        // Проверяем, существует ли файл
        if (file_exists($corrected_file)) {
            echo "✅ Файл существует\n";
        } else {
            echo "❌ Файл не найден\n";
        }
    }
}

// Восстанавливаем контент статьи (удаляем CSS)
if (strpos($post_content, '.intro {') !== false || strpos($post_content, '.toc {') !== false) {
    echo "\n=== ВОССТАНОВЛЕНИЕ КОНТЕНТА ===\n";
    echo "Найден CSS код в контенте, удаляем...\n";
    
    // Удаляем весь CSS код
    $new_content = preg_replace('/\.intro\s*\{[^}]*\}/', '', $post_content);
    $new_content = preg_replace('/\.toc\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.article-image\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.example\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.checklist\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.warning\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.red-flag\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.faq\s*\{[^}]*\}/', '', $new_content);
    $new_content = preg_replace('/\.faq-item\s*\{[^}]*\}/', '', $new_content);
    
    // Удаляем все CSS свойства
    $new_content = preg_replace('/border-radius:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/border-left:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/border:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/box-shadow:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/width:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/height:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/max-width:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/display:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/padding:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/margin:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/position:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/left:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/content:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/transition:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/text-decoration:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/list-style:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/color:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/background:[^;]*;/', '', $new_content);
    $new_content = preg_replace('/solid[^;]*;/', '', $new_content);
    $new_content = preg_replace('/rgba\([^)]*\)/', '', $new_content);
    $new_content = preg_replace('/#[a-fA-F0-9]{6}/', '', $new_content);
    $new_content = preg_replace('/var\(--[^)]*\)/', '', $new_content);
    $new_content = preg_replace('/[0-9]+px/', '', $new_content);
    $new_content = preg_replace('/[0-9]+rem/', '', $new_content);
    $new_content = preg_replace('/[0-9]+\.[0-9]+s/', '', $new_content);
    
    // Убираем лишние пробелы
    $new_content = preg_replace('/\s+/', ' ', $new_content);
    $new_content = trim($new_content);
    
    // Если контент стал пустым, добавляем базовый контент
    if (empty($new_content)) {
        $new_content = '<!-- wp:paragraph -->
<p>Содержимое статьи будет восстановлено.</p>
<!-- /wp:paragraph -->';
    }
    
    // Обновляем статью
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content,
    ));
    
    echo "Контент статьи обновлен\n";
    echo "Новый контент (первые 200 символов): " . substr($new_content, 0, 200) . "...\n";
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
echo "Статья ID $post_id исправлена\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен\n";
