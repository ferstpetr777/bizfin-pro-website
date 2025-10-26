<?php
/**
 * Скрипт для обновления существующих постов с кириллическими slug
 * Запуск: php update-cyrillic-slugs.php
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-includes/wp-db.php');
require_once('wp-includes/pluggable.php');

// Функция для транслитерации кириллических символов в латинские
function cyrillic_to_latin_transliteration($text) {
    $transliteration = array(
        // Русские буквы
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        
        // Заглавные русские буквы
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
        'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
        'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        
        // Специальные символы
        '№' => 'no', ' ' => '-', '_' => '-', '.' => '-', ',' => '-', '!' => '', '?' => '',
        ':' => '', ';' => '', '"' => '', "'" => '', '(' => '', ')' => '', '[' => '', ']' => '',
        '{' => '', '}' => '', '<' => '', '>' => '', '|' => '', '\\' => '', '/' => '-',
        '=' => '', '+' => '', '*' => '', '&' => 'and', '%' => '', '$' => '', '#' => '',
        '@' => 'at', '~' => '', '`' => '', '^' => '', '№' => 'no'
    );
    
    // Применяем транслитерацию
    $text = strtr($text, $transliteration);
    
    // Убираем множественные дефисы и заменяем на один
    $text = preg_replace('/-+/', '-', $text);
    
    // Убираем дефисы в начале и конце
    $text = trim($text, '-');
    
    // Оставляем только латинские буквы, цифры и дефисы
    $text = preg_replace('/[^a-zA-Z0-9\-]/', '', $text);
    
    // Ограничиваем длину slug (максимум 200 символов)
    if (strlen($text) > 200) {
        $text = substr($text, 0, 200);
        $text = rtrim($text, '-');
    }
    
    return strtolower($text);
}

// Функция для обновления всех кириллических slug
function update_all_cyrillic_slugs() {
    global $wpdb;
    
    echo "Начинаем поиск постов с кириллическими slug...\n";
    
    // Находим все посты и страницы с кириллическими slug
    $posts = $wpdb->get_results("
        SELECT ID, post_title, post_name, post_type 
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND post_name REGEXP '[а-яё]'
        ORDER BY ID DESC
    ");
    
    if (empty($posts)) {
        echo "Посты с кириллическими slug не найдены.\n";
        return 0;
    }
    
    echo "Найдено " . count($posts) . " постов с кириллическими slug.\n\n";
    
    $updated_count = 0;
    $skipped_count = 0;
    
    foreach ($posts as $post) {
        echo "Обрабатываем пост ID {$post->ID}: '{$post->post_title}'\n";
        echo "  Старый slug: '{$post->post_name}'\n";
        
        $new_slug = cyrillic_to_latin_transliteration($post->post_title);
        
        // Проверяем уникальность
        $original_slug = $new_slug;
        $counter = 1;
        
        while (true) {
            $existing_post = get_page_by_path($new_slug, OBJECT, $post->post_type);
            
            if (!$existing_post || $existing_post->ID == $post->ID) {
                break;
            }
            
            $new_slug = $original_slug . '-' . $counter;
            $counter++;
            
            if ($counter > 1000) {
                $new_slug = $original_slug . '-' . time();
                break;
            }
        }
        
        echo "  Новый slug: '{$new_slug}'\n";
        
        // Обновляем slug
        $result = wp_update_post(array(
            'ID' => $post->ID,
            'post_name' => $new_slug
        ));
        
        if ($result && !is_wp_error($result)) {
            $updated_count++;
            echo "  ✓ Обновлен успешно\n";
        } else {
            $skipped_count++;
            echo "  ✗ Ошибка при обновлении\n";
        }
        
        echo "\n";
    }
    
    echo "Обновление завершено!\n";
    echo "Обновлено: {$updated_count} постов\n";
    echo "Пропущено: {$skipped_count} постов\n";
    
    return $updated_count;
}

// Запускаем обновление
echo "=== Обновление кириллических slug ===\n\n";
$updated = update_all_cyrillic_slugs();
echo "\n=== Готово! ===\n";
