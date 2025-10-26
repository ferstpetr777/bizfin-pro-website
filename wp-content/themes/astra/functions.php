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

/**
 * Добавление CSS стилей для статьи "Срок действия банковской гарантии"
 * Соответствует критериям матрицы плагина BizFin SEO Article Generator
 */
function add_bsag_article_styles() {
    if (is_single() && (get_the_ID() == 2871 || get_the_ID() == 2893 || get_the_ID() == 2914 || get_the_ID() == 2943)) { // ID статей "Срок действия банковской гарантии", "Реестр банковских гарантий", "Банковская гарантия для госзакупок" и "Образец банковской гарантии"
        ?>
        <style type="text/css">
        /* Стили для статьи "Срок действия банковской гарантии" */
        .intro-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #3498db;
            margin-bottom: 30px;
        }

        .toc {
            background: #ffffff;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .toc ul {
            list-style: none;
            padding-left: 0;
        }

        .toc li {
            margin: 8px 0;
        }

        .toc a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .toc a:hover {
            text-decoration: underline;
        }

        .article-image {
            width: 100%;
            max-width: 800px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            margin: 20px 0;
        }

        .example-box {
            background: #e8f4fd;
            border: 1px solid #3498db;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .info-box {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            background: #fff;
            color: #667eea;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .faq-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .faq-item {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .faq-question {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .faq-answer {
            color: #555;
        }

        .calendar-widget {
            background: #fff;
            border: 2px solid #3498db;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .calendar-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            font-size: 1rem;
        }

        .calendar-button {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }

        .table-responsive table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-responsive th, 
        .table-responsive td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .table-responsive th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        .table-responsive tr:hover {
            background: #f8f9fa;
        }

        /* Дополнительные стили для статьи "Реестр банковских гарантий" */
        .success-box {
            background: #d4edda;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .video-guide {
            background: #fff;
            border: 2px solid #3498db;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .video-placeholder {
            background: #f8f9fa;
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 40px;
            margin: 20px 0;
            text-align: center;
            color: #666;
        }

        .step-guide {
            background: #fff;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        .step-content {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 50px);
        }

        .case-study {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .case-title {
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }

        .search-widget {
            background: #fff;
            border: 2px solid #3498db;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .search-input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            font-size: 1rem;
            width: 300px;
            max-width: 100%;
        }

        .search-button {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        /* Дополнительные стили для статьи "Банковская гарантия для госзакупок" */
        .comparison-table {
            background: #fff;
            border: 2px solid #3498db;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            overflow-x: auto;
        }

        .comparison-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .comparison-table th, 
        .comparison-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .comparison-table th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        .comparison-table tr:hover {
            background: #f8f9fa;
        }

        .law-44 {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .law-223 {
            background: #f3e5f5;
            border-left: 4px solid #9c27b0;
        }

        .requirements-map {
            background: #fff;
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .requirement-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            display: flex;
            align-items: center;
        }

        .requirement-icon {
            background: #28a745;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .requirement-content {
            flex: 1;
        }

        .checklist {
            background: #fff;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .checklist-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .checklist-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            cursor: pointer;
        }

        .checklist-text {
            flex: 1;
        }

        /* Дополнительные стили для статьи "Образец банковской гарантии" */
        .structure-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            display: flex;
            align-items: center;
        }

        .structure-number {
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .structure-content {
            flex: 1;
        }

        .critical-condition {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }

        .bad-formulation {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }

        .good-formulation {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }

        .error-box {
            background: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .sample-document {
            background: #fff;
            border: 2px solid #6c757d;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .sample-header {
            background: #6c757d;
            color: white;
            padding: 10px;
            margin: -20px -20px 20px -20px;
            border-radius: 10px 10px 0 0;
            font-weight: bold;
            text-align: center;
        }

        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .download-section {
            background: #fff;
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .download-button {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }

        .download-button:hover {
            background: #218838;
        }

        /* Адаптивные стили */
        @media (max-width: 768px) {
            .intro-section, .toc, .faq-section {
                padding: 15px;
            }
            
            .cta-section {
                padding: 20px;
            }
            
            .calendar-widget {
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .table-responsive th, 
            .table-responsive td {
                padding: 8px;
            }
            
            .search-input {
                width: 100%;
            }
            
            .video-guide, .search-widget {
                padding: 15px;
            }
            
            .comparison-table, .requirements-map, .checklist {
                padding: 15px;
            }
            
            .requirement-item {
                flex-direction: column;
                text-align: center;
            }
            
            .requirement-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .structure-item {
                flex-direction: column;
                text-align: center;
            }
            
            .structure-number {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .sample-document, .download-section {
                padding: 15px;
            }
        }

        /* Стили для разумной ширины контента */
        .single-post .entry-content {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }

        .single-post .entry-content h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }

        .single-post .entry-content h2 {
            font-size: 2rem;
            color: #34495e;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .single-post .entry-content h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .single-post .entry-content h4 {
            font-size: 1.25rem;
            color: #34495e;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        /* Нормализация заголовков */
        .single-post .entry-content h1,
        .single-post .entry-content h2,
        .single-post .entry-content h3,
        .single-post .entry-content h4 {
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        /* Стили для мобильных устройств */
        @media (max-width: 768px) {
            .single-post .entry-content h1 {
                font-size: 2rem;
            }
            
            .single-post .entry-content h2 {
                font-size: 1.75rem;
            }
            
            .single-post .entry-content {
                padding: 0 10px;
            }
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'add_bsag_article_styles');
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
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
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

/**
 * Load custom functions and styles
 */
require_once ASTRA_THEME_DIR . 'functions_broken.php';






// Подключение CSS для исправления изображений в статьях
add_action("wp_enqueue_scripts", "enqueue_article_images_fix_css");
function enqueue_article_images_fix_css() {
    // Подключаем CSS только на страницах статей
    if (is_single() && get_post_type() === "post") {
        wp_enqueue_style(
            "article-images-fix", 
            get_stylesheet_directory_uri() . "/article-images-fix.css", 
            array(), 
            "1.0.0"
        );
    }
}

// Подключение CSS для стилей логотипа
add_action("wp_enqueue_scripts", "enqueue_logo_styles_css");
function enqueue_logo_styles_css() {
    wp_enqueue_style(
        "logo-styles", 
        get_stylesheet_directory_uri() . "/logo-styles.css", 
        array(), 
        "1.0.0"
    );
}

// Добавление алфавитного блока сразу после шапки на страницах статей
add_action('astra_content_before', 'add_alphabet_block_after_header');
function add_alphabet_block_after_header() {
    if (is_single() && get_post_type() === 'post') {
        // Подключаем стили и скрипты алфавитного блока
        wp_enqueue_style('abp-v2-css');
        wp_enqueue_script('abp-v2-js');
        
        echo '<div class="abp-v2-alphabet-block alignfull">';
        echo '<div class="abp-v2-main-header-content">';
        echo '<div class="abp-v2-main-title"><h1>Блог</h1></div>';
        echo '<div class="abp-v2-main-subtitle"><p>Найдите в алфавитном порядке</p></div>';
        echo '<div class="abp-v2-main-search">';
        echo '<input type="text" id="abp-v2-search-input" placeholder="Поиск по ключевым словам..." />';
        echo '<button id="abp-v2-search-btn" type="button">🔍</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="abp-v2-main-alphabet">';
        echo do_shortcode('[abp_v2_alphabet_only]');
        echo '</div>';
        echo '</div>';
        
        // Добавляем JavaScript для инициализации после загрузки DOM
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Инициализируем алфавитный блок
            if (typeof ABP_V2_Alphabet !== "undefined") {
                ABP_V2_Alphabet.init();
            }
        });
        </script>';
    }
}