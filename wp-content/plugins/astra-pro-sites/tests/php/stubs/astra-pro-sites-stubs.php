<?php

namespace {
    /**
     * Astra_Sites_Helper
     *
     * @since 1.0.0
     */
    class Astra_Sites_Helper
    {
        /**
         * Initiator
         *
         * @since 1.0.0
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Add svg image support
         *
         * @since 1.1.5
         *
         * @param array  $response    Attachment response.
         * @param object $attachment Attachment object.
         * @param array  $meta        Attachment meta data.
         */
        public function add_svg_image_support($response, $attachment, $meta)
        {
        }
        /**
         * Get SVG Dimensions
         *
         * @since 1.1.5
         *
         * @param  string $svg SVG file path.
         * @return array      Return SVG file height & width for valid SVG file.
         */
        public static function get_svg_dimensions($svg)
        {
        }
        /**
         * Custom Menu Widget
         *
         * In widget export we set the nav menu slug instead of ID.
         * So, In import process we check get menu id by slug and set
         * it in import widget process.
         *
         * @since 1.0.7
         *
         * @param  object $all_sidebars Widget data.
         * @return object               Set custom menu id by slug.
         */
        public function custom_menu_widget($all_sidebars)
        {
        }
        /**
         * Downloads an image from the specified URL.
         *
         * Taken from the core media_sideload_image() function and
         * modified to return an array of data instead of html.
         *
         * @since 1.0.10
         *
         * @param string $file The image file path.
         * @return array An array of image data.
         */
        public static function sideload_image($file)
        {
        }
        /**
         * Extract image URLs and other URLs from a given HTML content.
         *
         * @since 2.6.10
         *
         * @param string $content HTML content string.
         * @return array Array of URLS.
         */
        public static function extract_segregated_urls($content)
        {
        }
        /**
         * Get the client IP address.
         *
         * @since 2.6.4
         */
        public static function get_client_ip()
        {
        }
    }
    /**
     * Customizer Site options importer class.
     *
     * @since  1.0.0
     */
    class Astra_Site_Options_Import
    {
        /**
         * Instanciate Astra_Site_Options_Importer
         *
         * @since  1.0.0
         * @return (Object) Astra_Site_Options_Importer
         */
        public static function instance()
        {
        }
        /**
         * Import site options.
         *
         * @since  1.0.2    Updated option if exist in defined option array 'site_options()'.
         *
         * @since  1.0.0
         *
         * @param  (Array) $options Array of site options to be imported from the demo.
         */
        public function import_options($options = array())
        {
        }
        /**
         * Get post from post title and post type.
         *
         * @since 4.0.6
         *
         * @param  mixed  $post_title  post title.
         * @param  string $post_type post type.
         * @return mixed
         */
        public function get_page_by_title($post_title, $post_type)
        {
        }
    }
    /**
     * Customizer Data importer class.
     *
     * @since  1.0.0
     */
    class Astra_Customizer_Import
    {
        /**
         * Instantiate Astra_Customizer_Import
         *
         * @since  1.0.0
         * @return (Object) Astra_Customizer_Import
         */
        public static function instance()
        {
        }
        /**
         * Import customizer options.
         *
         * @since  1.0.0
         *
         * @param  (Array) $options customizer options from the demo.
         */
        public function import($options)
        {
        }
    }
    /**
     * Astra_Sites_Batch_Processing_Widgets
     *
     * @since 1.0.14
     */
    class Astra_Sites_Batch_Processing_Widgets
    {
        /**
         * WP Forms.
         *
         * @since 2.6.22
         * @var object Class object.
         */
        public $wpforms_ids_mapping;
        /**
         * Initiator
         *
         * @since 1.0.14
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.14
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 1.0.14
         * @return void
         */
        public function import()
        {
        }
        /**
         * Widget WP Forms
         *
         * @since 3.1.3
         * @return void
         */
        public function widget_wpform()
        {
        }
        /**
         * Widget Text
         *
         * @since 2.6.22
         * @return void
         */
        public function widget_text()
        {
        }
        /**
         * Widget Media Image
         *
         * @since 1.0.14
         * @return void
         */
        public function widget_media_image()
        {
        }
    }
    /**
     * Astra_Sites_Batch_Processing_Beaver_Builder
     *
     * @since 1.0.14
     */
    class Astra_Sites_Batch_Processing_Beaver_Builder
    {
        /**
         * Initiator
         *
         * @since 1.0.14
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.14
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 1.0.14
         * @return void
         */
        public function import()
        {
        }
        /**
         * Update post meta.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        public function import_single_post($post_id = 0)
        {
        }
        /**
         * Import Module Images.
         *
         * @param  object $settings Module settings object.
         * @return object
         */
        public static function update_module($settings)
        {
        }
        /**
         * Import Column Images.
         *
         * @param  object $settings Column settings object.
         * @return object
         */
        public static function update_column($settings)
        {
        }
        /**
         * Import Row Images.
         *
         * @param  object $settings Row settings object.
         * @return object
         */
        public static function update_row($settings)
        {
        }
        /**
         * Helper: Import BG Images.
         *
         * @param  object $settings Row settings object.
         * @return object
         */
        public static function import_bg_image($settings)
        {
        }
        /**
         * Helper: Import Photo.
         *
         * @param  object $settings Row settings object.
         * @return object
         */
        public static function import_photo($settings)
        {
        }
    }
    /**
     * Astra_Sites_Batch_Processing_Cleanup
     *
     * @since 4.0.11
     */
    class Astra_Sites_Batch_Processing_Cleanup
    {
        /**
         * Constructor
         *
         * @since 4.0.11
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 4.0.11
         * @return void
         */
        public function import()
        {
        }
    }
    /**
     * Astra Sites Batch Processing Brizy
     *
     * @since 1.2.14
     */
    class Astra_Sites_Batch_Processing_Brizy
    {
        /**
         * Initiator
         *
         * @since 1.2.14
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.2.14
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 1.2.14
         * @return void
         */
        public function import()
        {
        }
        /**
         * Update post meta.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        public function import_single_post($post_id = 0)
        {
        }
    }
}
namespace Elementor\TemplateLibrary {
    /**
     * Elementor template library local source.
     *
     * Elementor template library local source handler class is responsible for
     * handling local Elementor templates saved by the user locally on his site.
     *
     * @since 1.2.13 Added compatibility for Elemetnor v2.5.0
     * @since 1.0.0
     */
    class Astra_Sites_Batch_Processing_Elementor extends \Elementor\TemplateLibrary\Source_Local
    {
        /**
         * Import
         *
         * @since 1.0.14
         * @return void
         */
        public function import()
        {
        }
        /**
         * Update post meta.
         *
         * @since 1.0.14
         * @param  integer $post_id Post ID.
         * @return void
         */
        public function import_single_post($post_id = 0)
        {
        }
    }
}
namespace {
    /**
     * Astra_Sites_Batch_Processing_Customizer
     *
     * @since 3.0.22
     */
    class Astra_Sites_Batch_Processing_Customizer
    {
        /**
         * Initiator
         *
         * @since 3.0.22
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 3.0.22
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 3.0.22
         * @return void
         */
        public function import()
        {
        }
        /**
         * Downloads images from customizer.
         */
        public static function images_download()
        {
        }
    }
    /**
     * Astra_Sites_Batch_Processing_Importer
     *
     * @since 1.0.14
     */
    class Astra_Sites_Batch_Processing_Importer
    {
        /**
         * Initiator
         *
         * @since 1.0.14
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.14
         */
        public function __construct()
        {
        }
        /**
         * Import All Categories and Tags
         *
         * @since 2.6.22
         * @return void
         */
        public function import_all_categories_and_tags()
        {
        }
        /**
         * Import All Categories and Tags
         *
         * @since 2.6.22
         * @return void
         */
        public function import_all_categories()
        {
        }
        /**
         * Import Categories
         *
         * @since 2.0.0
         * @return void
         */
        public function import_site_categories()
        {
        }
        /**
         * Import Block Categories
         *
         * @since 2.0.0
         * @return void
         */
        public function import_block_categories()
        {
        }
        /**
         * Import Page Builders
         *
         * @since 2.0.0
         * @return void
         */
        public function set_license_page_builder()
        {
        }
        /**
         * Import Page Builders
         *
         * @since 2.0.0
         * @return void
         */
        public function import_page_builders()
        {
        }
        /**
         * Import Blocks
         *
         * @since 2.0.0
         * @param  integer $page Page number.
         * @return void
         */
        public function import_blocks($page = 1)
        {
        }
        /**
         * Import
         *
         * @since 1.0.14
         * @since 2.0.0 Added page no.
         *
         * @param  integer $page Page number.
         * @return array
         */
        public function import_sites($page = 1)
        {
        }
    }
    /**
     * Astra_Sites_Batch_Processing
     *
     * @since 1.0.14
     */
    class Astra_Sites_Batch_Processing
    {
        /**
         * Process All
         *
         * @since 1.0.14
         * @var object Class object.
         * @access public
         */
        public static $process_all;
        /**
         * Force Sync
         *
         * @since 4.2.4
         * @var bool is force sync.
         * @access public
         */
        public static $is_force_sync = \false;
        /**
         * Last Export Checksums
         *
         * @since 2.0.0
         * @var object Class object.
         * @access public
         */
        public $last_export_checksums;
        /**
         * Sites Importer
         *
         * @since 2.0.0
         * @var object Class object.
         * @access public
         */
        public static $process_site_importer;
        /**
         * Process Single Page
         *
         * @since 2.0.0
         * @var object Class object.
         * @access public
         */
        public static $process_single;
        /**
         * Initiator
         *
         * @since 1.0.14
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.14
         */
        public function __construct()
        {
        }
        /**
         * Check if sync is forced.
         *
         * @return void
         */
        public function check_is_force_sync()
        {
        }
        /**
         * Update the latest checksum after all the import batch processes are done.
         */
        public function sync_batch_complete()
        {
        }
        /**
         * Include Files
         *
         * @since 2.5.0
         */
        public function includes()
        {
        }
        /**
         * Import All Categories
         *
         * @since 2.6.22
         * @return void
         */
        public function import_all_categories()
        {
        }
        /**
         * Import All Categories and Tags
         *
         * @since 2.6.22
         * @return void
         */
        public function import_all_categories_and_tags()
        {
        }
        /**
         * Import Block Categories
         *
         * @since 2.0.0
         * @return void
         */
        public function import_block_categories()
        {
        }
        /**
         * Import Page Builders
         *
         * @since 2.0.0
         * @return void
         */
        public function import_page_builders()
        {
        }
        /**
         * Import Blocks
         *
         * @since 2.0.0
         * @return void
         */
        public function import_blocks()
        {
        }
        /**
         * Import Sites
         *
         * @since 2.0.0
         * @return void
         */
        public function import_sites()
        {
        }
        /**
         * Sites Requests Count
         *
         * @since 2.0.0
         * @return void
         */
        public function sites_requests_count()
        {
        }
        /**
         * Blocks Requests Count
         *
         * @since 2.1.0
         * @return void
         */
        public function blocks_requests_count()
        {
        }
        /**
         * Update Library Complete
         *
         * @since 2.0.0
         * @return void
         */
        public function update_library_complete()
        {
        }
        /**
         * Get Last Exported Checksum Status
         *
         * @since 2.0.0
         * @return string Checksums Status.
         */
        public function get_last_export_checksums()
        {
        }
        /**
         * Set Last Exported Checksum
         *
         * @since 2.0.0
         * @return string Checksums Status.
         */
        public function set_last_export_checksums()
        {
        }
        /**
         * Update Library
         *
         * @since 2.0.0
         * @return void
         */
        public function update_library()
        {
        }
        /**
         * Start Importer
         *
         * @since 2.0.0
         * @return void
         */
        public function start_importer()
        {
        }
        /**
         * Json Files Names.
         *
         * @since 2.6.1
         * @return array
         */
        public function get_default_assets()
        {
        }
        /**
         * Process Batch
         *
         * @since 2.0.0
         * @return mixed
         */
        public function process_batch()
        {
        }
        /**
         * Log
         *
         * @since 2.0.0
         *
         * @param  string $message Log message.
         * @return void.
         */
        public function log($message = '')
        {
        }
        /**
         * Process Import
         *
         * @since 2.0.0
         *
         * @return mixed Null if process is already started.
         */
        public function process_import()
        {
        }
        /**
         * Get Total Requests
         *
         * @return integer
         */
        public function get_total_requests()
        {
        }
        /**
         * Get Blocks Total Requests
         *
         * @return integer
         */
        public function get_total_blocks_requests()
        {
        }
        /**
         * Start Single Page Import
         *
         * @param  int $page_id Page ID .
         * @since 2.0.0
         * @return void
         */
        public function start_process_single($page_id)
        {
        }
        /**
         * Skip Image from Batch Processing.
         *
         * @since 1.0.14
         *
         * @param  boolean $can_process Batch process image status.
         * @param  array   $attachment  Batch process image input.
         * @return boolean
         */
        public function skip_image($can_process, $attachment)
        {
        }
        /**
         * Get all post id's
         *
         * @since 1.0.14
         *
         * @param  array $post_types Post types.
         * @return array
         */
        public static function get_pages($post_types = array())
        {
        }
        /**
         * Get Supporting Post Types..
         *
         * @since 1.3.7
         * @param  integer $feature Feature.
         * @return array
         */
        public static function get_post_types_supporting($feature)
        {
        }
        /**
         * Get all categories.
         *
         * @return void
         */
        public function get_all_categories()
        {
        }
        /**
         * Get all categories and tags.
         *
         * @return void
         */
        public function get_all_categories_and_tags()
        {
        }
    }
    /**
     * Widget Data exporter class.
     */
    class Astra_Widget_Importer
    {
        /**
         * Instance
         *
         * @return object
         */
        public static function instance()
        {
        }
        /**
         * Available widgets
         *
         * Gather site's widgets into array with ID base, name, etc.
         * Used by export and import functions.
         *
         * @since 0.4
         * @global array $wp_registered_widget_updates
         * @return array Widget information
         */
        public function wie_available_widgets()
        {
        }
        /**
         * Import widget JSON data
         *
         * @since 0.4
         * @global array $wp_registered_sidebars
         *
         * @param object $data JSON widget data from .wie file.
         *
         * @return array Results array
         */
        public function import_widgets_data($data)
        {
        }
    }
    /**
     * WooCommerce Compatibility
     *
     * @since 1.1.4
     */
    class Astra_Sites_Compatibility_WooCommerce
    {
        /**
         * Initiator
         *
         * @since 1.1.4
         * @return object initialized object of class.
         */
        public static function instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.1.4
         */
        public function __construct()
        {
        }
        /**
         * Add product attributes.
         *
         * @since 1.1.4
         *
         * @param  string $demo_data        Import data.
         * @param  array  $demo_api_uri     Demo site URL.
         * @return void
         */
        public function add_attributes($demo_data = array(), $demo_api_uri = '')
        {
        }
        /**
         * Create default WooCommerce tables
         *
         * @param string $plugin_init Plugin file which is activated.
         * @return void
         */
        public function install_wc($plugin_init)
        {
        }
        /**
         * Hook into the pre-process term filter of the content import and register the
         * custom WooCommerce product attributes, so that the terms can then be imported normally.
         *
         * This should probably be removed once the WP importer 2.0 support is added in WooCommerce.
         *
         * Fixes: [WARNING] Failed to import pa_size L warnings in content import.
         * Code from: woocommerce/includes/admin/class-wc-admin-importers.php (ver 2.6.9).
         *
         * Github issue: https://github.com/proteusthemes/one-click-demo-import/issues/71
         *
         * @since 3.0.0
         * @param  array $data The term data to import.
         * @return array       The unchanged term data.
         */
        public function woocommerce_product_attributes_registration($data)
        {
        }
        /**
         * Update WooCommerce Lookup Table.
         *
         * @since 3.0.0
         *
         * @return void
         */
        public function update_wc_lookup_table()
        {
        }
    }
    /**
     * Astra Sites Compatibility LearnDash
     *
     * @since 2.3.8
     */
    class Astra_Sites_Compatibility_LearnDash
    {
        /**
         * Initiator
         *
         * @since 2.3.8
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 2.3.8
         */
        public function __construct()
        {
        }
    }
    /**
     * Astra_Sites_Compatibility_SFWD_LMS
     *
     * @since 1.3.13
     */
    class Astra_Sites_Compatibility_SFWD_LMS
    {
        /**
         * Initiator
         *
         * @since 1.3.13
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.3.13
         */
        public function __construct()
        {
        }
        /**
         * Set LearnDash Landing pages with respect to Cartflows.
         *
         * @since 2.3.2
         */
        public function process_landing_pages_mapping()
        {
        }
        /**
         * Set post types
         *
         * @since 1.3.13
         *
         * @param array $post_types Post types.
         */
        public function set_post_types($post_types = array())
        {
        }
    }
    /**
     * Astra Sites Compatibility
     *
     * @since 1.0.11
     */
    class Astra_Sites_Compatibility
    {
        /**
         * Initiator
         *
         * @since 1.0.11
         * @return object initialized object of class.
         */
        public static function instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.11
         */
        public function __construct()
        {
        }
    }
}
namespace AstraSites\Elementor {
    /**
     * Elementor Compatibility
     *
     * @since 2.0.0
     */
    class Astra_Sites_Compatibility_Elementor
    {
        /**
         * Initiator
         *
         * @since 2.0.0
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 2.0.0
         */
        public function __construct()
        {
        }
        /**
         * Disable Elementor redirect.
         *
         * @return void.
         */
        public function disable_elementor_redirect()
        {
        }
        /**
         * Remove the transient update check for plugins callback from Elementor.
         * This reduces the extra code execution for Elementor.
         */
        public function init()
        {
        }
        /**
         * Disable the attachment metadata
         */
        public function disable_attachment_metadata()
        {
        }
        /**
         * Force Delete Elementor Kit
         *
         * Delete the previously imported Elementor kit.
         *
         * @param int    $post_id     Post name.
         * @param string $post_type   Post type.
         */
        public function force_delete_kit($post_id = 0, $post_type = '')
        {
        }
        /**
         * Process post meta before WP importer.
         *
         * Normalize Elementor post meta on import, We need the `wp_slash` in order
         * to avoid the unslashing during the `add_post_meta`.
         *
         * Fired by `wp_import_post_meta` filter.
         *
         * @since 1.4.3
         * @access public
         *
         * @param array $post_meta Post meta.
         *
         * @return array Updated post meta.
         */
        public function on_wp_import_post_meta($post_meta)
        {
        }
        /**
         * Process post meta before WXR importer.
         *
         * Normalize Elementor post meta on import with the new WP_importer, We need
         * the `wp_slash` in order to avoid the unslashing during the `add_post_meta`.
         *
         * Fired by `wxr_importer.pre_process.post_meta` filter.
         *
         * @since 1.4.3
         * @access public
         *
         * @param array $post_meta Post meta.
         *
         * @return array Updated post meta.
         */
        public function on_wxr_importer_pre_process_post_meta($post_meta)
        {
        }
    }
}
namespace {
    /**
     * Beaver Builder Compatibility
     *
     * @since 3.0.21
     */
    class Astra_Sites_Compatibility_BB
    {
        /**
         * Initiator
         *
         * @since 3.0.21
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 3.0.21
         */
        public function __construct()
        {
        }
        /**
         * Disable redirection for Beaver Builder plugin when activated via Starter templates import process.
         */
        public function bb_activated()
        {
        }
    }
    /**
     * Astra Sites Compatibility for 'UABB - Lite'
     *
     * @see  https://wordpress.org/plugins/ultimate-addons-for-beaver-builder-lite/
     *
     * @package Astra Sites
     * @since 3.0.23
     */
    /**
     * UABB compatibility for Starter Templates.
     */
    class Astra_Sites_Compatibility_UABB
    {
        /**
         * Initiator
         *
         * @since 3.0.23
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor.
         */
        public function __construct()
        {
        }
        /**
         * Disable redirec after installing and activating UABB.
         *
         * @return void
         */
        public function uabb_activation()
        {
        }
    }
    /**
     * Astra_Sites_Compatibility_Astra_Pro
     *
     * @since 1.0.0
     */
    class Astra_Sites_Compatibility_Astra_Pro
    {
        /**
         * Initiator
         *
         * @since 1.0.0
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Import
         *
         * @since 1.1.6
         * @return void
         */
        public function import()
        {
        }
        /**
         * Update Site Origin Active Widgets
         *
         * @since 1.0.0
         *
         * @param  string $plugin_init        Plugin init file.
         * @param  array  $data               Data.
         * @return void
         */
        public function astra_pro($plugin_init = '', $data = array())
        {
        }
        /**
         * Import custom 404 section.
         *
         * @since 1.0.0
         * @param  array $demo_data Site all data render from API call.
         * @param  array $demo_api_uri Demo URL.
         */
        public function import_custom_404($demo_data = array(), $demo_api_uri = '')
        {
        }
        /**
         * Import settings enabled Astra extensions from the demo.
         *
         * @since  1.0.0
         * @param  array $demo_data Site all data render from API call.
         * @param  array $demo_api_uri Demo URL.
         */
        public function import_enabled_extension($demo_data = array(), $demo_api_uri = '')
        {
        }
        /**
         * Start post meta mapping of Astra Addon
         *
         * @since 1.1.6
         *
         * @return null     If there is no import option data found.
         */
        public static function start_post_mapping()
        {
        }
        /**
         * Update Header Mapping Data
         *
         * @since 1.1.6
         *
         * @param  int    $post_id     Post ID.
         * @param  string $meta_key Post meta key.
         * @param  array  $mapping  Mapping array.
         * @return void
         */
        public static function update_header_mapping($post_id = '', $meta_key = '', $mapping = array())
        {
        }
        /**
         * Update Location Rules
         *
         * @since 1.1.6
         *
         * @param  int    $post_id     Post ID.
         * @param  string $meta_key Post meta key.
         * @param  array  $mapping  Mapping array.
         * @return void
         */
        public static function update_location_rules($post_id = '', $meta_key = '', $mapping = array())
        {
        }
        /**
         * Get mapping locations.
         *
         * @since 1.1.6
         *
         * @param  array $location Location data.
         * @return array            Location mapping data.
         */
        public static function get_location_mappings($location = array())
        {
        }
        /**
         * Get advanced header mapping data
         *
         * @since 1.1.6
         *
         * @param  array $headers_old  Header mapping stored data.
         * @param  array $headers_data Header mapping data.
         * @return array                Filtered header mapping data.
         */
        public static function get_header_mapping($headers_old = array(), $headers_data = array())
        {
        }
        /**
         * Clear Cache
         *
         * @since 1.2.3
         * @return void
         */
        public function clear_cache()
        {
        }
    }
    /**
     * Astra Sites Compatibility for 'Checkout Plugins – Stripe for WooCommerce'
     *
     * @see  https://wordpress.org/plugins/checkout-plugins-stripe-woo/
     *
     * @package Astra Sites
     * @since 3.0.23
     */
    /**
     * Checkout Plugins - Stripe compatibility for Starter Templates.
     */
    class Astra_Sites_Checkout_Plugins_Stripe_WOO
    {
        /**
         * Initiator
         *
         * @since 3.0.23
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor.
         */
        public function __construct()
        {
        }
        /**
         * Disable redirec after installing and activating Checkout Plugins - Stripe.
         *
         * @param string $plugin_init Plugin init file used for activation.
         * @return void
         */
        public function checkout_plugins($plugin_init)
        {
        }
    }
    /**
     * Astra_Sites_White_Label
     *
     * @since 1.0.12
     */
    class Astra_Sites_White_Label
    {
        /**
         * Initiator
         *
         * @since 1.0.12
         *
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.12
         */
        public function __construct()
        {
        }
        /**
         * Update Astra's menu priority to show after Dashboard menu.
         *
         * @param int $menu_priority top level menu priority.
         * @since 3.1.22
         */
        public function update_admin_menu_position($menu_priority)
        {
        }
        /**
         * Add White Label data
         *
         * @param array $args White label.
         *  @since 2.6.0
         */
        public function add_white_label_name($args = array())
        {
        }
        /**
         * White labels the plugins page.
         *
         * @since 1.0.12
         *
         * @param array $plugins Plugins Array.
         * @return array
         */
        public function plugins_page($plugins)
        {
        }
        /**
         * Get value of single key from option array.
         *
         * @since  2.0.0.
         * @param  string $type Option type.
         * @param  string $key  Option key.
         * @param  string $default  Default value if key not found.
         * @return mixed        Return stored option value.
         */
        public static function get_option($type = '', $key = '', $default = \null)
        {
        }
        /**
         * Remove a "view details" link from the plugin list table
         *
         * @since 1.0.12
         *
         * @param array  $plugin_meta  List of links.
         * @param string $plugin_file Relative path to the main plugin file from the plugins directory.
         * @param array  $plugin_data  Data from the plugin headers.
         * @return array
         */
        public function plugin_links($plugin_meta, $plugin_file, $plugin_data)
        {
        }
        /**
         * Add White Label setting's
         *
         * @since 1.0.12
         *
         * @param  array $settings White label setting.
         * @return array
         */
        public static function settings($settings = array())
        {
        }
        /**
         * Add White Label form
         *
         * @since 1.0.12
         *
         * @param  array $settings White label setting.
         * @return void
         */
        public static function add_white_label_form($settings = array())
        {
        }
        /**
         * Page Title
         *
         * @since 1.0.12
         *
         * @param  string $title Page Title.
         * @return string        Filtered Page Title.
         */
        public function get_white_label_name($title = '')
        {
        }
        /**
         * White Label Link
         *
         * @since 2.0.0
         *
         * @param  string $link  Default link.
         * @return string        Filtered Page Title.
         */
        public function get_white_label_link($link = '')
        {
        }
        /**
         * Is Astra sites White labeled
         *
         * @since 1.2.13
         *
         * @return string
         */
        public function is_white_labeled()
        {
        }
        /**
         * Get white label name
         *
         * @since 2.6.0
         *
         * @return string
         */
        public function get_white_label()
        {
        }
    }
    /**
     * Astra Sites Importer
     */
    class Astra_Sites_Importer_Log
    {
        /**
         * Set Instance
         *
         * @since 1.1.0
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Check file read/write permissions and process.
         *
         * @since 1.1.0
         * @return null
         */
        public function has_file_read_write()
        {
        }
        /**
         * File Permission Notice
         *
         * @since 2.0.0
         * @return void
         */
        public function file_permission_notice()
        {
        }
        /**
         * Add log file URL in UI response.
         *
         * @since 1.1.0
         */
        public static function add_log_file_url()
        {
        }
        /**
         * Current Time for log.
         *
         * @since 1.1.0
         * @return string Current time with time zone.
         */
        public static function current_time()
        {
        }
        /**
         * Import Start
         *
         * @since 1.1.0
         * @param  array  $data         Import Data.
         * @param  string $demo_api_uri Import site API URL.
         * @return void
         */
        public function start($data = array(), $demo_api_uri = '')
        {
        }
        /**
         * Get Log File
         *
         * @since 1.1.0
         * @return string log file URL.
         */
        public static function get_log_file()
        {
        }
        /**
         * Log file directory
         *
         * @since 1.1.0
         * @param  string $dir_name Directory Name.
         * @return array    Uploads directory array.
         */
        public static function log_dir($dir_name = 'astra-sites')
        {
        }
        /**
         * Set log file
         *
         * @since 1.1.0
         */
        public static function set_log_file()
        {
        }
        /**
         * Write content to a file.
         *
         * @since 1.1.0
         * @param string $content content to be saved to the file.
         */
        public static function add($content)
        {
        }
        /**
         * Debug Mode
         *
         * @since 1.1.0
         * @return string Enabled for Debug mode ON and Disabled for Debug mode Off.
         */
        public static function get_debug_mode()
        {
        }
        /**
         * Memory Limit
         *
         * @since 1.1.0
         * @return string Memory limit.
         */
        public static function get_memory_limit()
        {
        }
        /**
         * Timezone
         *
         * @since 1.1.0
         * @see https://codex.wordpress.org/Option_Reference/
         *
         * @return string Current timezone.
         */
        public static function get_timezone()
        {
        }
        /**
         * Operating System
         *
         * @since 1.1.0
         * @return string Current Operating System.
         */
        public static function get_os()
        {
        }
        /**
         * Server Software
         *
         * @since 1.1.0
         * @return string Current Server Software.
         */
        public static function get_software()
        {
        }
        /**
         * MySql Version
         *
         * @since 1.1.0
         * @return string Current MySql Version.
         */
        public static function get_mysql_version()
        {
        }
        /**
         * XML Reader
         *
         * @since 1.2.8
         * @return string Current XML Reader status.
         */
        public static function get_xmlreader_status()
        {
        }
        /**
         * PHP Version
         *
         * @since 1.1.0
         * @return string Current PHP Version.
         */
        public static function get_php_version()
        {
        }
        /**
         * PHP Max Input Vars
         *
         * @since 1.1.0
         * @return string Current PHP Max Input Vars
         */
        public static function get_php_max_input_vars()
        {
        }
        /**
         * PHP Max Post Size
         *
         * @since 1.1.0
         * @return string Current PHP Max Post Size
         */
        public static function get_php_max_post_size()
        {
        }
        /**
         * PHP Max Execution Time
         *
         * @since 1.1.0
         * @return string Current Max Execution Time
         */
        public static function get_max_execution_time()
        {
        }
        /**
         * PHP GD Extension
         *
         * @since 1.1.0
         * @return string Current PHP GD Extension
         */
        public static function get_php_extension_gd()
        {
        }
        /**
         * Display Data
         *
         * @since 2.0.0
         * @return void
         */
        public function display_data()
        {
        }
    }
    /**
     * Astra_Sites_Utils
     */
    class Astra_Sites_Utils
    {
        /**
         * Third party cache plugin clear cache.
         *
         * @since 4.0.0
         * @return void
         */
        public static function third_party_cache_plugins_clear_cache()
        {
        }
        /**
         * This function helps to purge all cache in clodways envirnoment.
         * In presence of Breeze plugin (https://wordpress.org/plugins/breeze/)
         *
         * @since 4.0.0
         * @return void
         */
        public static function clear_cloudways_cache()
        {
        }
    }
    /**
     * Astra Sites Importer
     */
    class Astra_Sites_Importer
    {
        /**
         * Instance
         *
         * @since  1.0.0
         * @var (Object) Class object
         */
        public static $instance = \null;
        /**
         * Set Instance
         *
         * @since  1.0.0
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor.
         *
         * @since  1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Delete imported posts
         *
         * @since 1.3.0
         * @since 1.4.0 The `$post_id` was added.
         * Note: This function can be deleted after a few releases since we are performing the delete operation in chunks.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        public function delete_imported_posts($post_id = 0)
        {
        }
        /**
         * Delete imported WP forms
         *
         * @since 1.3.0
         * @since 1.4.0 The `$post_id` was added.
         * Note: This function can be deleted after a few releases since we are performing the delete operation in chunks.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        public function delete_imported_wp_forms($post_id = 0)
        {
        }
        /**
         * Delete imported terms
         *
         * @since 1.3.0
         * @since 1.4.0 The `$post_id` was added.
         * Note: This function can be deleted after a few releases since we are performing the delete operation in chunks.
         *
         * @param  integer $term_id Term ID.
         * @return void
         */
        public function delete_imported_terms($term_id = 0)
        {
        }
        /**
         * Delete related transients
         *
         * @since 3.1.3
         */
        public function delete_related_transient()
        {
        }
        /**
         * Delete directory when installing plugin.
         *
         * Set by enabling `clear_destination` option in the upgrader.
         *
         * @since 3.0.10
         * @param array $options Options for the upgrader.
         * @return array $options The options.
         */
        public function plugin_install_clear_directory($options)
        {
        }
        /**
         * Restrict WooCommerce Pages Creation process
         *
         * Why? WooCommerce creates set of pages on it's activation
         * These pages are re created via our XML import step.
         * In order to avoid the duplicacy we restrict these page creation process.
         *
         * @since 3.0.0
         */
        public function disable_default_woo_pages_creation()
        {
        }
        /**
         * Set the timeout for the HTTP request by request URL.
         *
         * E.g. If URL is images (jpg|png|gif|jpeg) are from the domain `https://websitedemos.net` then we have set the timeout by 30 seconds. Default 5 seconds.
         *
         * @since 1.3.8
         *
         * @param int    $timeout_value Time in seconds until a request times out. Default 5.
         * @param string $url           The request URL.
         */
        public function set_timeout_for_images($timeout_value, $url)
        {
        }
        /**
         * Change flow status
         *
         * @since 2.0.0
         *
         * @param  array $args Flow query args.
         * @return array Flow query args.
         */
        public function change_flow_status($args)
        {
        }
        /**
         * Track Flow
         *
         * @since 2.0.0
         *
         * @param  integer $flow_id Flow ID.
         * @return void
         */
        public function track_flows($flow_id)
        {
        }
        /**
         * Import WP Forms
         *
         * @since 1.2.14
         * @since 1.4.0 The `$wpforms_url` was added.
         *
         * @param  string $wpforms_url WP Forms JSON file URL.
         * @return void
         */
        public function import_wpforms($wpforms_url = '')
        {
        }
        /**
         * Import CartFlows
         *
         * @since 2.0.0
         *
         * @param  string $url Cartflows JSON file URL.
         * @return void
         */
        public function import_cartflows($url = '')
        {
        }
        /**
         * Get single demo.
         *
         * @since  1.0.0
         *
         * @param  (String) $demo_api_uri API URL of a demo.
         *
         * @return (Array) $astra_demo_data demo data for the demo.
         */
        public static function get_single_demo($demo_api_uri)
        {
        }
        /**
         * Clear Cache.
         *
         * @since  1.0.9
         */
        public function clear_related_cache()
        {
        }
        /**
         * Update Latest Checksums
         *
         * Store latest checksum after batch complete.
         *
         * @since 2.0.0
         * @return void
         */
        public function update_latest_checksums()
        {
        }
    }
    /**
     * Astra Admin Settings
     */
    class Astra_Sites_Page
    {
        /**
         * View all actions
         *
         * @since 1.0.6
         * @var array $view_actions
         */
        public $view_actions = array();
        /**
         * Initiator
         *
         * @since 1.3.0
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.3.0
         */
        public function __construct()
        {
        }
        /**
         * Admin notice
         *
         * @since 1.3.5
         *
         * @return void
         */
        public function getting_started()
        {
        }
        /**
         * Save Page Builder
         *
         * @since 1.4.0 The `$page_builder_slug` was added.
         *
         * @param  string $page_builder_slug Page Builder Slug.
         * @return mixed
         */
        public function save_page_builder_on_submit($page_builder_slug = '')
        {
        }
        /**
         * Save Page Builder
         *
         * @return void
         */
        public function save_page_builder_on_ajax()
        {
        }
        /**
         * Dismiss AI Promotion
         *
         * @return void
         */
        public function dismiss_ai_promotion()
        {
        }
        /**
         * Get Page Builder Sites
         *
         * @since 2.0.0
         *
         * @param  string $default_page_builder default page builder slug.
         * @return array page builder sites.
         */
        public function get_sites_by_page_builder($default_page_builder = '')
        {
        }
        /**
         * Get single setting value
         *
         * @param  string $key      Setting key.
         * @param  mixed  $defaults Setting value.
         * @return mixed           Stored setting value.
         */
        public function get_setting($key = '', $defaults = '')
        {
        }
        /**
         * Get Settings
         *
         * @return array Stored settings.
         */
        public function get_settings()
        {
        }
        /**
         * Update Settings
         *
         * @param  array $args Arguments.
         */
        public function update_settings($args = array())
        {
        }
        /**
         * View actions
         *
         * @since 1.0.11
         */
        public function get_view_actions()
        {
        }
        /**
         * Site Filters
         *
         * @since 2.0.0
         *
         * @return void
         */
        public function site_filters()
        {
        }
        /**
         * Get Default Page Builder
         *
         * @since 2.0.0
         *
         * @return mixed page builders or empty string.
         */
        public function get_default_page_builder()
        {
        }
        /**
         * Get Page Builders
         *
         * @since 2.0.0
         *
         * @param  string $slug Page Builder Slug.
         * @return array page builders.
         */
        public function get_page_builder_image($slug)
        {
        }
        /**
         * Page Builder List
         *
         * @since 1.4.0
         * @return array
         */
        public function get_page_builders()
        {
        }
        /**
         * Get and return page URL
         *
         * @param string $menu_slug Menu name.
         * @since 1.0.6
         * @return  string page url
         */
        public function get_page_url($menu_slug)
        {
        }
        /**
         * Converts a period of time in seconds into a human-readable format representing the interval.
         *
         * @since  2.0.0
         *
         * Example:
         *
         *     echo self::interval( 90 );
         *     // 1 minute 30 seconds
         *
         * @param  int $since A period of time in seconds.
         * @return string An interval represented as a string.
         */
        public function interval($since)
        {
        }
        /**
         * Check Cron Status
         *
         * Gets the current cron status by performing a test spawn. Cached for one hour when all is well.
         *
         * @since 2.0.0
         *
         * @param bool $cache Whether to use the cached result from previous calls.
         * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a WP_Error object if not.
         */
        public static function test_cron($cache = \true)
        {
        }
    }
}
namespace Elementor\TemplateLibrary {
    /**
     * Elementor template library local source.
     *
     * Elementor template library local source handler class is responsible for
     * handling local Elementor templates saved by the user locally on his site.
     *
     * @since 2.0.0 Added compatibility for Elemetnor v2.5.0
     */
    class Astra_Sites_Elementor_Pages extends \Elementor\TemplateLibrary\Source_Local
    {
        /**
         * Update post meta.
         *
         * @since 2.0.0
         * @param  integer $post_id Post ID.
         * @param  array   $data Elementor Data.
         * @return array   $data Elementor Imported Data.
         */
        public function import($post_id = 0, $data = array())
        {
        }
    }
}
namespace {
    /**
     * Astra_Sites_File_System
     */
    class Astra_Sites_File_System
    {
        /**
         * Folder name for the json files.
         * 
         * @var string
         * @since 4.2.0
         */
        public static $folder_name = 'json';
        /**
         * Instance of Astra_Sites.
         *
         * @since  4.2.0
         * @return self Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Create files for demo content.
         * 
         * @return void
         * @since 4.2.0
         */
        public function create_file()
        {
        }
        /**
         * Delete a JSON file from the uploads directory.
         *
         * @param string $file_name File name to be deleted.
         * @return void True on success, false on failure.
         */
        public function delete_json_file($file_name)
        {
        }
        /**
         * Getting json file for templates from uploads.
         * 
         * @param string $file_name  File data.
         * @param bool   $array_format  Is The file content array.
         * 
         * @return mixed
         */
        public function get_json_file_content($file_name, $array_format = \true)
        {
        }
        /**
         * Getter for get_json_file_content
         *
         * @since  4.2.0
         * 
         * @return mixed
         */
        public function get_demo_content()
        {
        }
        /**
         * Delete for get_json_file_content
         *
         * @since  4.2.0
         * 
         * @return mixed
         */
        public function delete_demo_content()
        {
        }
        /**
         * Create single json file.
         *
         * @since 4.2.2
         * @param array<string, mixed> $file file data.
         * 
         * @return void
         */
        public function create_single_file($file)
        {
        }
        /**
         * Update files/directories.
         * 
         * @param string $file_name    The file name.
         * @param mixed  $file_content The file content.
         * 
         * @return void
         */
        public function update_json_file($file_name, $file_content)
        {
        }
        /**
         * Setter for update_json_file()
         * 
         * @param string|int $file_content The file content.
         * 
         * @return mixed
         */
        public function update_demo_data($file_content)
        {
        }
    }
    /**
     * Astra Sites Update
     *
     * @since 4.2.2
     */
    class Astra_Sites_Update
    {
        /**
         * Initiator
         *
         * @since 4.2.2
         * @return object initialized object of class.
         */
        public static function set_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 4.2.2
         */
        public function __construct()
        {
        }
        /**
         * Update
         *
         * @since 4.2.2
         * @return void
         */
        public static function init()
        {
        }
    }
    /**
     * Astra_Sites
     */
    class Astra_Sites
    {
        /**
         * API Domain name
         *
         * @var (String) URL
         */
        public $api_domain;
        /**
         * API URL which is used to get the response from.
         *
         * @since  1.0.0
         * @var (String) URL
         */
        public $api_url;
        /**
         * Search API URL which is used to get the response from.
         *
         * @since  2.0.0
         * @var (String) URL
         */
        public $search_analytics_url;
        /**
         * Import Analytics API URL
         *
         * @since  3.1.4
         * @var (String) URL
         */
        public $import_analytics_url;
        /**
         * API URL which is used to get the response from Pixabay.
         *
         * @since  2.0.0
         * @var (String) URL
         */
        public $pixabay_url;
        /**
         * API Key which is used to get the response from Pixabay.
         *
         * @since  2.0.0
         * @var (String) URL
         */
        public $pixabay_api_key;
        /**
         * Localization variable
         *
         * @since  2.0.0
         * @var (Array) $local_vars
         */
        public static $local_vars = array();
        /**
         * Localization variable
         *
         * @since  2.0.0
         * @var (Array) $wp_upload_url
         */
        public $wp_upload_url = '';
        /**
         * Instance of Astra_Sites.
         *
         * @since  1.0.0
         *
         * @return self Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Set reset data
         * Note: This function can be deleted after a few releases since we are performing the delete operation in chunks.
         * 
         * @return array<string, array>
         */
        public function get_reset_data()
        {
        }
        /**
         * Enable ZipAI Copilot.
         *
         * @since 3.5.0
         *
         * @param array $modules module array.
         * @return boolean
         */
        public function enable_zip_ai_copilot($modules)
        {
        }
        /** 
         *  Set adding AI icon to WordPress menu.
         * 
         * @return void
         */
        public function add_custom_admin_css()
        {
        }
        /**
         * Set plugin param for auth URL.
         *
         * @param array $url_param url parameters.
         *
         * @since  3.5.0
         */
        public function add_auth_url_param($url_param)
        {
        }
        /**
         * Get plugin status
         *
         * @since 3.5.0
         *
         * @param  string $plugin_init_file Plguin init file.
         * @return string
         */
        public function get_plugin_status($plugin_init_file)
        {
        }
        /**
         * Add slashes while importing the XML with WordPress Importer v2.
         *
         * @param array $postdata Processed Post data.
         * @param array $data Post data.
         */
        public function wp_slash_after_xml_import($postdata, $data)
        {
        }
        /**
         * Check is Starter Templates AJAX request.
         *
         * @since 2.7.0
         * @return boolean
         */
        public function is_starter_templates_request()
        {
        }
        /**
         * Filters the message that the default PHP error template displays.
         *
         * @since 2.7.0
         *
         * @param string $message HTML error message to display.
         * @param array  $error   Error information retrieved from `error_get_last()`.
         * @return mixed
         */
        public function php_error_message($message, $error)
        {
        }
        /**
         * Filters an HTTP status header.
         *
         * @since 2.6.20
         *
         * @param string $status_header HTTP status header.
         * @param int    $code          HTTP status code.
         * @param string $description   Description for the status code.
         * @param string $protocol      Server protocol.
         *
         * @return mixed
         */
        public function status_header($status_header, $code, $description, $protocol)
        {
        }
        /**
         * Update Analytics Optin/Optout
         */
        public function update_analytics()
        {
        }
        /**
         * Update Subscription
         */
        public function update_subscription()
        {
        }
        /**
         * Push Data to Search API.
         *
         * @since  2.0.0
         * @param array<string, string> $response Response data object.
         * @param array<string, string> $data Data object.
         *
         * @return array Search response.
         */
        public function search_push($response, $data)
        {
        }
        /**
         * Push Data to Import Analytics API.
         *
         * @since  3.1.4
         */
        public function push_to_import_analytics()
        {
        }
        /**
         * Before Astra Image delete, remove from options.
         *
         * @since  2.0.0
         * @param int $id ID to deleting image.
         * @return void
         */
        public function delete_astra_images($id)
        {
        }
        /**
         * Elementor Batch Process via AJAX
         *
         * @since 2.0.0
         */
        public function elementor_process_import_for_page()
        {
        }
        /**
         * API Request
         *
         * @since 2.0.0
         */
        public function api_request()
        {
        }
        /**
         * API Request
         *
         * @since 3.2.4
         */
        public function elementor_api_request()
        {
        }
        /**
         * API Flush Request
         *
         * @since 3.2.4
         */
        public function elementor_flush_request()
        {
        }
        /**
         * Insert Template
         *
         * @return void
         */
        public function insert_image_templates()
        {
        }
        /**
         * Insert Template
         *
         * @return void
         */
        public function insert_image_templates_bb_and_brizy()
        {
        }
        /**
         * Insert Template
         *
         * @return void
         */
        public function insert_templates()
        {
        }
        /**
         * Add/Remove Favorite.
         *
         * @since  2.0.0
         */
        public function add_to_favorite()
        {
        }
        /**
         * Import Template.
         *
         * @since  2.0.0
         */
        public function create_template()
        {
        }
        /**
         * Search Images.
         *
         * @since 2.7.3.
         */
        public function search_images()
        {
        }
        /**
         * Download and save the image in the media library.
         *
         * @since  2.0.0
         */
        public function create_image()
        {
        }
        /**
         * Set the upload directory
         */
        public function get_wp_upload_url()
        {
        }
        /**
         * Create the image and return the new media upload id.
         *
         * @param String $url URL to pixabay image.
         * @param String $name Name to pixabay image.
         * @param String $photo_id Photo ID to pixabay image.
         * @param String $description Description to pixabay image.
         * @see http://codex.wordpress.org/Function_Reference/wp_insert_attachment#Example
         */
        public function create_image_from_url($url, $name, $photo_id, $description = '')
        {
        }
        /**
         * Import Post Meta
         *
         * @since 2.0.0
         *
         * @param  integer $post_id  Post ID.
         * @param  array   $metadata  Post meta.
         * @return void
         */
        public function import_post_meta($post_id, $metadata)
        {
        }
        /**
         * Import Post Meta
         *
         * @since 2.0.0
         *
         * @param  integer $post_id  Post ID.
         * @param  array   $metadata  Post meta.
         * @return void
         */
        public function import_template_meta($post_id, $metadata)
        {
        }
        /**
         * Close getting started notice for current user
         *
         * @since 1.3.5
         * @return void
         */
        public function getting_started_notice()
        {
        }
        /**
         * Get theme install, active or inactive status.
         *
         * @since 1.3.2
         *
         * @return string Theme status
         */
        public function get_theme_status()
        {
        }
        /**
         * Loads textdomain for the plugin.
         *
         * @since 1.0.1
         */
        public function load_textdomain()
        {
        }
        /**
         * Show action links on the plugin screen.
         *
         * @param   mixed $links Plugin Action links.
         * @return  array
         */
        public function action_links($links)
        {
        }
        /**
         * Get the API URL.
         *
         * @since  1.0.0
         * 
         * @return string
         */
        public static function get_api_domain()
        {
        }
        /**
         * Setter for $api_url
         *
         * @since  1.0.0
         */
        public function set_api_url()
        {
        }
        /**
         * Getter for $api_url
         *
         * @since  1.0.0
         * 
         * @return string
         */
        public function get_api_url()
        {
        }
        /**
         * Enqueue admin scripts.
         *
         * @since  1.3.2    Added 'install-theme.js' to install and activate theme.
         * @since  1.0.5    Added 'getUpgradeText' and 'getUpgradeURL' localize variables.
         *
         * @since  1.0.0
         *
         * @param  string $hook Current hook name.
         * @return void
         */
        public function admin_enqueue($hook = '')
        {
        }
        /**
         * Get CTA link
         *
         * @param string $source    The source of the link.
         * @param string $medium    The medium of the link.
         * @param string $campaign  The campaign of the link.
         * @return array
         */
        public function get_cta_link($source = '', $medium = '', $campaign = '')
        {
        }
        /**
         * Get CTA Links
         *
         * @since 2.6.18
         *
         * @param string $source    The source of the link.
         * @param string $medium    The medium of the link.
         * @param string $campaign  The campaign of the link.
         * @return array
         */
        public function get_cta_links($source = '', $medium = '', $campaign = '')
        {
        }
        /**
         * Returns Localization Variables.
         *
         * @since 2.0.0
         */
        public function get_local_vars()
        {
        }
        /**
         * Get palette colors
         *
         * @since 4.0.0
         *
         * @return mixed
         */
        public function get_page_palette_colors()
        {
        }
        /**
         * Get default AI categories.
         *
         * @since 2.0.0
         *
         * @return array
         */
        public function get_default_ai_categories()
        {
        }
        /**
         * Get palette colors
         *
         * @since 4.0.0
         *
         * @return mixed
         */
        public function get_block_palette_colors()
        {
        }
        /**
         * Display subscription form
         *
         * @since 2.6.1
         *
         * @return boolean
         */
        public function should_display_subscription_form()
        {
        }
        /**
         * Import Compatibility Errors
         *
         * @since 2.0.0
         * @return mixed
         */
        public function get_compatibilities_data()
        {
        }
        /**
         * Get all compatibilities
         *
         * @since 2.0.0
         *
         * @return array
         */
        public function get_compatibilities()
        {
        }
        /**
         * Register module required js on elementor's action.
         *
         * @since 2.0.0
         */
        public function register_widget_scripts()
        {
        }
        /**
         * Register module required js on elementor's action.
         *
         * @since 2.0.0
         */
        public function popup_styles()
        {
        }
        /**
         * Get all sites
         *
         * @since 2.0.0
         * @return array All sites.
         */
        public function get_all_sites()
        {
        }
        /**
         * Get all sites
         *
         * @since 2.2.4
         * @param  string $option Site options name.
         * @return mixed Site Option value.
         */
        public function get_api_option($option)
        {
        }
        /**
         * Get all blocks
         *
         * @since 2.0.0
         * @return array All Elementor Blocks.
         */
        public function get_all_blocks()
        {
        }
        /**
         * Retrieves the required plugins data based on the response and required plugin list.
         *
         * @param array             $response            The response containing the plugin data.
         * @param array             $required_plugins    The list of required plugins.
         * @param array<int,string> $features    The list of selected features.
         * @since 3.2.5
         * @return array                     The array of required plugins data.
         */
        public function get_required_plugins_data($response, $required_plugins, $features = array())
        {
        }
        /**
         * Get all required plugin list.
         *
         * @param  array<int,string>               $features list of features.
         * @param  array<int,array<string,string>> $required_plugins required plugins.
         * @return array<int,array<string,string>> The array of required plugins data.
         */
        public function get_feature_plugin_list($features, $required_plugins = array())
        {
        }
        /**
         * After Plugin Activate
         *
         * @since 2.0.0
         *
         * @param  string $plugin_init        Plugin Init File.
         * @param  array  $options            Site Options.
         * @param  array  $enabled_extensions Enabled Extensions.
         * @return void
         */
        public function after_plugin_activate($plugin_init = '', $options = array(), $enabled_extensions = array())
        {
        }
        /**
         * Has Pro Version Support?
         * And
         * Is Pro Version Installed?
         *
         * Check Pro plugin version exist of requested plugin lite version.
         *
         * Eg. If plugin 'BB Lite Version' required to import demo. Then we check the 'BB Agency Version' is exist?
         * If yes then we only 'Activate' Agency Version. [We couldn't install agency version.]
         * Else we 'Activate' or 'Install' Lite Version.
         *
         * @since 1.0.1
         *
         * @param  string $lite_version Lite version init file.
         * @return mixed               Return false if not installed or not supported by us
         *                                    else return 'Pro' version details.
         */
        public function pro_plugin_exist($lite_version = '')
        {
        }
        /**
         * Get Default Page Builders
         *
         * @since 2.0.0
         * @return array
         */
        public function get_default_page_builders()
        {
        }
        /**
         * Get Page Builders
         *
         * @since 2.0.0
         * @return array
         */
        public function get_page_builders()
        {
        }
        /**
         * Get Page Builder Filed
         *
         * @since 2.0.0
         * @param  string $page_builder Page Bulider.
         * @param  string $field        Field name.
         * @return mixed
         */
        public function get_page_builder_field($page_builder = '', $field = '')
        {
        }
        /**
         * Get License Key
         *
         * @since 2.0.0
         * @return string
         */
        public function get_license_key()
        {
        }
        /**
         * Get Sync Complete Message
         *
         * @since 2.0.0
         * @param  boolean $echo Echo the message.
         * @return mixed
         */
        public function get_sync_complete_message($echo = \false)
        {
        }
        /**
         * Get an instance of WP_Filesystem_Direct.
         *
         * @since 2.0.0
         * @return mixed A WP_Filesystem_Direct instance.
         */
        public static function get_filesystem()
        {
        }
        /**
         * Disable WP-Forms redirect.
         *
         * @return void.
         */
        public function disable_wp_forms_redirect()
        {
        }
        /**
         * Admin Dashboard Notices.
         *
         * @since 3.1.17
         * @return void
         */
        public function admin_dashboard_notices()
        {
        }
        /**
         * Admin Welcome Notice.
         *
         * @since 3.1.17
         * @return void
         */
        public function admin_welcome_notices()
        {
        }
        /**
         * Enqueue Astra Notices CSS.
         *
         * @since 3.1.17
         *
         * @return void
         */
        public static function notice_assets()
        {
        }
        /**
         * Display notice on dashboard if WP_Filesystem() false.
         *
         * @return void
         */
        public function check_filesystem_access_notice()
        {
        }
        /**
         * Remove query parameters from the URL.
         * 
         * @param  String   $url URL.
         * @param  String[] $params Query parameters.
         *
         * @return string       URL.
         */
        public function remove_query_params($url, $params)
        {
        }
    }
    /**
     * Astra_Sites_Error_Handler
     */
    class Astra_Sites_Error_Handler
    {
        /**
         * Instance of Astra_Sites_Error_Handler.
         *
         * @since  3.0.23
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         */
        public function __construct()
        {
        }
        /**
         * Stop the shutdown handlers.
         *
         * @return void
         */
        public function stop_handler()
        {
        }
        /**
         * Start the error handling.
         */
        public function start_error_handler()
        {
        }
        /**
         * Stop and restore the error handlers.
         */
        public function stop_error_handler()
        {
        }
        /**
         * Uncaught exception handler.
         *
         * In PHP >= 7 this will receive a Throwable object.
         * In PHP < 7 it will receive an Exception object.
         *
         * @throws Exception Exception that is catched.
         * @param Throwable|Exception $e The error or exception.
         */
        public function exception_handler($e)
        {
        }
        /**
         * Displays fatal error output for sites running PHP < 7.
         */
        public function shutdown_handler()
        {
        }
    }
    /**
     * Astra_Sites_Elementor_Images
     */
    class Astra_Sites_Elementor_Images
    {
        /**
         * Instance of Astra_Sites_Elementor_Images.
         *
         * @since  2.0.0
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Import Image.
         *
         * @since  2.0.0
         * @param array $image Downloaded Image array.
         */
        public function get_attachment_data($image)
        {
        }
    }
    /**
     * Admin
     */
    class Astra_Sites_Whats_New
    {
        /**
         * Get Instance
         *
         * @since x.x.x
         *
         * @return self Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Enqueue scripts in the admin area.
         *
         * @param string $hook Current screen hook.
         *
         * @return void
         */
        public function enqueue_scripts($hook = '')
        {
        }
    }
    /**
     * Admin
     */
    class Astra_Sites_Ast_Block_Templates
    {
        /**
         * Get Instance
         *
         * @since 1.0.0
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Version Check
         *
         * @return void
         */
        public function version_check()
        {
        }
        /**
         * Load latest plugin
         *
         * @return void
         */
        public function load()
        {
        }
    }
    /**
     * Admin
     */
    class Astra_Sites_Zipwp_Images
    {
        /**
         * Get Instance
         *
         * @since 1.0.0
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Update Tab title
         * 
         * @param string $title tab title.
         *
         * @return string
         */
        public function update_image_library_title($title)
        {
        }
        /**
         * Version Check
         *
         * @return void
         */
        public function version_check()
        {
        }
        /**
         * Load latest plugin
         *
         * @return void
         */
        public function load()
        {
        }
    }
    /**
     * Admin
     */
    class Astra_Sites_Zip_AI
    {
        /**
         * Get Instance
         *
         * @since 4.0.4
         *
         * @return object Class object.
         */
        public static function get_instance()
        {
        }
        /**
         * Checks for latest version of zip-ai library available in environment.
         *
         * @since 4.0.4
         *
         * @return void
         */
        public function version_check()
        {
        }
        /**
         * Load latest zip-ai library
         *
         * @since 4.0.4
         *
         * @return void
         */
        public function load()
        {
        }
    }
    /**
     * ZipWP Helper.
     *
     * @package {{package}}
     * @since 4.0.0
     */
    /**
     * Importer Helper
     *
     * @since 4.0.0
     */
    class Astra_Sites_ZipWP_Helper
    {
        /**
         * Get Saved Token.
         * 
         * @since 4.0.0
         * @return string
         */
        public static function get_token()
        {
        }
        /**
         * Get Saved Auth Token.
         * 
         * @since 4.1.0
         * @return string
         */
        public static function get_auth_token()
        {
        }
        /**
         * Get Saved ZipWP user email.
         * 
         * @since 4.0.0
         * @return string
         */
        public static function get_zip_user_email()
        {
        }
        /**
         * Get Saved settings.
         * 
         * @since 4.0.0
         * @return string
         */
        public static function get_setting()
        {
        }
        /**
         * Encrypt data using base64.
         *
         * @param string $input The input string which needs to be encrypted.
         * @since 4.0.0
         * @return string The encrypted string.
         */
        public static function encrypt($input)
        {
        }
        /**
         * Decrypt data using base64.
         *
         * @param string $input The input string which needs to be decrypted.
         * @since 4.0.0
         * @return string The decrypted string.
         */
        public static function decrypt($input)
        {
        }
        /**
         * Get Business details.
         *
         * @since 4.0.0
         * @param string $key options name.
         * @return array<string,string,string,string,string,string,string,int> | string Array for business details or single detail in a string.
         */
        public static function get_business_details($key = '')
        {
        }
        /**
         * Get image placeholder array.
         *
         * @since 4.0.9
         * @return array<string, array<string, string>>
         */
        public static function get_image_placeholders()
        {
        }
        /**
         * Download image from URL.
         *
         * @param array $image Image data.
         * @return int|\WP_Error Image ID or WP_Error.
         * @since {{since}}
         */
        public static function download_image($image)
        {
        }
    }
    /**
     * Reporting error
     */
    class Astra_Sites_Reporting
    {
        /**
         * Initiator
         *
         * @since 3.1.4
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 3.1.4
         */
        public function __construct()
        {
        }
        /**
         * Schedule the reporting of Error.
         *
         * @since 3.1.4
         * @return void
         */
        public function schedule_reporting_event()
        {
        }
        /**
         * Send Error.
         *
         * @since 3.1.4
         * @return void
         */
        public function send_analytics_lead()
        {
        }
        /**
         * Report Error.
         * 
         * @param array $data Error data.
         * @since 3.1.4
         */
        public function report($data)
        {
        }
    }
    /**
     * Replace Images
     */
    class Astra_Sites_Replace_Images
    {
        /**
         * Image index
         *
         * @since 4.1.0
         * @var array<string,int>
         */
        public static $image_index = 0;
        /**
         * Old Images ids
         * 
         * @var array<int,int>
         * @since 4.1.0
         */
        public static $old_image_urls = array();
        /**
         * Reusable block tracking.
         * 
         * @var array<int,int>
         */
        public static $reusable_blocks = array();
        /**
         * Filtered images.
         * 
         * @var array<string, array<string, string>>
         */
        public static $filtered_images = array();
        /**
         * Initiator
         *
         * @since 3.1.4
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 3.1.4
         */
        public function __construct()
        {
        }
        /**
         * Download Images
         *
         * @since 4.1.0
         * @return void
         */
        public function download_selected_image()
        {
        }
        /**
         * Replace images in pages.
         * @since 4.1.0
         * 
         * @retuen void
         */
        public function replace_images()
        {
        }
        /**
         * Replace images in post.
         * @since 4.1.0
         * 
         * @retuen void
         */
        public function replace_in_post()
        {
        }
        /** Parses images and other content in the Spectra Info Box block.
         *
         * @since {{since}}
         * @param \WP_Post $post Post.
         * @return void
         */
        public function parse_featured_image($post)
        {
        }
        /**
         * Cleanup the old images.
         * 
         * @return void
         * @since 4.1.0
         */
        public function cleanup()
        {
        }
        /**
         * Replace images in customizer.
         *
         * @since 4.1.0
         */
        public function replace_in_customizer()
        {
        }
        /**
         * Update the Social options
         *
         * @param array $options Social Options.
         * @since  {{since}}
         * @return void
         */
        public function update_social_options($options)
        {
        }
        /**
         * Gather all options eligible for replacement algorithm.
         * All elements placed in Header and Footer builder.
         *
         * @since  {{since}}
         * @return array $options Options.
         */
        public function get_options()
        {
        }
        /**
         * Updating the header and footer background image.
         *
         * @since 4.1.0
         * @param array<string,array<string,string>> $obj Reference of Block array.
         * @return array<string,array<string,int|string>> $obj Updated Block array.
         */
        public function get_updated_astra_option($obj)
        {
        }
        /**
         * Replace the content with AI generated data in all Pages.
         *
         * @since 4.1.0
         * @return void
         */
        public function replace_in_pages()
        {
        }
        /**
         * Parse the content for potential AI based content.
         *
         * @since 4.1.0
         * @param string $content Post Content.
         * @return string $content Modified content.
         */
        public function parse_replace_images($content)
        {
        }
        /**
         * Update the Blocks with new mapping data.
         *
         * @since 4.1.0
         * @param array<mixed> $blocks Array of Blocks.
         * @return array<mixed> $blocks Modified array of Blocks.
         */
        public function get_updated_blocks(&$blocks)
        {
        }
        /**
         * Parse social icon list.
         *
         * @since {{since}}
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_social_icons($block)
        {
        }
        /**
         * Replace the image in the block if present from the AI generated images.
         *
         * @since 4.1.0
         * @param array<mixed> $block Reference of Block array.
         * @return void
         */
        public function replace_images_in_blocks(&$block)
        {
        }
        /**
         * Get pages.
         *
         * @return array<int|\WP_Post> Array for pages.
         * @param string $type Post type.
         * @since 4.1.0
         */
        public static function get_pages($type = 'page')
        {
        }
        /**
         * Parses Spectra form block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return void
         */
        public function parse_spectra_form($block)
        {
        }
        /**
         * Parses Google Map for the Spectra Google Map block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_spectra_google_map($block)
        {
        }
        /**
         * Parses images and other content in the Spectra Container block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_spectra_container($block)
        {
        }
        /**
         * Parses images and other content in the Spectra Info Box block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_spectra_infobox($block)
        {
        }
        /**
         * Parses images and other content in the Spectra Image block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_spectra_image($block)
        {
        }
        /**
         * Parses images and other content in the Spectra Info Box block.
         *
         * @since 4.1.0
         * @param array<mixed> $block Block.
         * @return array<mixed> $block Block.
         */
        public function parse_spectra_gallery($block)
        {
        }
        /**
         * Check if we need to skip the URL.
         *
         * @param string $url URL to check.
         * @return boolean
         * @since 4.1.0
         */
        public static function is_skipable($url)
        {
        }
        /**
         * Get Image for the specified index
         *
         * @param int    $index Index of the image.
         * @return array|boolean Array of images or false.
         * @since 4.1.0
         */
        public function get_image($index = 0)
        {
        }
        /**
         * Set Image as per oriantation
         *
         * @return void
         */
        public function set_images()
        {
        }
        /**
         * Increment Image index
         *
         *
         * @return void
         */
        public function increment_image_index()
        {
        }
        /**
         * Fix to alter the Astra global color variables.
         *
         * @since {{since}}
         * @param string $content Post Content.
         * @return string $content Modified content.
         */
        public function replace_content_glitch($content)
        {
        }
    }
    class Astra_Sites_ZipWP_Api
    {
        /**
         * Initiator
         *
         * @since 4.0.0
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 4.0.0
         */
        public function __construct()
        {
        }
        /**
         * Get api domain
         *
         * @since 4.0.0
         * @return string
         */
        public function get_api_domain()
        {
        }
        /**
         * Get api namespace
         *
         * @since 4.0.0
         * @return string
         */
        public function get_api_namespace()
        {
        }
        /**
         * Get API headers
         *
         * @since 4.0.0
         * @return array
         */
        public function get_api_headers()
        {
        }
        /**
         * Check whether a given request has permission to read notes.
         *
         * @param  object $request WP_REST_Request Full details about the request.
         * @return object|boolean
         */
        public function get_item_permissions_check($request)
        {
        }
        /**
         * Register route
         *
         * @since 4.0.0
         * @return void
         */
        public function register_route()
        {
        }
        /**
         * Get the zip plan details
         * @since 4.0.0
         */
        public function get_zip_plan_details()
        {
        }
        /**
         * Get User Credits.
         *
         * @param \WP_REST_Request $request Full details about the request.
         * @return mixed
         */
        public function get_user_credits($request)
        {
        }
        /**
         * Revoke access.
         *
         * @param \WP_REST_Request $request Full details about the request.
         * @return WP_REST_Response
         */
        public function revoke_access($request) : \WP_REST_Response
        {
        }
    }
    class Astra_Sites_ZipWP_Integration
    {
        /**
         * Initiator
         *
         * @since 4.0.0
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 4.0.0
         */
        public function __construct()
        {
        }
        /**
         * Check whether a given request has permission to read notes.
         *
         * @param  object $request WP_REST_Request Full details about the request.
         * @return object|boolean
         */
        public function get_items_permissions_check($request)
        {
        }
        /**
         * Register scripts.
         *
         * @return void
         * @since  4.0.0
         */
        public function register_preview_scripts()
        {
        }
        /**
         * Define Constants
         *
         * @since 4.0.0
         * @return void
         */
        public function define_constants() : void
        {
        }
        /**
         * Save auth token
         *
         * @since 4.0.0
         * @return void
         */
        public function save_auth_token()
        {
        }
        /**
         * Get ZIP Plans.
         */
        public function get_zip_plans()
        {
        }
    }
    /**
     * AI Site Setup
     */
    class Astra_Sites_Onboarding_Setup
    {
        /**
         * FSE logo attributes
         *
         * @since 3.3.0
         * @var (array) fse_logo_attributes
         */
        public static $fse_logo_attributes = [];
        /**
         * Initiator
         *
         * @since 3.0.0-beta.1
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 3.0.0-beta.1
         */
        public function __construct()
        {
        }
        /**
         * Prepare XML Data.
         *
         * @since 1.1.0
         * @return void
         */
        public function import_prepare_xml()
        {
        }
        /**
         * Delete transient for import process.
         *
         * @since 3.1.4
         * @return void
         */
        public function temporary_cache_errors($posted_data)
        {
        }
        /**
         * Delete transient for import process.
         *
         * @since 3.1.4
         * @return void
         */
        public function delete_transient_for_import_process()
        {
        }
        /**
         * Report Error.
         *
         * @since 3.0.0
         * @return void
         */
        public function report_error()
        {
        }
        /**
         * Get full path of the created log file.
         *
         * @return string File Path.
         * @since 3.0.25
         */
        public function get_log_file_path()
        {
        }
        /**
         * Get installed PHP version.
         *
         * @return float PHP version.
         * @since 3.0.16
         */
        public function get_php_version()
        {
        }
        /**
         * Set site related data.
         *
         * @since 3.0.0-beta.1
         * @return void
         */
        public function set_site_data()
        {
        }
        /**
         * Set FSE site related data.
         *
         * @since 3.3.0
         * @return void
         */
        public function set_fse_site_data()
        {
        }
        /**
         * Set FSE site related data.
         *
         * @since 3.3.0
         * @return void
         */
        public function update_fse_site_logo($post_name)
        {
        }
    }
    /**
     * Astra Sites Importer
     */
    class Intelligent_Starter_Templates_Loader
    {
        /**
         * Initiator
         *
         * @since 3.0.0-beta.1
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor.
         *
         * @since  3.0.0-beta.1
         */
        public function __construct()
        {
        }
        /**
         * Add main menu
         *
         * @since 3.0.0-beta.1
         */
        public function admin_menu()
        {
        }
        /**
         * Menu callback
         *
         * @since 3.0.0-beta.1
         */
        public function menu_callback()
        {
        }
        /**
         * Admin Body Classes
         *
         * @since 3.0.0-beta.1
         * @param string $classes Space separated class string.
         */
        public function admin_body_class($classes = '')
        {
        }
        /**
         * Enqueue scripts in the admin area.
         *
         * @param string $hook Current screen hook.
         *
         * @return void
         */
        public function enqueue_scripts($hook = '')
        {
        }
        /**
         * Check if we should report error or not.
         * Skipping error reporting for a few hosting providers.
         */
        public function should_report_error()
        {
        }
        /**
         * Genereate and return the Google fonts url.
         *
         * @since 3.0.0-beta.1
         * @return string
         */
        public function google_fonts_url()
        {
        }
        /**
         * Register page builder templates flag.
         *
         * @return void
         */
        public function page_builder_field()
        {
        }
        /**
         * Enable page builder templates flag markup.
         *
         * @return void
         */
        // page_builders_enable_disable_option
        public function page_builders_enable_disable_option()
        {
        }
    }
    /**
     * Astra_Pro_Sites_White_Label
     *
     * @since 1.0.0
     */
    class Astra_Pro_Sites_White_Label
    {
        /**
         * Initiator
         *
         * @since 1.0.0
         * @return object initialized object of class.
         */
        public static function set_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Get value of single key from option array.
         *
         * @since 1.0.0
         * @param  string $type Option type.
         * @param  string $key  Option key.
         * @param  string $default  Default value if key not found.
         * @return mixed        Return stored option value.
         */
        public static function get_option($type = '', $key = '', $default = \null)
        {
        }
        /**
         * White labels the plugins page.
         *
         * @param array $plugins Plugins Array.
         * @return array
         */
        public function plugins_page($plugins)
        {
        }
    }
    /**
     * Astra Pro Sites
     *
     * @since 1.0.0
     */
    class Astra_Pro_Sites
    {
        /**
         * Instance of Astra_Pro_Sites.
         *
         * @since 1.0.0
         *
         * @return object Class object.
         */
        public static function set_instance()
        {
        }
        /**
         * API Request Params
         *
         * @since 1.0.5
         *
         * @param  array $args API request arguments.
         * @return arrray       Filtered API request params.
         */
        public function api_request_params($args = array())
        {
        }
        /**
         * Page Title
         *
         * @since 1.0.0
         *
         * @param  string $title Page Title.
         * @return string        Filtered Page Title.
         */
        public function page_title($title = '')
        {
        }
        /**
         * Update Vars
         *
         * @since 1.0.0
         *
         * @param  array $vars Localize variables.
         * @return array        Filtered localize variables.
         */
        public function update_vars($vars = array())
        {
        }
        /**
         * Loads textdomain for the plugin.
         *
         * @since 1.0.0
         */
        public function load_textdomain()
        {
        }
        /**
         * Admin Notices
         *
         * @since 1.0.0
         * @return void
         */
        public function admin_notices()
        {
        }
        /**
         * Show action links on the plugin screen.
         *
         * @since 1.0.0
         *
         * @param   mixed $links Plugin Action links.
         * @return  array        Filtered plugin action links.
         */
        public function action_links($links = array())
        {
        }
    }
    /**
     * Astra Pro Sites Update
     *
     * @since 1.0.0
     */
    class Astra_Pro_Sites_Update
    {
        /**
         * Initiator
         *
         * @since 1.0.0
         * @return object initialized object of class.
         */
        public static function set_instance()
        {
        }
        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Update
         *
         * @since 1.0.0
         * @return void
         */
        public static function init()
        {
        }
        /**
         * Update white label branding of older version than 1.0.0-rc.8.
         *
         * @since 1.0.0
         * @return void
         */
        public static function v_1_0_0_rc_9()
        {
        }
    }
    /**
     * Brainstorm Update
     */
    class Brainstorm_Update_Astra_Pro_Sites
    {
        /**
         * Initiator
         */
        public static function get_instance()
        {
        }
        /**
         * Constructor
         */
        public function __construct()
        {
        }
        /**
         * License Activate
         *
         * @since 2.0.0
         * @return void
         */
        public function activate_or_deactivate_license()
        {
        }
        /**
         * After License Update
         * Show action links on the plugin screen.
         *
         * Set the default page builder ID to load it by default.
         *
         * @since 1.2.4
         * @param   mixed $links Plugin Action links.
         * @return  array        Filtered plugin action links.
         */
        public function license_form_and_links($links = array())
        {
        }
        /**
         * License Notice
         *
         * @since 1.2.4 Updated the license form message if the white label is not set.
         * @since 1.0.0
         *
         * @param  string $purchase_nag Product Purchase nag.
         * @return string               Purchase nag.
         */
        public function license_notice($purchase_nag)
        {
        }
        /**
         * Product Activation Link
         *
         * @since 1.0.0
         *
         * @param  string $message      Activation notice message.
         * @param  string $url          Product activation link.
         * @param  string $product_name Product Name.
         * @return mixed               Activation notice.
         */
        public function activation_notice($message = '', $url = '', $product_name = '')
        {
        }
        /**
         * Update brainstorm product version and product path.
         *
         * @return void
         */
        public function version_check()
        {
        }
        /**
         * Remove bundled products for Astra Pro Sites.
         * For Astra Pro Sites the bundled products are only used for one click plugin installation when importing the Astra Site.
         * License Validation and product updates are managed separately for all the products.
         *
         * @since 1.0.0
         *
         * @param  array  $product_parent  Array of parent product ids.
         * @param  String $bsf_product    Product ID or  Product init or Product name based on $search_by.
         * @param  String $search_by      Reference to search by id | init | name of the product.
         *
         * @return array                 Array of parent product ids.
         */
        public function remove_astra_pro_bundled_products($product_parent, $bsf_product, $search_by)
        {
        }
        /**
         * Load the brainstorm updater.
         *
         * @return void
         */
        public function load()
        {
        }
        /**
         * Install Pluigns Filter
         *
         * Add brainstorm bundle products in plugin installer list though filter.
         *
         * @since 1.0.0
         *
         * @param  array $brainstrom_products   Brainstorm Products.
         * @return array                        Brainstorm Products merged with Brainstorm Bundle Products.
         */
        public function plugin_information($brainstrom_products = array())
        {
        }
        /**
         * License Form Link
         *
         * @since 1.0.0
         *
         * @param  string $link License form link.
         * @return string       Popup License form link.
         */
        public function license_form_link($link = '')
        {
        }
        /**
         * License Form Text.
         *
         * @since 1.0.0
         *
         * @param  string $form_heading         Form Heading.
         * @param  string $license_status_class Form status class.
         * @param  string $license_status       Form status.
         * @return mixed                        HTML markup of the license form heading.
         */
        public function license_form_titles($form_heading = '', $license_status_class = '', $license_status = '')
        {
        }
        /**
         * Skip Menu.
         *
         * @param array $products products.
         * @return array $products updated products.
         */
        public function skip_menu($products)
        {
        }
    }
}
namespace {
    /**
     * Error Log
     *
     * A wrapper function for the error_log() function.
     *
     * @since 2.0.0
     *
     * @param  mixed $message Error message.
     * @return void
     */
    function astra_sites_error_log($message = '')
    {
    }
    /**
     *
     * Get suggestion link.
     *
     * @since 2.6.1
     *
     * @return suggestion link.
     */
    function astra_sites_get_suggestion_link()
    {
    }
    /**
     * Check for the valid image
     *
     * @param string $link  The Image link.
     *
     * @since 2.6.2
     * @return boolean
     */
    function astra_sites_is_valid_image($link = '')
    {
    }
    /**
     * Returns the value of the index for the Site Data
     *
     * @param string $index  The index value of the data.
     *
     * @since 2.6.14
     * @return mixed
     */
    function astra_get_site_data($index = '')
    {
    }
    /**
     * Get all the forms to be reset.
     *
     * @since 3.0.3
     * @return array
     */
    function astra_sites_get_reset_form_data()
    {
    }
    /**
     * Get all the terms to be reset.
     *
     * @since 3.0.3
     * @return array
     */
    function astra_sites_get_reset_term_data()
    {
    }
    /**
     * Remove the post excerpt
     *
     * @param int $post_id  The post ID.
     * @since 3.1.0
     */
    function astra_sites_empty_post_excerpt($post_id = 0)
    {
    }
    \define('INTELLIGENT_TEMPLATES_FILE', __FILE__);
    \define('INTELLIGENT_TEMPLATES_BASE', \plugin_basename(\INTELLIGENT_TEMPLATES_FILE));
    \define('INTELLIGENT_TEMPLATES_DIR', \plugin_dir_path(\INTELLIGENT_TEMPLATES_FILE));
    \define('INTELLIGENT_TEMPLATES_URI', \plugins_url('/', \INTELLIGENT_TEMPLATES_FILE));
    /**
     * Display notice if PHP version is below 7.4
     */
    function astra_pro_sites_php_version_notice()
    {
    }
    \define('ASTRA_PRO_SITES_NAME', \__('Premium Starter Templates', 'astra-sites'));
    \define('ASTRA_PRO_SITES_VER', '4.2.5');
    \define('ASTRA_PRO_SITES_FILE', __FILE__);
    \define('ASTRA_PRO_SITES_BASE', \plugin_basename(\ASTRA_PRO_SITES_FILE));
    \define('ASTRA_PRO_SITES_DIR', \plugin_dir_path(\ASTRA_PRO_SITES_FILE));
    \define('ASTRA_PRO_SITES_URI', \plugins_url('/', \ASTRA_PRO_SITES_FILE));
    /**
     * Astra Sites Setup
     *
     * @since 1.0.0
     * @return void
     */
    function astra_pro_sites_setup()
    {
    }
    /**
     * Fetch Bundled Products
     *
     * @since 1.1.2 Checking required plugins on `register_activation_hook` hook instead of `admin_init`.
     *
     * @since 1.0.0
     * @return void
     */
    function astra_pro_sites_fetch_bundled_products()
    {
    }
    /**
     * Redirect to onboarding.
     *
     * @since 3.3.0
     * @return void
     */
    function astra_sites_redirect_to_onboarding()
    {
    }
    /**
     * Astra pro sites activate.
     *
     * @since 4.1.2
     * @return void
     */
    function astra_pro_sites_activate()
    {
    }
    \define('ASTRA_SITES_NAME', \__('Starter Templates', 'astra-sites'));
    \define('ASTRA_SITES_VER', '4.2.5');
    \define('ASTRA_SITES_FILE', __FILE__);
    \define('ASTRA_SITES_BASE', \plugin_basename(\ASTRA_SITES_FILE));
    \define('ASTRA_SITES_DIR', \plugin_dir_path(\ASTRA_SITES_FILE));
    \define('ASTRA_SITES_URI', \plugins_url('/', \ASTRA_SITES_FILE));
    /**
     * Brainstorm_Update_Astra_Pro_Sites
     *
     * @package Astra
     * @since 1.0.0
     */
    // Ignore the PHPCS warning about constant declaration.
    // @codingStandardsIgnoreStart
    \define('BSF_REMOVE_astra-pro-sites_FROM_REGISTRATION_LISTING', \true);
}