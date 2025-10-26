<?php
// Подключение стилей для статей
function enqueue_article_styles() {
    if (is_single() && get_post_type() == "post") {
        wp_enqueue_style("article-styles", get_template_directory_uri() . "/article-styles.css", array(), "1.0.0");
    }
}
add_action("wp_enqueue_scripts", "enqueue_article_styles");
?>