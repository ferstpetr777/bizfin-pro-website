<?php
require_once('wp-config.php');

/**
 * Скрипт для восстановления статей из архивной копии
 * Использование: php restore_article_from_archive.php [ID_СТАТЬИ]
 */

// Функция для поиска контента статьи в архивном SQL файле
function find_article_content_in_archive($post_id) {
    $archive_sql_path = '/tmp/bizfin_restore/bizfin-pro.ru/bizfin-pro.ru_database_backup_20251023_130034.sql';
    
    if (!file_exists($archive_sql_path)) {
        return false;
    }
    
    // Ищем статью в архиве по ID - ищем все записи с этим ID
    $grep_command = "grep -A 2000 \"INSERT INTO.*wp_posts.*$post_id\" $archive_sql_path | head -1000";
    $output = shell_exec($grep_command);
    
    if (empty($output)) {
        return false;
    }
    
    // Ищем post_content в SQL записи - ищем 5-е поле (post_content) в VALUES
    // Формат: VALUES (ID, post_author, post_date, post_date_gmt, post_content, post_title, ...)
    if (preg_match('/VALUES\s*\(\s*' . $post_id . '\s*,\s*[^,]+,\s*[^,]+,\s*[^,]+,\s*\'([^\']*(?:\\\\.[^\']*)*)\'/', $output, $matches)) {
        $content = $matches[1];
        // Декодируем экранированные символы
        $content = str_replace('\\n', "\n", $content);
        $content = str_replace('\\r', "\r", $content);
        $content = str_replace('\\t', "\t", $content);
        $content = str_replace('\\"', '"', $content);
        $content = str_replace("\\'", "'", $content);
        $content = str_replace('\\\\', '\\', $content);
        
        return $content;
    }
    
    // Альтернативный поиск - ищем post_content в любом месте строки
    if (preg_match('/post_content\',\s*\'([^\']*(?:\\\\.[^\']*)*)\'/', $output, $matches)) {
        $content = $matches[1];
        // Декодируем экранированные символы
        $content = str_replace('\\n', "\n", $content);
        $content = str_replace('\\r', "\r", $content);
        $content = str_replace('\\t', "\t", $content);
        $content = str_replace('\\"', '"', $content);
        $content = str_replace("\\'", "'", $content);
        $content = str_replace('\\\\', '\\', $content);
        
        return $content;
    }
    
    return false;
}

// Функция для проверки качества контента
function check_content_quality($content) {
    $quality_checks = array(
        'length' => strlen($content),
        'has_paragraphs' => strpos($content, '<p>') !== false,
        'has_headings' => strpos($content, '<h2>') !== false || strpos($content, '<h3>') !== false,
        'has_lists' => strpos($content, '<ul>') !== false || strpos($content, '<ol>') !== false,
        'is_template' => strpos($content, 'Полное руководство по теме статьи') !== false,
        'has_real_content' => strlen($content) > 5000 && strpos($content, 'Полное руководство по теме статьи') === false
    );
    
    return $quality_checks;
}

// Функция для восстановления статьи
function restore_article_from_archive($post_id) {
    echo "=== ВОССТАНОВЛЕНИЕ СТАТЬИ ID $post_id ИЗ АРХИВА ===\n";
    
    // Получаем информацию о статье
    $post = get_post($post_id);
    if (!$post) {
        echo "❌ Статья с ID $post_id не найдена!\n";
        return false;
    }
    
    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    
    echo "Заголовок: $title\n";
    echo "URL: $url\n";
    echo "Текущий контент (длина): " . strlen($post->post_content) . " символов\n";
    
    // Ищем контент в архиве
    echo "Поиск контента в архивной копии...\n";
    $archive_content = find_article_content_in_archive($post_id);
    
    if (!$archive_content) {
        echo "❌ Контент не найден в архивной копии!\n";
        return false;
    }
    
    echo "✅ Контент найден в архиве (длина): " . strlen($archive_content) . " символов\n";
    
    // Проверяем качество контента
    $quality = check_content_quality($archive_content);
    echo "\n=== АНАЛИЗ КАЧЕСТВА КОНТЕНТА ===\n";
    echo "Длина контента: " . $quality['length'] . " символов\n";
    echo "Содержит параграфы: " . ($quality['has_paragraphs'] ? '✅' : '❌') . "\n";
    echo "Содержит заголовки: " . ($quality['has_headings'] ? '✅' : '❌') . "\n";
    echo "Содержит списки: " . ($quality['has_lists'] ? '✅' : '❌') . "\n";
    echo "Это шаблон: " . ($quality['is_template'] ? '❌ ДА' : '✅ НЕТ') . "\n";
    echo "Реальный контент: " . ($quality['has_real_content'] ? '✅ ДА' : '❌ НЕТ') . "\n";
    
    if (!$quality['has_real_content']) {
        echo "⚠️  ВНИМАНИЕ: Контент может быть шаблонным или недостаточно полным!\n";
        echo "Продолжить восстановление? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim($line) !== 'y' && trim($line) !== 'Y') {
            echo "❌ Восстановление отменено пользователем\n";
            return false;
        }
    }
    
    // Обновляем статью
    echo "\nОбновление статьи...\n";
    $updated_post = array(
        'ID'           => $post_id,
        'post_content' => $archive_content,
    );
    
    $result = wp_update_post($updated_post);
    wp_cache_flush();
    
    if ($result) {
        echo "✅ Статья ID $post_id успешно восстановлена из архива!\n";
        echo "URL: $url\n";
        
        // Показываем первые 500 символов восстановленного контента
        echo "\n=== ПРЕВЬЮ ВОССТАНОВЛЕННОГО КОНТЕНТА ===\n";
        echo substr(strip_tags($archive_content), 0, 500) . "...\n";
        
        return true;
    } else {
        echo "❌ Ошибка при восстановлении статьи ID $post_id\n";
        return false;
    }
}

// Основная логика
if (isset($argv[1]) && is_numeric($argv[1])) {
    $post_id = intval($argv[1]);
    restore_article_from_archive($post_id);
} else {
    echo "Использование: php restore_article_from_archive.php [ID_СТАТЬИ]\n";
    echo "Пример: php restore_article_from_archive.php 2526\n";
}
?>
