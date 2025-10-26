<?php
/**
 * Кастомный футер для всех страниц
 */

// Получаем правильный футер с главной страницы
$footer_post = get_post(479);
if ($footer_post && $footer_post->post_type === 'elementor-hf') {
    // Получаем данные Elementor для футера
    $elementor_data = get_post_meta(479, '_elementor_data', true);
    
    if ($elementor_data) {
        // Рендерим Elementor контент
        echo '<div class="custom-elementor-footer">';
        echo do_shortcode('[elementor-template id="479"]');
        echo '</div>';
    }
}
?>

