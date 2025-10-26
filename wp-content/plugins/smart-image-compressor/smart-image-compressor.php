<?php
/**
 * Plugin Name: Smart Image Compressor
 * Plugin URI: https://bizfin-pro.ru/
 * Description: Умный плагин для автоматического сжатия изображений в WebP формат с сохранением всех SEO-атрибутов
 * Version: 1.0.0
 * Author: BizFin Pro
 * License: GPL v2 or later
 * Text Domain: smart-image-compressor
 */

// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы плагина
define('SIC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SIC_VERSION', '1.0.0');

/**
 * Основной класс плагина
 */
class SmartImageCompressor {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('sic_options', $this->get_default_options());
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_sic_compress_image', array($this, 'ajax_compress_image'));
        add_action('wp_ajax_sic_batch_compress', array($this, 'ajax_batch_compress'));
        add_action('add_attachment', array($this, 'auto_compress_on_upload'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Инициализация плагина
     */
    public function init() {
        load_plugin_textdomain('smart-image-compressor', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Получение настроек по умолчанию
     */
    private function get_default_options() {
        return array(
            'max_file_size' => 400, // KB
            'quality' => 85,
            'format' => 'webp',
            'auto_compress' => true,
            'preserve_metadata' => true,
            'backup_originals' => false
        );
    }
    
    /**
     * Добавление меню в админ-панель
     */
    public function add_admin_menu() {
        // Добавляем главное меню плагина
        add_menu_page(
            'Smart Image Compressor',
            'Smart Image Compressor',
            'manage_options',
            'smart-image-compressor',
            array($this, 'admin_page'),
            'dashicons-images-alt2',
            30
        );
        
        // Добавляем подменю для основной страницы
        add_submenu_page(
            'smart-image-compressor',
            'Сжатие изображений',
            'Сжатие изображений',
            'manage_options',
            'smart-image-compressor',
            array($this, 'admin_page')
        );
        
        // Добавляем подменю для настроек
        add_submenu_page(
            'smart-image-compressor',
            'Настройки Smart Image Compressor',
            'Настройки',
            'manage_options',
            'sic-settings',
            array($this, 'settings_page')
        );
        
        // Добавляем подменю для статистики
        add_submenu_page(
            'smart-image-compressor',
            'Статистика Smart Image Compressor',
            'Статистика',
            'manage_options',
            'sic-statistics',
            array($this, 'statistics_page')
        );
    }
    
    /**
     * Подключение скриптов для админ-панели
     */
    public function admin_scripts($hook) {
        if ($hook == 'media_page_smart-image-compressor' || $hook == 'settings_page_sic-settings') {
            wp_enqueue_script('sic-admin', SIC_PLUGIN_URL . 'assets/admin.js', array('jquery'), SIC_VERSION, true);
            wp_enqueue_style('sic-admin', SIC_PLUGIN_URL . 'assets/admin.css', array(), SIC_VERSION);
            wp_localize_script('sic-admin', 'sic_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sic_nonce'),
                'strings' => array(
                    'compressing' => 'Сжатие...',
                    'success' => 'Успешно сжато',
                    'error' => 'Ошибка сжатия',
                    'batch_processing' => 'Пакетная обработка...'
                )
            ));
        }
    }
    
    /**
     * Регистрация настроек
     */
    public function register_settings() {
        register_setting('sic_options', 'sic_options', array($this, 'validate_options'));
    }
    
    /**
     * Валидация настроек
     */
    public function validate_options($input) {
        $output = array();
        $output['max_file_size'] = absint($input['max_file_size']);
        $output['quality'] = min(100, max(1, absint($input['quality'])));
        $output['format'] = sanitize_text_field($input['format']);
        $output['auto_compress'] = isset($input['auto_compress']);
        $output['preserve_metadata'] = isset($input['preserve_metadata']);
        $output['backup_originals'] = isset($input['backup_originals']);
        return $output;
    }
    
    /**
     * Автоматическое сжатие при загрузке
     */
    public function auto_compress_on_upload($attachment_id) {
        if ($this->options['auto_compress']) {
            $this->compress_image($attachment_id);
        }
    }
    
    /**
     * Основная функция сжатия изображения
     */
    public function compress_image($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        // Получаем метаданные изображения
        $metadata = wp_get_attachment_metadata($attachment_id);
        $attachment = get_post($attachment_id);
        
        // Сохраняем SEO-атрибуты
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $title = $attachment->post_title;
        $description = $attachment->post_content;
        $caption = $attachment->post_excerpt;
        
        // Проверяем размер файла
        $file_size = filesize($file_path);
        $file_size_kb = round($file_size / 1024);
        
        if ($file_size_kb <= $this->options['max_file_size']) {
            return array('status' => 'skipped', 'message' => 'Файл уже достаточно мал');
        }
        
        // Создаем резервную копию если нужно
        if ($this->options['backup_originals']) {
            $backup_path = $file_path . '.backup';
            copy($file_path, $backup_path);
        }
        
        // Сжимаем изображение
        $result = $this->process_image($file_path, $attachment_id);
        
        if ($result['success']) {
            // Обновляем метаданные
            wp_update_attachment_metadata($attachment_id, $metadata);
            
            // Восстанавливаем SEO-атрибуты
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_title' => $title,
                'post_content' => $description,
                'post_excerpt' => $caption
            ));
            
            return array(
                'status' => 'success',
                'original_size' => $file_size_kb,
                'new_size' => $result['new_size'],
                'savings' => $file_size_kb - $result['new_size']
            );
        }
        
        return array('status' => 'error', 'message' => $result['error']);
    }
    
    /**
     * Обработка изображения
     */
    private function process_image($file_path, $attachment_id) {
        $image_info = getimagesize($file_path);
        
        if (!$image_info) {
            return array('success' => false, 'error' => 'Не удалось получить информацию об изображении');
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        $mime_type = $image_info['mime'];
        
        // Создаем изображение в зависимости от типа
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($file_path);
                break;
            default:
                return array('success' => false, 'error' => 'Неподдерживаемый формат изображения');
        }
        
        if (!$source) {
            return array('success' => false, 'error' => 'Не удалось создать изображение');
        }
        
        // Вычисляем новые размеры для достижения целевого размера
        $target_size_kb = $this->options['max_file_size'];
        $quality = $this->options['quality'];
        
        // Пробуем разные уровни качества
        $new_size = $this->calculate_optimal_size($source, $file_path, $target_size_kb, $quality);
        
        imagedestroy($source);
        
        return array('success' => true, 'new_size' => $new_size);
    }
    
    /**
     * Вычисление оптимального размера
     */
    private function calculate_optimal_size($source, $file_path, $target_size_kb, $quality) {
        $width = imagesx($source);
        $height = imagesy($source);
        
        // Пробуем разные уровни качества
        for ($q = $quality; $q >= 10; $q -= 10) {
            $temp_path = $file_path . '.temp';
            
            if (function_exists('imagewebp')) {
                imagewebp($source, $temp_path, $q);
            } else {
                // Fallback на JPEG если WebP не поддерживается
                imagejpeg($source, $temp_path, $q);
            }
            
            $temp_size = filesize($temp_path);
            $temp_size_kb = round($temp_size / 1024);
            
            if ($temp_size_kb <= $target_size_kb) {
                // Заменяем оригинальный файл
                rename($temp_path, $file_path);
                return $temp_size_kb;
            }
            
            unlink($temp_path);
        }
        
        // Если не удалось достичь целевого размера, используем минимальное качество
        $temp_path = $file_path . '.temp';
        if (function_exists('imagewebp')) {
            imagewebp($source, $temp_path, 10);
        } else {
            imagejpeg($source, $temp_path, 10);
        }
        
        rename($temp_path, $file_path);
        return round(filesize($file_path) / 1024);
    }
    
    /**
     * AJAX обработка сжатия одного изображения
     */
    public function ajax_compress_image() {
        check_ajax_referer('sic_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $result = $this->compress_image($attachment_id);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX обработка пакетного сжатия
     */
    public function ajax_batch_compress() {
        check_ajax_referer('sic_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $offset = intval($_POST['offset']);
        $limit = 5; // Обрабатываем по 5 изображений за раз
        
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'meta_query' => array(
                array(
                    'key' => '_sic_compressed',
                    'compare' => 'NOT EXISTS'
                )
            )
        );
        
        $attachments = get_posts($args);
        $results = array();
        
        foreach ($attachments as $attachment) {
            $result = $this->compress_image($attachment->ID);
            $results[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'result' => $result
            );
            
            // Отмечаем как обработанное
            update_post_meta($attachment->ID, '_sic_compressed', time());
        }
        
        wp_send_json(array(
            'processed' => count($attachments),
            'results' => $results,
            'has_more' => count($attachments) == $limit
        ));
    }
    
    /**
     * Страница админ-панели
     */
    public function admin_page() {
        include SIC_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    /**
     * Страница настроек
     */
    public function settings_page() {
        include SIC_PLUGIN_PATH . 'templates/settings-page.php';
    }
    
    /**
     * Страница статистики
     */
    public function statistics_page() {
        include SIC_PLUGIN_PATH . 'templates/statistics-page.php';
    }
}

// Инициализация плагина
new SmartImageCompressor();

// Активация плагина
register_activation_hook(__FILE__, 'sic_activate');
function sic_activate() {
    // Создаем таблицы или выполняем другие действия при активации
}

// Деактивация плагина
register_deactivation_hook(__FILE__, 'sic_deactivate');
function sic_deactivate() {
    // Очищаем временные файлы или выполняем другие действия при деактивации
}
?>
