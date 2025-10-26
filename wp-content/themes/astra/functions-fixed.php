<?php
/**
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '4.11.10' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );
define( 'ASTRA_THEME_ORG_VERSION', file_exists( ASTRA_THEME_DIR . 'inc/w-org-version.php' ) );

/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '4.11.6' );

/**
 * Load in-house compatibility.
 */
if ( ASTRA_THEME_ORG_VERSION ) {
	require_once ASTRA_THEME_DIR . 'inc/w-org-version.php';
}

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

define( 'ASTRA_WEBSITE_BASE_URL', 'https://wpastra.com' );

/**
 * ToDo: Deprecate constants in future versions as they are no longer used in the codebase.
 */
define( 'ASTRA_PRO_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'dashboard', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );
define( 'ASTRA_PRO_CUSTOMIZER_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'customizer', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';

/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/lib/docs/class-astra-docs-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-wp-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/dark-mode.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

// Enable NPS Survey only if the starter templates version is < 4.3.7 or > 4.4.4 to prevent fatal error.
if ( ! defined( 'ASTRA_SITES_VER' ) || version_compare( ASTRA_SITES_VER, '4.3.7', '<' ) || version_compare( ASTRA_SITES_VER, '4.4.4', '>' ) ) {
	// NPS Survey Integration
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-notice.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-survey.php';
}

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

/* Setup API */
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-api-init.php';

if ( is_admin() ) {
	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'admin/class-astra-admin-loader.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-astra-notices.php';
}

/**
 * Metabox additions.
 */
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-elementor-editor-settings.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/posts-structures/class-astra-post-structures.php';
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/surecart/class-astra-surecart.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-starter-content.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/scroll-to-top/class-astra-scroll-to-top.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymous functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';

// Добавляем кастомные стили кнопок
function add_custom_button_styles() {
    wp_enqueue_style('custom-buttons', get_template_directory_uri() . '/custom-buttons.css', array(), '1.0.0');
    wp_enqueue_style('safari-image-fix', get_template_directory_uri() . '/safari-image-fix.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'add_custom_button_styles');

// Добавляем JavaScript для исправления Safari
function add_safari_fix_script() {
    wp_enqueue_script('safari-image-fix', get_template_directory_uri() . '/safari-image-fix.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'add_safari_fix_script');

// Добавляем улучшенные стили для статистических карточек
function add_statistics_cards_styles() {
    wp_enqueue_style('statistics-cards-enhanced', get_template_directory_uri() . '/statistics-cards-enhanced.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'add_statistics_cards_styles');

// Добавляем интерактивность для статистических карточек
function add_statistics_cards_interactivity() {
    wp_enqueue_script('statistics-cards-interactive', get_template_directory_uri() . '/statistics-cards-interactive.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'add_statistics_cards_interactivity');

// Добавляем переопределяющие стили для статистических карточек (высокий приоритет)
function add_statistics_cards_override_styles() {
    wp_enqueue_style('statistics-cards-override', get_template_directory_uri() . '/statistics-override.css', array(), '1.0.1');
}
add_action('wp_enqueue_scripts', 'add_statistics_cards_override_styles', 20);

// Глобальные шрифты Poppins и Inter для всего сайта
function enqueue_custom_fonts() {
    wp_enqueue_style('custom-fonts', get_template_directory_uri() . '/custom-fonts.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_fonts', 5);

// Подключение шрифтов в head для максимальной совместимости
require_once get_template_directory() . '/fonts-head.php';

// Удаление теней с цифр в статистических блоках
function remove_number_shadows() {
    wp_enqueue_style('remove-number-shadows', get_template_directory_uri() . '/remove-number-shadows.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'remove_number_shadows', 50);

// JavaScript для удаления теней с цифр
function remove_number_shadows_js() {
    wp_enqueue_script('remove-number-shadows-js', get_template_directory_uri() . '/remove-number-shadows.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'remove_number_shadows_js', 55);

// Усиление контрастности ТОЛЬКО заголовков в статистических блоках
function add_stats_headings_contrast() {
    wp_enqueue_style('stats-headings-contrast', get_template_directory_uri() . '/stats-headings-contrast.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'add_stats_headings_contrast', 60);

// Простое исправление иконок Elementor (только CSS, без JavaScript)
function fix_elementor_icons() {
    ?>
    <style>
    /* Исправление отображения иконок Elementor */
    @font-face {
        font-family: 'eicons';
        src: url('<?php echo plugins_url('elementor/assets/lib/eicons/fonts/eicons.woff2?5.43.0&fix='.time(), __FILE__); ?>') format('woff2'),
             url('<?php echo plugins_url('elementor/assets/lib/eicons/fonts/eicons.woff?5.43.0&fix='.time(), __FILE__); ?>') format('woff');
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }

    /* Принудительное применение шрифтов к иконкам */
    [class*=" eicon-"],
    [class^=eicon] {
        font-family: 'eicons' !important;
        display: inline-block !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        -webkit-font-smoothing: antialiased !important;
        -moz-osx-font-smoothing: grayscale !important;
    }

    /* Исправление для FontAwesome */
    .fa,
    .fas,
    .fab {
        font-family: 'Font Awesome 5 Free', 'Font Awesome 5 Brands' !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'fix_elementor_icons', 999);
add_action('admin_head', 'fix_elementor_icons', 999);

// Исправление цветов футера
function add_footer_fix_styles() {
    wp_enqueue_style('footer-fix', get_template_directory_uri() . '/footer-fix.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'add_footer_fix_styles', 100);

// JavaScript для исправления цветов футера
function add_footer_fix_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Исправление цветов заголовков и текста в футере
        function fixFooterColors() {
            // Находим все элементы футера
            const footerElements = document.querySelectorAll('footer, .site-footer, .footer-widget, .ast-footer-widget');
            
            footerElements.forEach(function(footer) {
                // Исправляем заголовки
                const headings = footer.querySelectorAll('h1, h2, h3, h4, h5, h6, .elementor-heading-title');
                headings.forEach(function(heading) {
                    heading.style.color = '#000000';
                    heading.style.fontWeight = 'bold';
                });
                
                // Исправляем весь текст
                const textElements = footer.querySelectorAll('p, span, div, a, li');
                textElements.forEach(function(element) {
                    if (element.style.color === '' || element.style.color === 'rgb(255, 255, 255)' || element.style.color === 'white') {
                        element.style.color = '#000000';
                    }
                });
            });
            
            // Скрываем пустой подвал
            const copyrightElements = document.querySelectorAll('.ast-footer-copyright, .footer-copyright, .copyright');
            copyrightElements.forEach(function(element) {
                if (element.textContent.trim() === '' || element.textContent.includes('Copyright') || element.textContent.includes('Powered by')) {
                    element.style.display = 'none';
                }
            });
            
            // Скрываем пустые строки футера
            const footerRows = document.querySelectorAll('.ast-builder-footer-row');
            footerRows.forEach(function(row) {
                if (row.textContent.trim() === '' || row.innerHTML.trim() === '') {
                    row.style.display = 'none';
                }
            });
        }
        
        // Запускаем исправление сразу
        fixFooterColors();
        
        // Запускаем исправление через небольшую задержку для динамически загружаемого контента
        setTimeout(fixFooterColors, 500);
        setTimeout(fixFooterColors, 1000);
        setTimeout(fixFooterColors, 2000);
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_footer_fix_script', 999);

// Заголовки безопасности для SSL
function add_security_headers() {
    if (!is_admin()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Заголовки для HTTPS
        if (is_ssl()) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
    }
}
add_action("send_headers", "add_security_headers");

/**
 * Добавляем кастомный футер только на страницы статей и блога
 */
function add_custom_footer() {
    if (!is_admin()) {
        // Показываем кастомный футер только на страницах статей и блога, но не на главной
        if (is_single() || is_page('blog2') || is_page('kalkulyator_bankovskikh_garantiy') || is_page('keysy_biznes_finans') || is_page('why-choose-me') || is_page('bankovskaya_garantiya_na_vozvrat_avansa') || is_page('contact') || is_home() || is_category() || is_tag() || is_archive()) {
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
        }
    }
}
add_action('wp_footer', 'add_custom_footer');

/**
 * Скрываем оригинальный футер на страницах статей и блога
 */
function hide_original_footer() {
    if (!is_admin() && (is_single() || is_page('blog2') || is_page('kalkulyator_bankovskikh_garantiy') || is_page('keysy_biznes_finans') || is_page('why-choose-me') || is_page('bankovskaya_garantiya_na_vozvrat_avansa') || is_page('contact') || is_home() || is_category() || is_tag() || is_archive())) {
        echo '<style>
        .site-footer,
        footer,
        .elementor-location-footer,
        .elementor-section.elementor-section-footer,
        .elementor-element.elementor-element-footer,
        .hfe-before-footer-wrap,
        .footer-width-fixer {
            display: none !important;
        }
        </style>';
    }
}
add_action('wp_head', 'hide_original_footer');

/**
 * Добавляем CSS для исправления цветов текста в футере
 */
function add_footer_text_color_fix() {
    if (!is_admin()) {
        echo '<style>
        .custom-elementor-footer .elementor-icon-list-text {
            color: #000000 !important;
        }
        .custom-elementor-footer .elementor-widget-text-editor {
            color: #000000 !important;
        }
        .custom-elementor-footer .elementor-widget-text-editor p,
        .custom-elementor-footer .elementor-widget-text-editor li {
            color: #000000 !important;
        }
        </style>';
    }
}
add_action('wp_head', 'add_footer_text_color_fix');

/**
 * Функция для транслитерации кириллических символов в латинские
 * Автоматически создает латинские slug для всех постов и страниц
 */
function cyrillic_to_latin_transliteration($text) {
    $transliteration = array(
        // Русские буквы
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        
        // Заглавные русские буквы
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
        'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
        'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        
        // Специальные символы
        '№' => 'no', ' ' => '-', '_' => '-', '.' => '-', ',' => '-', '!' => '', '?' => '',
        ':' => '', ';' => '', '"' => '', "'" => '', '(' => '', ')' => '', '[' => '', ']' => '',
        '{' => '', '}' => '', '<' => '', '>' => '', '|' => '', '\\' => '', '/' => '-',
        '=' => '', '+' => '', '*' => '', '&' => 'and', '%' => '', '$' => '', '#' => '',
        '@' => 'at', '~' => '', '`' => '', '^' => '', '№' => 'no'
    );
    
    // Применяем транслитерацию
    $text = strtr($text, $transliteration);
    
    // Убираем множественные дефисы и заменяем на один
    $text = preg_replace('/-+/', '-', $text);
    
    // Убираем дефисы в начале и конце
    $text = trim($text, '-');
    
    // Оставляем только латинские буквы, цифры и дефисы
    $text = preg_replace('/[^a-zA-Z0-9\-]/', '', $text);
    
    // Ограничиваем длину slug (максимум 200 символов)
    if (strlen($text) > 200) {
        $text = substr($text, 0, 200);
        $text = rtrim($text, '-');
    }
    
    return strtolower($text);
}

/**
 * Автоматическое создание латинского slug при сохранении поста/страницы
 */
function auto_generate_latin_slug($data, $postarr) {
    // Проверяем, что это пост или страница
    if (!in_array($data['post_type'], ['post', 'page'])) {
        return $data;
    }
    
    // Проверяем, что есть заголовок
    if (empty($data['post_title'])) {
        return $data;
    }
    
    // Проверяем, что slug не задан вручную (если slug пустой или содержит кириллицу)
    $current_slug = isset($postarr['post_name']) ? $postarr['post_name'] : '';
    
    // Если slug пустой или содержит кириллические символы, генерируем новый
    if (empty($current_slug) || preg_match('/[а-яё]/iu', $current_slug)) {
        $latin_slug = cyrillic_to_latin_transliteration($data['post_title']);
        
        // Проверяем уникальность slug
        $original_slug = $latin_slug;
        $counter = 1;
        
        while (true) {
            // Проверяем, существует ли такой slug
            $existing_post = get_page_by_path($latin_slug, OBJECT, $data['post_type']);
            
            // Если пост не найден или это тот же пост (при обновлении)
            if (!$existing_post || (isset($postarr['ID']) && $existing_post->ID == $postarr['ID'])) {
                break;
            }
            
            // Добавляем номер к slug
            $latin_slug = $original_slug . '-' . $counter;
            $counter++;
            
            // Защита от бесконечного цикла
            if ($counter > 1000) {
                $latin_slug = $original_slug . '-' . time();
                break;
            }
        }
        
        $data['post_name'] = $latin_slug;
    }
    
    return $data;
}

// Подключаем хук для автоматического создания slug
add_filter('wp_insert_post_data', 'auto_generate_latin_slug', 10, 2);

/**
 * Дополнительная функция для обработки существующих постов с кириллическими slug
 */
function update_existing_cyrillic_slugs() {
    global $wpdb;
    
    // Находим все посты и страницы с кириллическими slug
    $posts = $wpdb->get_results("
        SELECT ID, post_title, post_name, post_type 
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND post_name REGEXP '[а-яё]'
        ORDER BY ID DESC
        LIMIT 50
    ");
    
    foreach ($posts as $post) {
        $new_slug = cyrillic_to_latin_transliteration($post->post_title);
        
        // Проверяем уникальность
        $original_slug = $new_slug;
        $counter = 1;
        
        while (true) {
            $existing_post = get_page_by_path($new_slug, OBJECT, $post->post_type);
            
            if (!$existing_post || $existing_post->ID == $post->ID) {
                break;
            }
            
            $new_slug = $original_slug . '-' . $counter;
            $counter++;
            
            if ($counter > 1000) {
                $new_slug = $original_slug . '-' . time();
                break;
            }
        }
        
        // Обновляем slug
        wp_update_post(array(
            'ID' => $post->ID,
            'post_name' => $new_slug
        ));
        
        // Логируем изменения
        error_log("Updated slug for post ID {$post->ID}: '{$post->post_name}' -> '{$new_slug}'");
    }
}

/**
 * Функция для принудительного обновления всех кириллических slug
 * Вызывается через WP-CLI или админ-панель
 */
function force_update_all_cyrillic_slugs() {
    global $wpdb;
    
    // Находим ВСЕ посты и страницы с кириллическими slug
    $posts = $wpdb->get_results("
        SELECT ID, post_title, post_name, post_type 
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND post_name REGEXP '[а-яё]'
        ORDER BY ID DESC
    ");
    
    $updated_count = 0;
    
    foreach ($posts as $post) {
        $new_slug = cyrillic_to_latin_transliteration($post->post_title);
        
        // Проверяем уникальность
        $original_slug = $new_slug;
        $counter = 1;
        
        while (true) {
            $existing_post = get_page_by_path($new_slug, OBJECT, $post->post_type);
            
            if (!$existing_post || $existing_post->ID == $post->ID) {
                break;
            }
            
            $new_slug = $original_slug . '-' . $counter;
            $counter++;
            
            if ($counter > 1000) {
                $new_slug = $original_slug . '-' . time();
                break;
            }
        }
        
        // Обновляем slug
        $result = wp_update_post(array(
            'ID' => $post->ID,
            'post_name' => $new_slug
        ));
        
        if ($result && !is_wp_error($result)) {
            $updated_count++;
            error_log("Updated slug for post ID {$post->ID}: '{$post->post_name}' -> '{$new_slug}'");
        }
    }
    
    return $updated_count;
}
