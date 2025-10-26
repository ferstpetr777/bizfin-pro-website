<?php
// Подключаем WordPress
require_once(__DIR__ . '/wp-load.php');

// Получаем текущие настройки Astra
$astra_settings = get_option('astra-settings', array());

// Очищаем элементы футера
$astra_settings['footer-desktop-items'] = array(
    'above' => array(
        'above_1' => array(),
        'above_2' => array(),
        'above_3' => array(),
        'above_4' => array(),
        'above_5' => array()
    ),
    'primary' => array(
        'primary_1' => array(),
        'primary_2' => array(),
        'primary_3' => array(),
        'primary_4' => array(),
        'primary_5' => array()
    ),
    'below' => array(
        'below_1' => array(),
        'below_2' => array(),
        'below_3' => array(),
        'below_4' => array(),
        'below_5' => array()
    )
);

// Сохраняем настройки
$result = update_option('astra-settings', $astra_settings);

if ($result) {
    echo "✓ Футер Astra успешно отключен!\n";
    echo "Теперь на всех страницах будет отображаться кастомный футер Elementor.\n";
} else {
    echo "✗ Ошибка при обновлении настроек.\n";
}
?>

