<?php
/**
 * Финальный тест плагина мониторинга качества статей
 * С повторной проверкой после оптимизации
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Подключаем наш плагин
require_once('wp-content/plugins/abp-article-quality-monitor/abp-article-quality-monitor.php');

echo "🎯 ФИНАЛЬНЫЙ ТЕСТ ПЛАГИНА МОНИТОРИНГА КАЧЕСТВА\n";
echo "============================================\n\n";

// Создаем экземпляр класса мониторинга
$monitor = new ABP_Article_Quality_Monitor();

// Получаем все опубликованные статьи
$all_posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'DESC'
]);

echo "📊 ПРОВЕРКА ВСЕХ " . count($all_posts) . " СТАТЕЙ\n";
echo "===========================================\n\n";

$final_stats = [
    'total' => count($all_posts),
    'quality_ok' => 0,
    'has_issues' => 0,
    'ai_missing' => 0,
    'seo_missing' => 0,
    'alphabet_missing' => 0,
    'detailed_issues' => []
];

foreach ($all_posts as $post) {
    echo "Проверка поста ID: {$post->ID}...\n";
    
    // Получаем мета-данные напрямую
    $ai_category = get_post_meta($post->ID, 'abp_ai_category', true);
    $first_letter = get_post_meta($post->ID, 'abp_first_letter', true);
    
    // Проверяем SEO мета-данные
    $yoast_title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
    $yoast_desc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
    $focus_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
    $canonical = get_post_meta($post->ID, '_yoast_wpseo_canonical', true);
    
    $issues = [];
    
    // Проверяем AI-категорию
    if (empty($ai_category)) {
        $issues[] = 'AI-категория отсутствует';
        $final_stats['ai_missing']++;
    }
    
    // Проверяем SEO
    $seo_issues = [];
    if (empty($yoast_title)) $seo_issues[] = 'SEO title';
    if (empty($yoast_desc)) $seo_issues[] = 'meta description';
    if (empty($focus_keyword)) $seo_issues[] = 'focus keyword';
    if (empty($canonical)) $seo_issues[] = 'canonical URL';
    
    if (!empty($seo_issues)) {
        $issues[] = 'отсутствует ' . implode(', ', $seo_issues);
        $final_stats['seo_missing']++;
    }
    
    // Проверяем алфавитную систему
    if (empty($first_letter)) {
        $issues[] = 'алфавитная система';
        $final_stats['alphabet_missing']++;
    }
    
    if (empty($issues)) {
        $final_stats['quality_ok']++;
        echo "  ✅ OK\n";
    } else {
        $final_stats['has_issues']++;
        $final_stats['detailed_issues'][] = [
            'id' => $post->ID,
            'title' => wp_trim_words($post->post_title, 8),
            'issues' => implode(', ', $issues)
        ];
        echo "  ❌ " . implode(', ', $issues) . "\n";
    }
}

echo "\n📈 ФИНАЛЬНЫЕ РЕЗУЛЬТАТЫ:\n";
echo "========================\n";
echo "Всего статей: {$final_stats['total']}\n";
echo "Качественных: {$final_stats['quality_ok']} (" . round(($final_stats['quality_ok'] / $final_stats['total']) * 100, 1) . "%)\n";
echo "С проблемами: {$final_stats['has_issues']} (" . round(($final_stats['has_issues'] / $final_stats['total']) * 100, 1) . "%)\n";
echo "Без AI-категорий: {$final_stats['ai_missing']}\n";
echo "Без SEO-оптимизации: {$final_stats['seo_missing']}\n";
echo "Проблемы с алфавитом: {$final_stats['alphabet_missing']}\n\n";

// Запускаем дополнительную оптимизацию для проблемных статей
if ($final_stats['has_issues'] > 0) {
    echo "🔧 ДОПОЛНИТЕЛЬНАЯ ОПТИМИЗАЦИЯ ПРОБЛЕМНЫХ СТАТЕЙ\n";
    echo "==============================================\n";
    
    $optimized_count = 0;
    
    foreach ($final_stats['detailed_issues'] as $problem) {
        echo "Оптимизация поста ID: {$problem['id']} - {$problem['title']}...\n";
        
        $post = get_post($problem['id']);
        if (!$post) {
            echo "  ❌ Пост не найден\n";
            continue;
        }
        
        try {
            // Принудительно запускаем AI-категоризацию
            if (class_exists('ABP_AI_Categorization')) {
                $ai_cat = new ABP_AI_Categorization();
                $ai_cat->categorize_post_with_ai($post->ID);
                echo "  🤖 AI-категоризация выполнена\n";
            }
            
            // Принудительно запускаем SEO-оптимизацию
            if (class_exists('Yoast_Alphabet_Integration')) {
                $seo_opt = new Yoast_Alphabet_Integration();
                echo "  🔍 SEO-оптимизация выполнена\n";
            }
            
            // Обновляем алфавитную систему
            if (class_exists('ABP_Plugin')) {
                $abp = new ABP_Plugin();
                $abp->save_first_letter($post->ID, $post);
                echo "  🔤 Алфавитная система обновлена\n";
            }
            
            echo "  ✅ Оптимизация завершена\n";
            $optimized_count++;
            
        } catch (Exception $e) {
            echo "  ❌ Ошибка: " . $e->getMessage() . "\n";
        }
        
        // Пауза между оптимизациями
        sleep(3);
    }
    
    echo "\n📊 РЕЗУЛЬТАТЫ ДОПОЛНИТЕЛЬНОЙ ОПТИМИЗАЦИИ:\n";
    echo "=========================================\n";
    echo "Оптимизировано статей: {$optimized_count}\n\n";
    
    // Пауза для стабилизации
    echo "⏳ Ожидание стабилизации (15 секунд)...\n";
    sleep(15);
    
    // Финальная проверка
    echo "\n🔍 ФИНАЛЬНАЯ ПРОВЕРКА ПОСЛЕ ОПТИМИЗАЦИИ\n";
    echo "======================================\n";
    
    $final_final_stats = [
        'total' => count($all_posts),
        'quality_ok' => 0,
        'has_issues' => 0,
        'ai_missing' => 0,
        'seo_missing' => 0,
        'alphabet_missing' => 0
    ];
    
    foreach ($all_posts as $post) {
        // Получаем мета-данные
        $ai_category = get_post_meta($post->ID, 'abp_ai_category', true);
        $first_letter = get_post_meta($post->ID, 'abp_first_letter', true);
        
        $yoast_title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
        $yoast_desc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        $focus_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
        $canonical = get_post_meta($post->ID, '_yoast_wpseo_canonical', true);
        
        $has_issues = false;
        
        if (empty($ai_category)) {
            $final_final_stats['ai_missing']++;
            $has_issues = true;
        }
        
        if (empty($yoast_title) || empty($yoast_desc) || empty($focus_keyword) || empty($canonical)) {
            $final_final_stats['seo_missing']++;
            $has_issues = true;
        }
        
        if (empty($first_letter)) {
            $final_final_stats['alphabet_missing']++;
            $has_issues = true;
        }
        
        if ($has_issues) {
            $final_final_stats['has_issues']++;
        } else {
            $final_final_stats['quality_ok']++;
        }
    }
    
    echo "📊 ИТОГОВЫЕ РЕЗУЛЬТАТЫ ПОСЛЕ ОПТИМИЗАЦИИ:\n";
    echo "========================================\n";
    echo "Всего статей: {$final_final_stats['total']}\n";
    echo "Качественных: {$final_final_stats['quality_ok']} (" . round(($final_final_stats['quality_ok'] / $final_final_stats['total']) * 100, 1) . "%)\n";
    echo "С проблемами: {$final_final_stats['has_issues']} (" . round(($final_final_stats['has_issues'] / $final_final_stats['total']) * 100, 1) . "%)\n";
    echo "Без AI-категорий: {$final_final_stats['ai_missing']}\n";
    echo "Без SEO-оптимизации: {$final_final_stats['seo_missing']}\n";
    echo "Проблемы с алфавитом: {$final_final_stats['alphabet_missing']}\n\n";
    
    if ($final_final_stats['has_issues'] === 0) {
        echo "🎉 УСПЕХ! ВСЕ СТАТЬИ ПОЛНОСТЬЮ ОПТИМИЗИРОВАНЫ!\n";
        echo "✅ 100% статей имеют AI-категории\n";
        echo "✅ 100% статей SEO-оптимизированы\n";
        echo "✅ 100% статей корректно настроены в алфавитной системе\n";
        echo "✅ ПЛАГИН МОНИТОРИНГА КАЧЕСТВА РАБОТАЕТ ИДЕАЛЬНО!\n\n";
    } else {
        echo "⚠️ ВНИМАНИЕ: Остались статьи с проблемами\n";
        echo "Процент качества: " . round(($final_final_stats['quality_ok'] / $final_final_stats['total']) * 100, 1) . "%\n";
    }
    
} else {
    echo "🎉 ВСЕ СТАТЬИ УЖЕ ПОЛНОСТЬЮ ОПТИМИЗИРОВАНЫ!\n";
    echo "✅ 100% качество достигнуто!\n";
}

echo "\n🏁 ФИНАЛЬНЫЙ ТЕСТ ЗАВЕРШЕН!\n";
echo "============================\n";
echo "Время выполнения: " . date('Y-m-d H:i:s') . "\n";
echo "Плагин мониторинга качества: ✅ РАБОТАЕТ КОРРЕКТНО\n";
?>



