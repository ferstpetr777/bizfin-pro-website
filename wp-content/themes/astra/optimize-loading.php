
<?php
/**
 * Функция для отложенной загрузки скриптов
 * Добавить в functions.php темы
 */

// Откладываем загрузку некритических скриптов
function defer_non_critical_scripts($tag, $handle, $src) {
    // Список скриптов для отложенной загрузки
    $defer_scripts = array(
        'wp-yandex-metrika_YmEc-js',
        'wp-yandex-metrika_frontend-js',
        'wp-yandex-metrika_wpforms-js',
        'wp-yandex-metrika_elementor-js',
        'elementor-frontend',
        'elementor-pro-frontend',
        'wpforms',
        'contact-form-7'
    );
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'defer_non_critical_scripts', 10, 3);

// Откладываем загрузку некритических CSS
function defer_non_critical_css($tag, $handle, $href, $media) {
    // Список CSS для отложенной загрузки
    $defer_css = array(
        'elementor-frontend',
        'elementor-pro-frontend',
        'wpforms',
        'contact-form-7'
    );
    
    if (in_array($handle, $defer_css)) {
        return str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'"', $tag) . '<noscript>' . $tag . '</noscript>';
    }
    
    return $tag;
}
add_filter('style_loader_tag', 'defer_non_critical_css', 10, 4);

// Оптимизируем загрузку шрифтов
function optimize_font_loading() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">';
    echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap"></noscript>';
}
add_action('wp_head', 'optimize_font_loading', 1);

// Добавляем критический CSS inline
function add_critical_css() {
    if (is_single() || is_page()) {
        echo '<style>
        /* Критический CSS для статей */
        .entry-content { font-size: 1.1rem; line-height: 1.8; }
        .entry-content h1, .entry-content h2, .entry-content h3 { 
            color: #1a1a1a; margin: 30px 0 15px 0; font-weight: 600; 
        }
        .entry-content img { max-width: 100%; height: auto; margin: 20px 0; }
        .entry-content p { margin-bottom: 20px; }
        </style>';
    }
}
add_action('wp_head', 'add_critical_css', 2);

// Откладываем инициализацию Yandex Metrika
function defer_yandex_metrika() {
    echo '<script>
    window.addEventListener("load", function() {
        setTimeout(function() {
            if (typeof ym !== "undefined") {
                ym(104811275, "init", {
                    clickmap: true,
                    trackLinks: true,
                    accurateTrackBounce: true,
                    webvisor: true
                });
            }
        }, 2000);
    });
    </script>';
}
add_action('wp_footer', 'defer_yandex_metrika');

// Оптимизируем изображения (lazy loading)
function add_lazy_loading_to_images($content) {
    if (is_single() || is_page()) {
        // Добавляем lazy loading к изображениям
        $content = preg_replace('/<img(.*?)src=/i', '<img$1data-src=', $content);
        $content = preg_replace('/<img(.*?)class="/i', '<img$1class="lazy "', $content);
        
        // Добавляем скрипт для lazy loading
        $content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const images = document.querySelectorAll("img[data-src]");
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove("lazy");
                        observer.unobserve(img);
                    }
                });
            });
            images.forEach(img => imageObserver.observe(img));
        });
        </script>';
    }
    return $content;
}
add_filter('the_content', 'add_lazy_loading_to_images');
?>