<?php
/**
 * Комплексная оптимизация всех неоптимизированных статей
 * ABP Article Quality Monitor - Полная оптимизация
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Недостаточно прав для выполнения операции');
}

echo "=== КОМПЛЕКСНАЯ ОПТИМИЗАЦИЯ СТАТЕЙ ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

// Получаем все неоптимизированные статьи
global $wpdb;
$table_name = $wpdb->prefix . 'abp_quality_checks';

$unoptimized_posts = $wpdb->get_results("
    SELECT DISTINCT p.ID, p.post_title, q.overall_status, q.issues
    FROM {$wpdb->posts} p
    INNER JOIN (
        SELECT q1.* FROM $table_name q1
        INNER JOIN (
            SELECT post_id, MAX(id) as max_id
            FROM $table_name
            GROUP BY post_id
        ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
        WHERE q1.overall_status != 'ok'
    ) q ON p.ID = q.post_id
    WHERE p.post_type = 'post' AND p.post_status = 'publish'
    ORDER BY p.post_date DESC
");

echo "Найдено неоптимизированных статей: " . count($unoptimized_posts) . "\n\n";

$optimized_count = 0;
$error_count = 0;
$results = [];

foreach ($unoptimized_posts as $post_data) {
    $post_id = $post_data->ID;
    $post_title = $post_data->post_title;
    
    echo "Обрабатываем статью ID: $post_id - " . wp_trim_words($post_title, 8) . "\n";
    
    try {
        // Получаем объект поста
        $post = get_post($post_id);
        if (!$post) {
            echo "  ❌ Ошибка: пост не найден\n";
            $error_count++;
            continue;
        }
        
        // 1. AI-категоризация
        echo "  🔄 Запуск AI-категоризации...\n";
        if (class_exists('ABP_AI_Categorization')) {
            $ai_cat = new ABP_AI_Categorization();
            $ai_cat->categorize_post_with_ai($post_id);
            echo "  ✅ AI-категоризация завершена\n";
        } else {
            echo "  ⚠️ ABP_AI_Categorization не найден\n";
        }
        
        // 2. SEO-оптимизация
        echo "  🔄 Запуск SEO-оптимизации...\n";
        
        // Получаем или создаем focus keyword
        $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        if (empty($focus_keyword)) {
            $focus_keyword = $post->post_title;
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
            echo "  📝 Создан focus keyword: $focus_keyword\n";
        }
        
        // Создаем SEO title если отсутствует
        $seo_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
        if (empty($seo_title)) {
            $seo_title = $post->post_title . ' | BizFin Pro';
            update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
            echo "  📝 Создан SEO title: $seo_title\n";
        }
        
        // Создаем meta description если отсутствует
        $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        if (empty($meta_desc)) {
            $meta_desc = $focus_keyword . ' - подробное руководство, условия получения, документы и процедуры. Профессиональные консультации по банковским гарантиям.';
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
            echo "  📝 Создана meta description\n";
        }
        
        // Добавляем canonical URL если отсутствует
        $canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
        if (empty($canonical)) {
            $canonical_url = get_permalink($post_id);
            update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical_url);
            echo "  📝 Добавлен canonical URL\n";
        }
        
        // Запускаем YoastAlphabetIntegration если доступен
        if (class_exists('YoastAlphabetIntegration')) {
            $seo_opt = new YoastAlphabetIntegration();
            $seo_opt->optimize_post_for_yoast($post_id, $focus_keyword);
            echo "  ✅ SEO-оптимизация через YoastAlphabetIntegration завершена\n";
        } else {
            echo "  ⚠️ YoastAlphabetIntegration не найден, используем базовую оптимизацию\n";
        }
        
        // 3. Алфавитная система
        echo "  🔄 Настройка алфавитной системы...\n";
        $first_letter_meta = get_post_meta($post_id, 'abp_first_letter', true);
        if (empty($first_letter_meta)) {
            $title = $post->post_title;
            $clean_title = preg_replace('/<[^>]+>/', '', $title);
            $clean_title = trim($clean_title);
            
            if (!empty($clean_title)) {
                $first_char = mb_strtoupper(mb_substr($clean_title, 0, 1, 'UTF-8'), 'UTF-8');
                if ($first_char === 'Ё') $first_char = 'Ё';
                elseif ($first_char === 'Е') $first_char = 'Е';
                
                update_post_meta($post_id, 'abp_first_letter', $first_char);
                echo "  📝 Установлена первая буква: $first_char\n";
            }
        }
        
        // Запускаем ABP_Plugin если доступен
        if (class_exists('ABP_Plugin')) {
            $abp = new ABP_Plugin();
            $abp->save_first_letter($post_id, $post);
            echo "  ✅ Алфавитная система настроена\n";
        }
        
        // 4. Принудительная проверка качества
        echo "  🔄 Запуск проверки качества...\n";
        if (class_exists('ABP_Article_Quality_Monitor')) {
            $quality_monitor = new ABP_Article_Quality_Monitor();
            $quality_monitor->check_post_quality($post_id, $post);
            echo "  ✅ Проверка качества завершена\n";
        }
        
        $optimized_count++;
        echo "  ✅ Статья ID $post_id успешно оптимизирована\n\n";
        
        $results[] = [
            'post_id' => $post_id,
            'title' => $post_title,
            'status' => 'optimized'
        ];
        
        // Небольшая задержка между статьями
        usleep(500000); // 0.5 секунды
        
    } catch (Exception $e) {
        echo "  ❌ Ошибка при оптимизации статьи ID $post_id: " . $e->getMessage() . "\n\n";
        $error_count++;
        $results[] = [
            'post_id' => $post_id,
            'title' => $post_title,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

echo "=== РЕЗУЛЬТАТЫ ОПТИМИЗАЦИИ ===\n";
echo "Всего обработано: " . count($unoptimized_posts) . "\n";
echo "Успешно оптимизировано: $optimized_count\n";
echo "Ошибок: $error_count\n";
echo "Завершено: " . date('Y-m-d H:i:s') . "\n\n";

// Финальная проверка результатов
echo "=== ФИНАЛЬНАЯ ПРОВЕРКА ===\n";
$final_check = $wpdb->get_results("
    SELECT overall_status, COUNT(*) as count
    FROM $table_name q1
    INNER JOIN (
        SELECT post_id, MAX(id) as max_id
        FROM $table_name
        GROUP BY post_id
    ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
    GROUP BY overall_status
");

foreach ($final_check as $check) {
    echo "Статус '{$check->overall_status}': {$check->count} статей\n";
}

$total_optimized = $wpdb->get_var("
    SELECT COUNT(*) FROM $table_name q1
    INNER JOIN (
        SELECT post_id, MAX(id) as max_id
        FROM $table_name
        GROUP BY post_id
    ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
    WHERE q1.overall_status = 'ok'
");

$total_posts = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts} 
    WHERE post_type = 'post' AND post_status = 'publish'
");

$optimization_percentage = $total_posts > 0 ? round(($total_optimized / $total_posts) * 100, 1) : 0;

echo "\nИТОГОВАЯ СТАТИСТИКА:\n";
echo "Всего статей: $total_posts\n";
echo "Полностью оптимизировано: $total_optimized\n";
echo "Процент оптимизации: $optimization_percentage%\n";

if ($optimization_percentage == 100) {
    echo "🎉 ВСЕ СТАТЬИ ПОЛНОСТЬЮ ОПТИМИЗИРОВАНЫ!\n";
} else {
    echo "⚠️ Требуется дополнительная работа для достижения 100%\n";
}

echo "\n=== ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ ===\n";
foreach ($results as $result) {
    $status_icon = $result['status'] === 'optimized' ? '✅' : '❌';
    echo "$status_icon ID {$result['post_id']}: " . wp_trim_words($result['title'], 6);
    if (isset($result['error'])) {
        echo " - Ошибка: {$result['error']}";
    }
    echo "\n";
}
?>

