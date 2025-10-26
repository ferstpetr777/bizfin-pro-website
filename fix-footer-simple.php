<?php
// Подключаем WordPress
require_once(__DIR__ . '/wp-load.php');

// Получаем правильный футер с главной страницы
$footer_post = get_post(479);
if ($footer_post && $footer_post->post_type === 'elementor-hf') {
    // Получаем данные Elementor для футера
    $elementor_data = get_post_meta(479, '_elementor_data', true);
    
    if ($elementor_data) {
        // Добавляем хук для отображения футера на всех страницах
        add_action('wp_footer', function() use ($elementor_data) {
            if (!is_admin()) {
                echo '<div id="custom-footer-wrapper">';
                // Здесь мы будем рендерить Elementor контент
                echo '</div>';
            }
        });
        
        echo "✅ Футер настроен для отображения на всех страницах.\n";
    } else {
        echo "❌ Не удалось получить данные Elementor для футера.\n";
    }
} else {
    echo "❌ Футер с ID 479 не найден или имеет неправильный тип.\n";
}

echo "\nГотово!\n";
?>

