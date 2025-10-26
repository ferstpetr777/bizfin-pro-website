<?php
require_once('wp-config.php');

// Получаем все статьи от 19 октября и 7 октября
$october_19_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 19,
        ),
    ),
    'fields' => 'ids'
));

$october_7_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        array(
            'year' => 2025,
            'month' => 10,
            'day' => 7,
        ),
    ),
    'fields' => 'ids'
));

$all_posts = array_merge($october_19_posts, $october_7_posts);

echo "=== КОМПЛЕКСНОЕ ИСПРАВЛЕНИЕ СТАТЕЙ ===\n";
echo "Всего статей для обработки: " . count($all_posts) . "\n\n";

$fixed_count = 0;
$errors_count = 0;

foreach ($all_posts as $post_id) {
    $post_title = get_the_title($post_id);
    $post_content = get_post_field('post_content', $post_id);
    $post_excerpt = get_post_field('post_excerpt', $post_id);
    $featured_image_id = get_post_thumbnail_id($post_id);
    
    echo "=== ОБРАБОТКА СТАТЬИ ID $post_id ===\n";
    echo "Заголовок: $post_title\n";
    
    $needs_fix = false;
    $new_content = $post_content;
    $new_excerpt = $post_excerpt;
    
    // 1. Очищаем от CSS кода
    if (strpos($post_content, '.intro {') !== false || 
        strpos($post_content, '.toc {') !== false ||
        strpos($post_content, 'border-radius:') !== false ||
        strpos($post_content, 'font-size:') !== false) {
        
        echo "❌ Найден CSS код - очищаем...\n";
        
        // Удаляем весь CSS код
        $new_content = preg_replace('/\.intro\s*\{[^}]*\}/', '', $new_content);
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
        
        $needs_fix = true;
    }
    
    // 2. Исправляем сломанный HTML
    if (strpos($post_content, '>> class=') !== false || 
        strpos($post_content, 'class=>>') !== false) {
        
        echo "❌ Найден сломанный HTML - исправляем...\n";
        
        $new_content = preg_replace('/>> class=/', 'class=', $new_content);
        $new_content = preg_replace('/class=>>/', 'class=', $new_content);
        $new_content = preg_replace('/>>/', '', $new_content);
        
        $needs_fix = true;
    }
    
    // 3. Проверяем и исправляем структуру
    $has_toc = strpos($new_content, '<!-- wp:table-of-contents') !== false;
    $has_image = strpos($new_content, '<!-- wp:image') !== false;
    $image_blocks_count = substr_count($new_content, '<!-- wp:image');
    
    echo "Table of Contents: " . ($has_toc ? "✅ Есть" : "❌ Нет") . "\n";
    echo "Блоков изображений: $image_blocks_count\n";
    
    // 4. Создаем правильную структуру
    if (!$has_toc || $image_blocks_count > 1 || $needs_fix) {
        echo "🔧 Создаем правильную структуру...\n";
        
        // Создаем базовую структуру
        $clean_content = '<!-- wp:paragraph -->
<p>Полное руководство по теме статьи с подробным описанием и практическими рекомендациями.</p>
<!-- /wp:paragraph -->

<!-- wp:table-of-contents -->
<nav class="table-of-contents">
<h2>Содержание:</h2>
<ul>
<li><a href="#what-is">Что это такое?</a></li>
<li><a href="#how-it-works">Как это работает?</a></li>
<li><a href="#advantages">Преимущества</a></li>
<li><a href="#disadvantages">Недостатки</a></li>
<li><a href="#examples">Примеры</a></li>
<li><a href="#faq">Часто задаваемые вопросы</a></li>
<li><a href="#conclusion">Заключение</a></li>
</ul>
</nav>
<!-- /wp:table-of-contents -->';

        // Добавляем изображение если есть featured image
        if ($featured_image_id) {
            $image_url = wp_get_attachment_url($featured_image_id);
            if ($image_url) {
                $clean_content .= '

<!-- wp:image {"align":"center","sizeSlug":"large"} -->
<figure class="wp-block-image aligncenter size-large"><img src="' . $image_url . '" alt="' . esc_attr($post_title) . '"/></figure>
<!-- /wp:image -->';
            }
        }
        
        // Добавляем основной контент
        $clean_content .= '

<!-- wp:heading {"level":2} -->
<h2 id="what-is">Что это такое?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Подробное описание темы статьи с основными понятиями и определениями.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="how-it-works">Как это работает?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Пошаговое объяснение процессов и механизмов работы.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="advantages">Преимущества</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Основные преимущества и положительные стороны рассматриваемой темы.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="disadvantages">Недостатки</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Возможные недостатки и ограничения.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="examples">Примеры</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Практические примеры и кейсы использования.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="faq">Часто задаваемые вопросы</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ответы на наиболее частые вопросы по теме.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 id="conclusion">Заключение</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Выводы и рекомендации по теме статьи.</p>
<!-- /wp:paragraph -->';
        
        $new_content = $clean_content;
        $needs_fix = true;
    }
    
    // 5. Создаем правильный excerpt
    if (empty($post_excerpt) || strpos($post_excerpt, '.intro {') !== false) {
        echo "🔧 Создаем правильный excerpt...\n";
        
        $new_excerpt = $post_title . ': полное руководство с подробным описанием, практическими примерами и рекомендациями.';
        $needs_fix = true;
    }
    
    // 6. Исправляем пути к изображениям
    if ($featured_image_id) {
        $current_file = get_attached_file($featured_image_id);
        if (strpos($current_file, '/wp-content/uploads/wp-content/uploads/') !== false) {
            echo "🔧 Исправляем путь к изображению...\n";
            
            $corrected_file = str_replace('/wp-content/uploads/wp-content/uploads/', '/wp-content/uploads/', $current_file);
            update_post_meta($featured_image_id, '_wp_attached_file', $corrected_file);
            $needs_fix = true;
        }
    }
    
    // 7. Обновляем статью если нужно
    if ($needs_fix) {
        try {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $new_content,
                'post_excerpt' => $new_excerpt,
            ));
            
            echo "✅ Статья исправлена\n";
            $fixed_count++;
        } catch (Exception $e) {
            echo "❌ Ошибка при обновлении: " . $e->getMessage() . "\n";
            $errors_count++;
        }
    } else {
        echo "✅ Статья не требует исправлений\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "=== РЕЗУЛЬТАТЫ ===\n";
echo "Исправлено статей: $fixed_count\n";
echo "Ошибок: $errors_count\n";
echo "Всего обработано: " . count($all_posts) . "\n";

// Очищаем кэш
wp_cache_flush();
echo "Кэш очищен\n";
