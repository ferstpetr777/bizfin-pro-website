<?php
/**
 * Тестовый скрипт для проверки плагина мониторинга качества статей
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Подключаем наш плагин
require_once('wp-content/plugins/abp-article-quality-monitor/abp-article-quality-monitor.php');

echo "=== ТЕСТИРОВАНИЕ ПЛАГИНА МОНИТОРИНГА КАЧЕСТВА СТАТЕЙ ===\n\n";

// Получаем несколько постов для тестирования
$posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 5,
    'orderby' => 'ID',
    'order' => 'DESC'
]);

echo "Найдено постов для тестирования: " . count($posts) . "\n\n";

foreach ($posts as $post) {
    echo "--- Тестирование поста ID: {$post->ID} ---\n";
    echo "Заголовок: " . wp_trim_words($post->post_title, 10) . "\n";
    
    // Создаем экземпляр класса мониторинга
    $monitor = new ABP_Article_Quality_Monitor();
    
    // Вызываем метод проверки качества
    $monitor->check_post_quality($post->ID, $post);
    
    // Получаем результаты проверки
    $quality_data = get_post_meta($post->ID, 'abp_quality_check', true);
    
    if ($quality_data) {
        echo "AI-категория: " . ($quality_data['ai_category_status'] === 'ok' ? '✅' : '❌') . "\n";
        echo "SEO-оптимизация: " . ($quality_data['seo_optimization_status'] === 'ok' ? '✅' : '❌') . "\n";
        echo "Алфавитная система: " . ($quality_data['alphabet_system_status'] === 'ok' ? '✅' : '❌') . "\n";
        echo "Общий статус: " . ($quality_data['overall_status'] === 'ok' ? '✅ OK' : '❌ ISSUES') . "\n";
        
        if (!empty($quality_data['issues'])) {
            echo "Проблемы: " . $quality_data['issues'] . "\n";
        }
    } else {
        echo "❌ Данные о качестве не найдены\n";
    }
    
    echo "\n";
}

// Проверяем таблицу в базе данных
global $wpdb;
$table_name = $wpdb->prefix . 'abp_quality_checks';

$checks_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
echo "=== СТАТИСТИКА БАЗЫ ДАННЫХ ===\n";
echo "Всего проверок в базе данных: $checks_count\n";

$recent_checks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY check_date DESC LIMIT 5");
echo "Последние 5 проверок:\n";

foreach ($recent_checks as $check) {
    echo "- Пост ID: {$check->post_id}, Статус: {$check->overall_status}, Дата: {$check->check_date}\n";
}

echo "\n=== ТЕСТИРОВАНИЕ ЗАВЕРШЕНО ===\n";
?>



