<?php
/**
 * Подключение шрифтов в head
 */

// Подключаем шрифты Google Fonts в head
function add_google_fonts_to_head() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">' . "\n";
}
add_action('wp_head', 'add_google_fonts_to_head', 1);

// Добавляем CSS переменные для шрифтов
function add_font_css_variables() {
    echo '<style>
        :root {
            --font-primary: "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif;
            --font-heading: "Poppins", "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Принудительное применение шрифтов */
        body, p, div, span, a, button, input, textarea, select {
            font-family: var(--font-primary) !important;
        }
        
        h1, h2, h3, h4, h5, h6, 
        .elementor-heading-title,
        .widget-title,
        .site-title,
        .entry-title {
            font-family: var(--font-heading) !important;
        }
        
        h1, h2 {
            font-weight: 700 !important;
        }
        
        h3, h4, h5, h6 {
            font-weight: 600 !important;
        }
    </style>' . "\n";
}
add_action('wp_head', 'add_font_css_variables', 2);
?>
