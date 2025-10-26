<?php
/**
 * Создание тестовой страницы с шорткодом
 * Company Rating Checker - Test Shortcode Page
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

echo "🔍 СОЗДАНИЕ ТЕСТОВОЙ СТРАНИЦЫ С ШОРТКОДОМ\n";
echo "==========================================\n\n";

// Проверяем, есть ли уже страница с шорткодом
$existing_page = get_page_by_path('company-rating-test');

if ($existing_page) {
    echo "✅ Тестовая страница уже существует: ID {$existing_page->ID}\n";
    echo "🔗 URL: " . get_permalink($existing_page->ID) . "\n\n";
} else {
    // Создаем новую страницу
    $page_data = array(
        'post_title' => 'Тест рейтинга компании',
        'post_content' => '
<h2>🔍 Проверка рейтинга компании</h2>
<p>Введите ИНН компании для получения комплексного анализа:</p>

[company_rating_checker]

<h3>📊 Описание системы рейтинга:</h3>
<p>Наша система анализирует компанию по следующим критериям:</p>
<ul>
<li><strong>Базовые данные</strong> - информация из DaData API</li>
<li><strong>ЕГРЮЛ данные</strong> - официальная информация из реестра</li>
<li><strong>МСП статус</strong> - категория предприятия</li>
<li><strong>Арбитражные риски</strong> - судебные разбирательства</li>
<li><strong>Государственные закупки</strong> - репутация поставщика</li>
<li><strong>ФНС данные</strong> - финансовые показатели</li>
<li><strong>Росстат данные</strong> - статистическая информация</li>
<li><strong>ЕФРСБ данные</strong> - процедуры банкротства</li>
<li><strong>РНП данные</strong> - недобросовестные поставщики</li>
<li><strong>ФССП данные</strong> - исполнительные производства</li>
</ul>

<p><strong>Максимальный балл:</strong> 195 баллов</p>
<p><strong>Количество факторов:</strong> 15</p>
        ',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'company-rating-test'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        echo "✅ Тестовая страница создана успешно: ID {$page_id}\n";
        echo "🔗 URL: " . get_permalink($page_id) . "\n\n";
    } else {
        echo "❌ Ошибка создания страницы\n";
        if (is_wp_error($page_id)) {
            echo "   " . $page_id->get_error_message() . "\n";
        }
    }
}

// Проверяем, зарегистрирован ли шорткод
echo "🔧 ПРОВЕРКА РЕГИСТРАЦИИ ШОРТКОДА:\n";
echo "==================================\n";

if (shortcode_exists('company_rating_checker')) {
    echo "✅ Шорткод [company_rating_checker] зарегистрирован\n";
} else {
    echo "❌ Шорткод [company_rating_checker] НЕ зарегистрирован\n";
}

// Проверяем глобальные шорткоды
global $shortcode_tags;
echo "\n📋 Доступные шорткоды:\n";
foreach ($shortcode_tags as $tag => $function) {
    if (strpos($tag, 'company') !== false || strpos($tag, 'rating') !== false) {
        echo "   ✅ [{$tag}] - " . (is_array($function) ? get_class($function[0]) . '::' . $function[1] : $function) . "\n";
    }
}

echo "\n⏰ Время завершения: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 СОЗДАНИЕ ТЕСТОВОЙ СТРАНИЦЫ ЗАВЕРШЕНО!\n";
echo "========================================\n";
?>
