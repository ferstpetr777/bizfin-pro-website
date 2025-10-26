<?php
require_once('wp-load.php');

if (!defined('ABSPATH')) {
    exit;
}

echo "=== НАСТРОЙКА FEATURED IMAGE ===\n\n";

// Получаем текущие настройки темы
$astra_settings = get_option('astra-settings', array());

echo "Текущие настройки featured image:\n";
echo "Layout 1: " . ($astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-1'] ?? 'не установлено') . "\n";
echo "Layout 2: " . ($astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-2'] ?? 'не установлено') . "\n";

// Устанавливаем правильные настройки
$astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-1'] = 'behind';
$astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-2'] = 'behind';

// Включаем featured image для постов
$astra_settings['ast-dynamic-single-post-article-featured-image-size'] = 'large';

// Сохраняем настройки
update_option('astra-settings', $astra_settings);

echo "\nНовые настройки:\n";
echo "Layout 1: " . $astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-1'] . "\n";
echo "Layout 2: " . $astra_settings['ast-dynamic-single-post-article-featured-image-position-layout-2'] . "\n";
echo "Размер: " . $astra_settings['ast-dynamic-single-post-article-featured-image-size'] . "\n";

echo "\n✅ Настройки featured image обновлены!\n";
echo "Теперь featured image должен отображаться в постах.\n";

echo "\n=== ПРОВЕРКА НАСТРОЕК ===\n";

// Проверим, что настройки применились
$updated_settings = get_option('astra-settings', array());
$layout1 = $updated_settings['ast-dynamic-single-post-article-featured-image-position-layout-1'] ?? 'none';
$layout2 = $updated_settings['ast-dynamic-single-post-article-featured-image-position-layout-2'] ?? 'none';

if ($layout1 === 'behind' && $layout2 === 'behind') {
    echo "✅ Настройки применены успешно!\n";
} else {
    echo "❌ Настройки не применились!\n";
}

echo "\n=== НАСТРОЙКА ЗАВЕРШЕНА ===\n";
?>

