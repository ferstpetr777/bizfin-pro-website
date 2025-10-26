<?php
/**
 * BizFin SEO Article Generator - Integration Manager
 * Централизованный менеджер интеграций для предотвращения конфликтов
 */

if (!defined('ABSPATH')) exit;

class BizFin_Integration_Manager {
    
    private static $instance = null;
    private $processed_posts = [];
    private $integration_queue = [];
    private $main_plugin; // Добавлено для доступа к основному плагину
    
    public static function get_instance($main_plugin = null) {
        if (self::$instance === null) {
            self::$instance = new self($main_plugin);
        } else if ($main_plugin !== null) {
            // Обновляем main_plugin если он передан
            self::$instance->main_plugin = $main_plugin;
        }
        return self::$instance;
    }
    
    private function __construct($main_plugin = null) {
        $this->main_plugin = $main_plugin; // Сохраняем ссылку на основной плагин
        
        // ОТКЛЮЧЕНО: Регистрируем единственный хук save_post
        // ABP Image Generator работает независимо через свой хук save_post
        // add_action('save_post', [$this, 'handle_save_post'], 5, 2);
        
        // Регистрируем обработчик отложенной интеграции
        add_action('bsag_delayed_integration', [$this, 'handle_delayed_integration'], 10, 1);
        
        // Регистрируем обработчики событий
        add_action('bsag_article_generated', [$this, 'handle_article_generated'], 5, 2);
        add_action('bsag_article_published', [$this, 'handle_article_published'], 5, 2);
    }
    
    /**
     * Централизованный обработчик save_post
     */
    public function handle_save_post($post_id, $post) {
        // Предотвращаем обработку ревизий
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Предотвращаем обработку автосохранений
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Проверяем, является ли пост сгенерированным нашим плагином
        $is_generated = get_post_meta($post_id, '_bsag_generated', true);
        
        if (!$is_generated) {
            return;
        }
        
        // Предотвращаем повторную обработку
        if (in_array($post_id, $this->processed_posts)) {
            return;
        }
        
        // Добавляем пост в список обработанных
        $this->processed_posts[] = $post_id;
        
        // ПРАВИЛЬНАЯ АРХИТЕКТУРА: Планируем отложенное выполнение
        // ABP Image Generator сначала создаст featured image, 
        // а затем через тайминг мы добавим изображение в контент
        $this->schedule_delayed_integration($post_id, $post);
    }
    
    /**
     * Планирование отложенной интеграции с таймингом
     */
    private function schedule_delayed_integration($post_id, $post) {
        // Планируем выполнение через 10 секунд после публикации
        // Это даст время ABP Image Generator создать featured image
        wp_schedule_single_event(
            time() + 10, // 10 секунд задержки
            'bsag_delayed_integration',
            [$post_id]
        );
        
        error_log("BizFin Integration Manager: Scheduled delayed integration for post {$post_id} in 10 seconds");
    }
    
    /**
     * Обработчик отложенной интеграции
     */
    public function handle_delayed_integration($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            error_log("BizFin Integration Manager: Post {$post_id} not found for delayed integration");
            return;
        }
        
        // Проверяем, есть ли featured image (созданный ABP Image Generator)
        if (!has_post_thumbnail($post_id)) {
            error_log("BizFin Integration Manager: No featured image found for post {$post_id}, retrying in 5 seconds");
            
            // Если featured image еще не создан, планируем повторную попытку
            wp_schedule_single_event(
                time() + 5, // 5 секунд задержки
                'bsag_delayed_integration',
                [$post_id]
            );
            return;
        }
        
        error_log("BizFin Integration Manager: Featured image found for post {$post_id}, proceeding with content image placement");
        
        // Теперь выполняем интеграции
        $this->execute_integrations($post_id, $post);
    }
    
    /**
     * Выполнение интеграций в правильном порядке
     */
    private function execute_integrations($post_id, $post) {
        // Фаза 1: Базовая SEO оптимизация (приоритет 10)
        $this->execute_seo_optimization($post_id, $post);
        
        // Фаза 2: ABP Quality Monitor (приоритет 20)
        $this->execute_abp_quality_check($post_id, $post);
        
        // Фаза 3: Alphabet Blog Panel (приоритет 30)
        $this->execute_alphabet_blog_integration($post_id, $post);
        
        // Фаза 4: ABP Image Generator (ОТКЛЮЧЕНО - работает независимо)
        // ABP Image Generator работает автономно через свой хук save_post
        // НЕ ВМЕШИВАЕМСЯ в его работу
        error_log("BizFin Integration Manager: ABP Image Generator works independently, skipping integration");
        
        // Фаза 5: Размещение изображения в контенте (ОТКЛЮЧЕНО)
        // ABP Image Generator сам управляет размещением изображений
        error_log("BizFin Integration Manager: Image placement handled by ABP Image Generator independently");
        
        // Фаза 6: Финальная оптимизация (приоритет 60)
        $this->execute_final_optimization($post_id, $post);
        
        // Запускаем событие завершения интеграций
        do_action('bsag_integrations_completed', $post_id, $post);
    }
    
    /**
     * Базовая SEO оптимизация
     */
    private function execute_seo_optimization($post_id, $post) {
        // Получаем данные статьи
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        
        if (!$article_data) {
            return;
        }
        
        $data = json_decode($article_data, true);
        
        // Обновляем мета-поля Yoast SEO
        if (class_exists('WPSEO_Options')) {
            update_post_meta($post_id, '_yoast_wpseo_title', $data['title'] ?? '');
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $data['meta_description'] ?? '');
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $data['keyword'] ?? '');
            update_post_meta($post_id, '_yoast_wpseo_canonical', get_permalink($post_id));
        }
        
        // Устанавливаем флаг SEO оптимизации
        update_post_meta($post_id, '_bsag_seo_optimized', true);
        
        // Логируем выполнение
        error_log("BizFin Integration Manager: SEO optimization completed for post {$post_id}");
    }
    
    /**
     * ABP Quality Monitor интеграция
     */
    private function execute_abp_quality_check($post_id, $post) {
        if (!class_exists('ABP_Article_Quality_Monitor')) {
            return;
        }
        
        // Запускаем проверку качества
        $quality_monitor = new ABP_Article_Quality_Monitor();
        $quality_monitor->check_post_quality($post_id, $post);
        
        // Устанавливаем флаг интеграции
        update_post_meta($post_id, '_bsag_abp_quality_checked', true);
        
        // Логируем выполнение
        error_log("BizFin Integration Manager: ABP Quality Check completed for post {$post_id}");
    }
    
    /**
     * Alphabet Blog Panel интеграция
     */
    private function execute_alphabet_blog_integration($post_id, $post) {
        if (!class_exists('ABP_Plugin')) {
            return;
        }
        
        // Получаем данные статьи
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        $data = json_decode($article_data, true);
        $keyword = $data['keyword'] ?? '';
        
        if (!empty($keyword)) {
            // Определяем первую букву ключевого слова
            $first_letter = mb_strtoupper(mb_substr($keyword, 0, 1, 'UTF-8'), 'UTF-8');
            
            // Сохраняем первую букву для ABP
            update_post_meta($post_id, 'abp_first_letter', $first_letter);
        }
        
        // Устанавливаем флаг интеграции
        update_post_meta($post_id, '_bsag_abp_integrated', true);
        
        // Логируем выполнение
        error_log("BizFin Integration Manager: Alphabet Blog Panel integration completed for post {$post_id}");
    }
    
    /**
     * ABP Image Generator интеграция
     */
    private function execute_abp_image_generation($post_id, $post) {
        if (!class_exists('ABP_Image_Generator')) {
            return;
        }
        
        // Проверяем, есть ли уже изображение
        if (has_post_thumbnail($post_id)) {
            error_log("BizFin Integration Manager: Post {$post_id} already has featured image");
            return;
        }
        
        // Запускаем генерацию изображения
        $image_generator = new ABP_Image_Generator();
        $image_generator->generate_image_for_post($post_id);
        
        // Устанавливаем флаг интеграции
        update_post_meta($post_id, '_bsag_abp_image_generated', true);
        
        // Логируем выполнение
        error_log("BizFin Integration Manager: ABP Image Generator integration completed for post {$post_id}");
    }
    
    /**
     * Размещение изображения в контенте (ПРАВИЛЬНАЯ АРХИТЕКТУРА)
     */
    private function execute_image_placement($post_id, $post) {
        if (!isset($this->main_plugin->image_placement_manager)) {
            error_log("BizFin Integration Manager: Image Placement Manager not available for post {$post_id}");
            return;
        }

        // Получаем featured image (уже созданный ABP Image Generator)
        $featured_image_id = get_post_thumbnail_id($post_id);
        if (!$featured_image_id) {
            error_log("BizFin Integration Manager: No featured image found for post {$post_id}");
            return;
        }

        // ПРАВИЛЬНАЯ ЛОГИКА:
        // 1. Featured image остается как главное изображение статьи (от ABP)
        // 2. Добавляем копию featured image в контент после оглавления
        // 3. НЕ удаляем featured image, НЕ создаем дублирование
        
        $content = $post->post_content;
        $updated_content = $this->main_plugin->image_placement_manager->insert_image_after_toc($content, $post_id);

        if ($content !== $updated_content) {
            // Обновляем контент поста
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $updated_content
            ]);

            // Отмечаем, что размещение исправлено
            update_post_meta($post_id, '_bsag_image_placement_fixed', true);

            error_log("BizFin Integration Manager: Content image placement completed for post {$post_id} (featured image preserved)");
        } else {
            error_log("BizFin Integration Manager: Content image already properly placed for post {$post_id}");
        }
    }
    
    /**
     * Финальная оптимизация
     */
    private function execute_final_optimization($post_id, $post) {
        // Обновляем статус интеграций
        update_post_meta($post_id, '_bsag_integrations_completed', true);
        update_post_meta($post_id, '_bsag_integration_timestamp', current_time('mysql'));
        
        // Логируем выполнение
        error_log("BizFin Integration Manager: Final optimization completed for post {$post_id}");
    }
    
    /**
     * Обработчик события генерации статьи
     */
    public function handle_article_generated($post_id, $article_data) {
        // Добавляем в очередь интеграций
        $this->integration_queue[] = [
            'post_id' => $post_id,
            'action' => 'generated',
            'data' => $article_data,
            'timestamp' => current_time('mysql')
        ];
        
        // Логируем событие
        error_log("BizFin Integration Manager: Article generated event received for post {$post_id}");
    }
    
    /**
     * Обработчик события публикации статьи
     */
    public function handle_article_published($post_id, $article_data) {
        // Добавляем в очередь интеграций
        $this->integration_queue[] = [
            'post_id' => $post_id,
            'action' => 'published',
            'data' => $article_data,
            'timestamp' => current_time('mysql')
        ];
        
        // Логируем событие
        error_log("BizFin Integration Manager: Article published event received for post {$post_id}");
    }
    
    /**
     * Получение статистики интеграций
     */
    public function get_integration_stats() {
        global $wpdb;
        
        $stats = [
            'total_generated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_generated' AND meta_value = '1'"),
            'seo_optimized' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_seo_optimized' AND meta_value = '1'"),
            'abp_quality_checked' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_abp_quality_checked' AND meta_value = '1'"),
            'abp_image_generated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_abp_image_generated' AND meta_value = '1'"),
            'abp_integrated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_abp_integrated' AND meta_value = '1'"),
            'integrations_completed' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_integrations_completed' AND meta_value = '1'")
        ];
        
        return $stats;
    }
    
    /**
     * Очистка обработанных постов (для отладки)
     */
    public function clear_processed_posts() {
        $this->processed_posts = [];
        error_log("BizFin Integration Manager: Processed posts list cleared");
    }
    
    /**
     * Получение очереди интеграций
     */
    public function get_integration_queue() {
        return $this->integration_queue;
    }
    
    /**
     * Очистка очереди интеграций
     */
    public function clear_integration_queue() {
        $this->integration_queue = [];
        error_log("BizFin Integration Manager: Integration queue cleared");
    }
}

// Инициализация менеджера интеграций
BizFin_Integration_Manager::get_instance();

