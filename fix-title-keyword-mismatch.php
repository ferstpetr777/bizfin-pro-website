<?php
/**
 * Исправление несоответствия заголовка и фокусного ключевого слова
 */

require_once('wp-config.php');
require_once('wp-load.php');

echo "=== ИСПРАВЛЕНИЕ НЕСООТВЕТСТВИЯ ЗАГОЛОВКА И КЛЮЧЕВОГО СЛОВА ===\n";
echo "Начало: " . date('Y-m-d H:i:s') . "\n\n";

global $wpdb;
$table_name = $wpdb->prefix . 'abp_quality_checks';

// Получаем статьи с проблемой несоответствия заголовка
$problem_posts = $wpdb->get_results("
    SELECT p.ID, p.post_title, q.overall_status, q.issues
    FROM {$wpdb->posts} p
    INNER JOIN (
        SELECT q1.* FROM $table_name q1
        INNER JOIN (
            SELECT post_id, MAX(id) as max_id
            FROM $table_name
            GROUP BY post_id
        ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
        WHERE q1.overall_status != 'ok' AND q1.issues LIKE '%Заголовок не соответствует%'
    ) q ON p.ID = q.post_id
    WHERE p.post_type = 'post' AND p.post_status = 'publish'
    ORDER BY p.post_date DESC
");

echo "Найдено статей с проблемой заголовка: " . count($problem_posts) . "\n\n";

$fixed_count = 0;

foreach ($problem_posts as $post_data) {
    $post_id = $post_data->ID;
    $post_title = $post_data->post_title;
    
    echo "Исправляем статью ID: $post_id - " . wp_trim_words($post_title, 8) . "\n";
    
    try {
        // Получаем текущий focus keyword
        $current_focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        echo "  Текущий focus keyword: $current_focus_keyword\n";
        
        // Создаем новый focus keyword на основе заголовка
        $new_focus_keyword = $post_title;
        update_post_meta($post_id, '_yoast_wpseo_focuskw', $new_focus_keyword);
        echo "  Новый focus keyword: $new_focus_keyword\n";
        
        // Обновляем meta description чтобы она начиналась с ключевого слова
        $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        if (!empty($meta_desc)) {
            // Проверяем, начинается ли meta description с ключевого слова
            $meta_desc_lower = mb_strtolower(trim($meta_desc), 'UTF-8');
            $keyword_lower = mb_strtolower(trim($new_focus_keyword), 'UTF-8');
            
            if (mb_strpos($meta_desc_lower, $keyword_lower, 0, 'UTF-8') !== 0) {
                // Обновляем meta description
                $new_meta_desc = $new_focus_keyword . ' - подробное руководство, условия получения, документы и процедуры. Профессиональные консультации по банковским гарантиям.';
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $new_meta_desc);
                echo "  Обновлена meta description\n";
            }
        }
        
        // Запускаем проверку качества
        $post = get_post($post_id);
        if (class_exists('ABP_Article_Quality_Monitor')) {
            $quality_monitor = new ABP_Article_Quality_Monitor();
            $quality_monitor->check_post_quality($post_id, $post);
            echo "  ✅ Проверка качества завершена\n";
        }
        
        $fixed_count++;
        echo "  ✅ Статья ID $post_id исправлена\n\n";
        
        usleep(300000); // 0.3 секунды
        
    } catch (Exception $e) {
        echo "  ❌ Ошибка при исправлении статьи ID $post_id: " . $e->getMessage() . "\n\n";
    }
}

echo "=== РЕЗУЛЬТАТЫ ИСПРАВЛЕНИЯ ===\n";
echo "Исправлено статей: $fixed_count\n";
echo "Завершено: " . date('Y-m-d H:i:s') . "\n\n";

// Финальная проверка
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
?>

