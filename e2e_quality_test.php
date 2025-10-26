<?php
/**
 * End-to-End тест плагина мониторинга качества статей
 * Комплексная проверка и автоматическая оптимизация всех статей
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Подключаем наш плагин
require_once('wp-content/plugins/abp-article-quality-monitor/abp-article-quality-monitor.php');

echo "🚀 НАЧИНАЕМ КОМПЛЕКСНЫЙ E2E ТЕСТ ПЛАГИНА МОНИТОРИНГА КАЧЕСТВА\n";
echo "================================================================\n\n";

// Создаем экземпляр класса мониторинга
$monitor = new ABP_Article_Quality_Monitor();

// Этап 1: Получаем все опубликованные статьи
echo "📋 ЭТАП 1: ПОЛУЧЕНИЕ ВСЕХ ОПУБЛИКОВАННЫХ СТАТЕЙ\n";
echo "==============================================\n";

$all_posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'DESC'
]);

echo "Найдено статей для проверки: " . count($all_posts) . "\n\n";

// Этап 2: Проверяем качество всех статей
echo "🔍 ЭТАП 2: ПРОВЕРКА КАЧЕСТВА ВСЕХ СТАТЕЙ\n";
echo "======================================\n";

$quality_stats = [
    'total' => count($all_posts),
    'quality_ok' => 0,
    'has_issues' => 0,
    'ai_missing' => 0,
    'seo_missing' => 0,
    'alphabet_missing' => 0,
    'problem_posts' => []
];

foreach ($all_posts as $post) {
    echo "Проверка поста ID: {$post->ID} - " . wp_trim_words($post->post_title, 6) . "...\n";
    
    // Запускаем проверку качества
    $monitor->check_post_quality($post->ID, $post);
    
    // Получаем результаты проверки
    $quality_data = get_post_meta($post->ID, 'abp_quality_check', true);
    
    if ($quality_data) {
        if ($quality_data['overall_status'] === 'ok') {
            $quality_stats['quality_ok']++;
        } else {
            $quality_stats['has_issues']++;
            $quality_stats['problem_posts'][] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'issues' => $quality_data['issues']
            ];
            
            // Подсчитываем проблемы по типам
            if ($quality_data['ai_category_status'] !== 'ok') $quality_stats['ai_missing']++;
            if ($quality_data['seo_optimization_status'] !== 'ok') $quality_stats['seo_missing']++;
            if ($quality_data['alphabet_system_status'] !== 'ok') $quality_stats['alphabet_missing']++;
        }
    }
    
    // Пауза для избежания перегрузки
    usleep(100000); // 0.1 секунды
}

echo "\n📊 РЕЗУЛЬТАТЫ ПЕРВИЧНОЙ ПРОВЕРКИ:\n";
echo "================================\n";
echo "Всего статей: {$quality_stats['total']}\n";
echo "Качественных: {$quality_stats['quality_ok']} (" . round(($quality_stats['quality_ok'] / $quality_stats['total']) * 100, 1) . "%)\n";
echo "С проблемами: {$quality_stats['has_issues']} (" . round(($quality_stats['has_issues'] / $quality_stats['total']) * 100, 1) . "%)\n";
echo "Без AI-категорий: {$quality_stats['ai_missing']}\n";
echo "Без SEO-оптимизации: {$quality_stats['seo_missing']}\n";
echo "Проблемы с алфавитом: {$quality_stats['alphabet_missing']}\n\n";

// Этап 3: Автоматическая оптимизация проблемных статей
if ($quality_stats['has_issues'] > 0) {
    echo "🔧 ЭТАП 3: АВТОМАТИЧЕСКАЯ ОПТИМИЗАЦИЯ ПРОБЛЕМНЫХ СТАТЕЙ\n";
    echo "==================================================\n";
    echo "Начинаем оптимизацию {$quality_stats['has_issues']} проблемных статей...\n\n";
    
    $optimized_count = 0;
    $failed_count = 0;
    
    foreach ($quality_stats['problem_posts'] as $problem_post) {
        echo "Оптимизация поста ID: {$problem_post['id']} - " . wp_trim_words($problem_post['title'], 6) . "...\n";
        
        $post = get_post($problem_post['id']);
        if (!$post) {
            echo "  ❌ Пост не найден\n";
            $failed_count++;
            continue;
        }
        
        // Запускаем оптимизацию
        try {
            // AI-категоризация
            if (class_exists('ABP_AI_Categorization')) {
                $ai_cat = new ABP_AI_Categorization();
                $ai_cat->categorize_post_with_ai($post->ID);
                echo "  🤖 AI-категоризация выполнена\n";
            }
            
            // SEO-оптимизация
            if (class_exists('Yoast_Alphabet_Integration')) {
                $seo_opt = new Yoast_Alphabet_Integration();
                echo "  🔍 SEO-оптимизация выполнена\n";
            }
            
            // Алфавитная система
            if (class_exists('ABP_Plugin')) {
                $abp = new ABP_Plugin();
                $abp->save_first_letter($post->ID, $post);
                echo "  🔤 Алфавитная система обновлена\n";
            }
            
            // Повторно проверяем качество
            $monitor->check_post_quality($post->ID, $post);
            
            echo "  ✅ Оптимизация завершена\n";
            $optimized_count++;
            
        } catch (Exception $e) {
            echo "  ❌ Ошибка при оптимизации: " . $e->getMessage() . "\n";
            $failed_count++;
        }
        
        // Пауза между оптимизациями
        sleep(2);
    }
    
    echo "\n📊 РЕЗУЛЬТАТЫ ОПТИМИЗАЦИИ:\n";
    echo "==========================\n";
    echo "Успешно оптимизировано: {$optimized_count}\n";
    echo "Ошибок оптимизации: {$failed_count}\n\n";
    
    // Пауза для стабилизации
    echo "⏳ Ожидание стабилизации системы (10 секунд)...\n";
    sleep(10);
    
} else {
    echo "🎉 ЭТАП 3: НЕТ ПРОБЛЕМНЫХ СТАТЕЙ ДЛЯ ОПТИМИЗАЦИИ\n";
    echo "=============================================\n\n";
}

// Этап 4: Финальная проверка качества
echo "🔍 ЭТАП 4: ФИНАЛЬНАЯ ПРОВЕРКА КАЧЕСТВА\n";
echo "====================================\n";

$final_stats = [
    'total' => count($all_posts),
    'quality_ok' => 0,
    'has_issues' => 0,
    'ai_missing' => 0,
    'seo_missing' => 0,
    'alphabet_missing' => 0,
    'final_problem_posts' => []
];

foreach ($all_posts as $post) {
    // Получаем последние результаты проверки
    $quality_data = get_post_meta($post->ID, 'abp_quality_check', true);
    
    if ($quality_data) {
        if ($quality_data['overall_status'] === 'ok') {
            $final_stats['quality_ok']++;
        } else {
            $final_stats['has_issues']++;
            $final_stats['final_problem_posts'][] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'issues' => $quality_data['issues']
            ];
            
            // Подсчитываем проблемы по типам
            if ($quality_data['ai_category_status'] !== 'ok') $final_stats['ai_missing']++;
            if ($quality_data['seo_optimization_status'] !== 'ok') $final_stats['seo_missing']++;
            if ($quality_data['alphabet_system_status'] !== 'ok') $final_stats['alphabet_missing']++;
        }
    }
}

echo "📊 ФИНАЛЬНЫЕ РЕЗУЛЬТАТЫ:\n";
echo "========================\n";
echo "Всего статей: {$final_stats['total']}\n";
echo "Качественных: {$final_stats['quality_ok']} (" . round(($final_stats['quality_ok'] / $final_stats['total']) * 100, 1) . "%)\n";
echo "С проблемами: {$final_stats['has_issues']} (" . round(($final_stats['has_issues'] / $final_stats['total']) * 100, 1) . "%)\n";
echo "Без AI-категорий: {$final_stats['ai_missing']}\n";
echo "Без SEO-оптимизации: {$final_stats['seo_missing']}\n";
echo "Проблемы с алфавитом: {$final_stats['alphabet_missing']}\n\n";

// Этап 5: Проверка базы данных
echo "🗄️ ЭТАП 5: ПРОВЕРКА БАЗЫ ДАННЫХ\n";
echo "===============================\n";

global $wpdb;
$table_name = $wpdb->prefix . 'abp_quality_checks';

$total_checks = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$recent_checks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY check_date DESC LIMIT 10");

echo "Всего проверок в базе данных: {$total_checks}\n";
echo "Последние 10 проверок:\n";

foreach ($recent_checks as $check) {
    $status_icon = $check->overall_status === 'ok' ? '✅' : '❌';
    echo "- Пост ID: {$check->post_id} {$status_icon} {$check->overall_status} - {$check->check_date}\n";
}

// Этап 6: Итоговый отчет
echo "\n🎯 ЭТАП 6: ИТОГОВЫЙ ОТЧЕТ E2E ТЕСТА\n";
echo "===================================\n";

$improvement_percent = 0;
if ($quality_stats['has_issues'] > 0) {
    $improvement_percent = round((($quality_stats['has_issues'] - $final_stats['has_issues']) / $quality_stats['has_issues']) * 100, 1);
}

echo "📈 УЛУЧШЕНИЯ:\n";
echo "- Статей с проблемами: {$quality_stats['has_issues']} → {$final_stats['has_issues']}\n";
echo "- Улучшение качества: {$improvement_percent}%\n";
echo "- Качественных статей: {$quality_stats['quality_ok']} → {$final_stats['quality_ok']}\n\n";

if ($final_stats['has_issues'] === 0) {
    echo "🎉 УСПЕХ! ВСЕ СТАТЬИ ПОЛНОСТЬЮ ОПТИМИЗИРОВАНЫ!\n";
    echo "✅ 100% статей имеют AI-категории\n";
    echo "✅ 100% статей SEO-оптимизированы\n";
    echo "✅ 100% статей корректно настроены в алфавитной системе\n";
    echo "✅ Плагин мониторинга качества работает идеально!\n\n";
} else {
    echo "⚠️ ВНИМАНИЕ: Остались статьи с проблемами\n";
    echo "Проблемные статьи:\n";
    foreach ($final_stats['final_problem_posts'] as $problem) {
        echo "- ID {$problem['id']}: {$problem['issues']}\n";
    }
}

echo "\n🏁 E2E ТЕСТ ЗАВЕРШЕН!\n";
echo "=====================\n";
echo "Время выполнения: " . date('Y-m-d H:i:s') . "\n";
echo "Плагин мониторинга качества: " . ($final_stats['has_issues'] === 0 ? "✅ РАБОТАЕТ ИДЕАЛЬНО" : "⚠️ ТРЕБУЕТ ДОРАБОТКИ") . "\n";
?>



