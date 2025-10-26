<?php
/**
 * Plugin Name: BizFin ChatGPT Consultant
 * Plugin URI: https://bizfin-pro.ru
 * Description: Профессиональный ChatGPT консультант по банковским гарантиям с векторной базой данных, поддержкой файлов и админ-панелью
 * Version: 1.0.7
 * Author: BizFin Pro Team
 * Text Domain: bizfin-chatgpt-consultant
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

// Константы плагина
define('BCC_VERSION', '1.0.7');
define('BCC_PLUGIN_FILE', __FILE__);
define('BCC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BCC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BCC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// OpenAI API конфигурация (из существующих плагинов)
define('BCC_OPENAI_API_KEY', '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');
define('BCC_OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('BCC_DEFAULT_MODEL', 'gpt-4o');

// Настройки по умолчанию
define('BCC_DEFAULT_AGENT_NAME', 'Алексей');
define('BCC_DEFAULT_INSTRUCTIONS', 'Ты - Алексей, профессиональный финансовый консультант, специализирующийся на банковских гарантиях. Твоя задача - помогать клиентам с вопросами по банковским продуктам, банковским гарантиям и страхованию банковских гарантий. Ты можешь обсуждать только темы, связанные с банковскими продуктами, банковскими гарантиями и страхованием банковских гарантий. Если пользователь задает вопросы не по теме, вежливо объясни, что ты специализируешься только на банковских гарантиях и связанных темах.');

// Подключаем необходимые файлы
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-logger.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-database.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-vector-db.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-file-processor.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-openai-client.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-chat-handler.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-admin.php';
require_once BCC_PLUGIN_DIR . 'includes/class-bcc-shortcode.php';

/**
 * Основной класс плагина
 */
class BizFin_ChatGPT_Consultant {
    
    private static $instance = null;
    private $logger;
    private $database;
    private $vector_db;
    private $file_processor;
    private $openai_client;
    private $chat_handler;
    private $admin;
    private $shortcode;
    
    /**
     * Получить экземпляр класса (Singleton)
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        
        // AJAX хуки
        add_action('wp_ajax_bcc_send_message', [$this, 'ajax_send_message']);
        add_action('wp_ajax_nopriv_bcc_send_message', [$this, 'ajax_send_message']);
        add_action('wp_ajax_bcc_upload_file', [$this, 'ajax_upload_file']);
        add_action('wp_ajax_nopriv_bcc_upload_file', [$this, 'ajax_upload_file']);
        add_action('wp_ajax_bcc_get_chat_history', [$this, 'ajax_get_chat_history']);
        add_action('wp_ajax_nopriv_bcc_get_chat_history', [$this, 'ajax_get_chat_history']);
        
        // Поддержка шорткодов в Elementor
        add_action('elementor/widgets/widgets_registered', [$this, 'register_elementor_widgets']);
        add_filter('elementor/widget/render_content', [$this, 'render_shortcodes_in_elementor'], 10, 2);
        
        // Активация и деактивация
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Инициализация компонентов
     */
    private function init_components() {
        $this->logger = new BCC_Logger();
        $this->database = new BCC_Database();
        $this->vector_db = new BCC_Vector_DB();
        $this->file_processor = new BCC_File_Processor();
        $this->openai_client = new BCC_OpenAI_Client();
        $this->chat_handler = new BCC_Chat_Handler();
        $this->admin = new BCC_Admin();
        $this->shortcode = new BCC_Shortcode();
        
        $this->logger->info('BizFin ChatGPT Consultant plugin initialized');
    }
    
    /**
     * Инициализация плагина
     */
    public function init() {
        // Загружаем текстовый домен
        load_plugin_textdomain('bizfin-chatgpt-consultant', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Инициализируем компоненты
        $this->database->init();
        $this->vector_db->init();
        $this->file_processor->init();
        $this->openai_client->init();
        $this->chat_handler->init();
        $this->admin->init();
        $this->shortcode->init();
    }
    
    /**
     * Подключение скриптов и стилей для фронтенда
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'bcc-frontend-css',
            BCC_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            BCC_VERSION
        );
        
        wp_enqueue_script(
            'bcc-frontend-js',
            BCC_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            BCC_VERSION,
            true
        );
        
        // Локализация для JavaScript
        wp_localize_script('bcc-frontend-js', 'bcc_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bcc_nonce'),
            'strings' => [
                'loading' => __('Загрузка...', 'bizfin-chatgpt-consultant'),
                'error' => __('Произошла ошибка', 'bizfin-chatgpt-consultant'),
                'file_upload_error' => __('Ошибка загрузки файла', 'bizfin-chatgpt-consultant'),
                'invalid_file_type' => __('Неподдерживаемый тип файла', 'bizfin-chatgpt-consultant'),
                'file_too_large' => __('Файл слишком большой', 'bizfin-chatgpt-consultant'),
            ]
        ]);
    }
    
    /**
     * Подключение скриптов и стилей для админки
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'bizfin-chatgpt') === false) {
            return;
        }
        
        wp_enqueue_style(
            'bcc-admin-css',
            BCC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            BCC_VERSION
        );
        
        wp_enqueue_script(
            'bcc-admin-js',
            BCC_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            BCC_VERSION,
            true
        );
        
        wp_localize_script('bcc-admin-js', 'bcc_admin_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bcc_admin_nonce'),
        ]);
    }
    
    /**
     * AJAX обработчик отправки сообщения
     */
    public function ajax_send_message() {
        $this->logger->info('AJAX send_message called');
        
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_nonce')) {
            $this->logger->error('Invalid nonce in send_message');
            wp_send_json_error(['message' => 'Ошибка безопасности']);
        }
        
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error(['message' => 'Сообщение не может быть пустым']);
        }
        
        try {
            $response = $this->chat_handler->process_message($message, $session_id);
            wp_send_json_success($response);
        } catch (Exception $e) {
            $this->logger->error('Error in send_message: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Произошла ошибка при обработке сообщения']);
        }
    }
    
    /**
     * AJAX обработчик загрузки файла
     */
    public function ajax_upload_file() {
        $this->logger->info('AJAX upload_file called');
        
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_nonce')) {
            $this->logger->error('Invalid nonce in upload_file');
            wp_send_json_error(['message' => 'Ошибка безопасности']);
        }
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'Файл не найден']);
        }
        
        try {
            $result = $this->file_processor->process_upload($_FILES['file']);
            wp_send_json_success($result);
        } catch (Exception $e) {
            $this->logger->error('Error in upload_file: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * AJAX обработчик получения истории чата
     */
    public function ajax_get_chat_history() {
        $this->logger->info('AJAX get_chat_history called');
        
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_nonce')) {
            $this->logger->error('Invalid nonce in get_chat_history');
            wp_send_json_error(['message' => 'Ошибка безопасности']);
        }
        
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        try {
            $history = $this->chat_handler->get_chat_history($session_id);
            wp_send_json_success($history);
        } catch (Exception $e) {
            $this->logger->error('Error in get_chat_history: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Ошибка получения истории']);
        }
    }
    
    /**
     * Активация плагина
     */
    public function activate() {
        $this->logger->info('Plugin activation started');
        
        // Создаем таблицы базы данных
        $this->database->create_tables();
        
        // Создаем директории для загрузок
        $upload_dir = wp_upload_dir();
        $bcc_upload_dir = $upload_dir['basedir'] . '/bizfin-chatgpt';
        
        if (!file_exists($bcc_upload_dir)) {
            wp_mkdir_p($bcc_upload_dir);
        }
        
        // Создаем директории для разных типов файлов
        $subdirs = ['images', 'documents', 'pdfs', 'temp'];
        foreach ($subdirs as $subdir) {
            $dir = $bcc_upload_dir . '/' . $subdir;
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
        
        // Устанавливаем настройки по умолчанию
        $this->set_default_options();
        
        // Создаем .htaccess для защиты загруженных файлов
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "deny from all\n";
        $htaccess_content .= "<Files ~ \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx)$\">\n";
        $htaccess_content .= "allow from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($bcc_upload_dir . '/.htaccess', $htaccess_content);
        
        $this->logger->info('Plugin activation completed');
    }
    
    /**
     * Деактивация плагина
     */
    public function deactivate() {
        $this->logger->info('Plugin deactivation started');
        
        // Очищаем кеш
        wp_cache_flush();
        
        $this->logger->info('Plugin deactivation completed');
    }
    
    /**
     * Установка настроек по умолчанию
     */
    private function set_default_options() {
        $default_options = [
            'bcc_agent_name' => BCC_DEFAULT_AGENT_NAME,
            'bcc_instructions' => BCC_DEFAULT_INSTRUCTIONS,
            'bcc_model' => BCC_DEFAULT_MODEL,
            'bcc_max_tokens' => 2000,
            'bcc_temperature' => 0.7,
            'bcc_max_file_size' => 10485760, // 10MB
            'bcc_allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
            'bcc_enable_file_processing' => true,
            'bcc_enable_vector_db' => true,
            'bcc_session_timeout' => 3600, // 1 час
            'bcc_max_history_messages' => 50,
            'bcc_enable_logging' => true,
        ];
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Регистрация виджетов Elementor
     */
    public function register_elementor_widgets() {
        // Регистрируем виджет для ChatGPT консультанта
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Elementor\Widget_Shortcode()
        );
    }
    
    /**
     * Рендеринг шорткодов в Elementor
     */
    public function render_shortcodes_in_elementor($content, $widget) {
        // Проверяем, содержит ли контент наш шорткод
        if (strpos($content, '[bizfin_chatgpt') !== false || strpos($content, '[bizfin_chatgpt_consultant') !== false) {
            // Обрабатываем шорткоды
            $content = do_shortcode($content);
        }
        
        return $content;
    }
    
    /**
     * Получить компоненты плагина
     */
    public function get_logger() { return $this->logger; }
    public function get_database() { return $this->database; }
    public function get_vector_db() { return $this->vector_db; }
    public function get_file_processor() { return $this->file_processor; }
    public function get_openai_client() { return $this->openai_client; }
    public function get_chat_handler() { return $this->chat_handler; }
    public function get_admin() { return $this->admin; }
    public function get_shortcode() { return $this->shortcode; }
}

// Инициализируем плагин
function bizfin_chatgpt_consultant() {
    return BizFin_ChatGPT_Consultant::get_instance();
}

// Запускаем плагин
bizfin_chatgpt_consultant();
