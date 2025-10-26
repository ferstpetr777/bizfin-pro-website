<?php
// Подключаем WordPress
require_once(__DIR__ . '/wp-load.php');

// Получаем ID правильного футера
$footer_id = 479;

// Настройки Theme Builder для футера
$theme_builder_conditions = array(
    'footer' => array(
        $footer_id => array(
            'include' => array(
                'general' => array(
                    'entire' => array()
                )
            ),
            'exclude' => array()
        )
    )
);

// Сохраняем настройки Theme Builder
$result = update_option('elementor_pro_theme_builder_conditions', $theme_builder_conditions);

if ($result) {
    echo "✅ Настройки Theme Builder успешно обновлены!\n";
    echo "Футер ID $footer_id теперь будет отображаться на всех страницах.\n";
} else {
    echo "❌ Ошибка при обновлении настроек Theme Builder.\n";
}

// Очищаем кеш Elementor
if (function_exists('\\Elementor\\Plugin::$instance->files_manager->clear_cache')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    echo "✅ Кеш Elementor очищен.\n";
}

echo "\nГотово! Проверьте сайт.\n";
?>
