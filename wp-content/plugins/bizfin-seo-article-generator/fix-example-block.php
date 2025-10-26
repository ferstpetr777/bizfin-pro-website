<?php
/**
 * Исправление блока примера - изменение только стилей для .example
 * Не меняем весь код, только проблемный блок
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// Получаем текущий контент статьи
$post_id = 2986;
$post = get_post($post_id);

if (!$post) {
    die("Статья не найдена");
}

// Получаем контент
$content = $post->post_content;

// Находим и заменяем только стили для .example блока
$old_example_styles = '.example {
            background: #e8f4fd;
            border-left: 4px solid var(--blue);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }';

$new_example_styles = '.example {
            background: #f8f9fa;
            border-left: 4px solid var(--blue);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
            color: var(--text);
        }';

// Заменяем стили
$content = str_replace($old_example_styles, $new_example_styles, $content);

// Обновляем пост
$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => $content
]);

if (is_wp_error($result)) {
    die('Ошибка при обновлении: ' . $result->get_error_message());
}

echo "✅ Блок примера исправлен!\n";
echo "📝 ID поста: {$post_id}\n";
echo "🔗 URL: " . get_permalink($post_id) . "\n";
echo "🎨 Изменения:\n";
echo "- Фон блока: изменен с #e8f4fd на #f8f9fa\n";
echo "- Цвет текста: добавлен var(--text) для лучшего контраста\n";
echo "- Текст теперь хорошо виден на светлом фоне\n";
echo "\n📈 Статья обновлена и готова к просмотру!\n";
?>
