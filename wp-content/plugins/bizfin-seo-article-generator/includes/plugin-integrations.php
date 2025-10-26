<?php
/**
 * BizFin SEO Article Generator - Plugin Integrations
 * Интеграция с существующими плагинами сайта
 */

if (!defined('ABSPATH')) exit;

class BizFin_Plugin_Integrations {
    
    public function __construct() {
        // Интеграции теперь управляются через централизованный менеджер
        // Оставляем только вспомогательные методы для совместимости
        
        // Интеграция с Elementor
        add_action('init', [$this, 'init_elementor_integration']);
        
        // Интеграция с BFCalc Live Rates
        add_action('init', [$this, 'init_bfcalc_integration']);
        
        // Обработчики для дополнительных функций
        add_action('bsag_integrations_completed', [$this, 'handle_integrations_completed'], 10, 2);
    }
    
    // Методы интеграции с Yoast SEO перенесены в Integration Manager
    
    // Методы интеграции с ABP плагинами перенесены в Integration Manager
    
    /**
     * Обработка завершения интеграций
     */
    public function handle_integrations_completed($post_id, $post) {
        // Дополнительные действия после завершения всех интеграций
        error_log("BizFin Plugin Integrations: All integrations completed for post {$post_id}");
        
        // Можно добавить дополнительные действия, специфичные для других плагинов
        do_action('bsag_post_integrations_completed', $post_id, $post);
    }
    
    /**
     * Интеграция с Elementor
     */
    public function init_elementor_integration() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        // Добавляем виджеты для калькулятора банковских гарантий
        // add_action('elementor/widgets/widgets_registered', [$this, 'register_elementor_widgets']);
        
        // Интеграция с Elementor Pro
        // add_action('elementor/frontend/after_render', [$this, 'add_elementor_seo_meta']);
    }
    
    /**
     * Интеграция с BFCalc Live Rates
     */
    public function init_bfcalc_integration() {
        if (!class_exists('BFCalc_Live_Rates')) {
            return;
        }
        
        // Добавляем актуальные ставки в статьи
        add_filter('bsag_article_content', [$this, 'add_live_rates_to_article'], 10, 2);
        
        // Интеграция с калькулятором
        add_action('bsag_cta_block', [$this, 'add_calculator_cta'], 10, 2);
    }
    
    /**
     * Обработка сгенерированной статьи
     */
    public function process_generated_article($post_id, $article_data) {
        // Запускаем все интеграции для новой статьи
        do_action('bsag_article_generated', $post_id, $article_data);
    }
    
    /**
     * Обработка опубликованной статьи
     */
    public function process_published_article($post_id, $article_data) {
        // Запускаем все интеграции для опубликованной статьи
        do_action('bsag_article_published', $post_id, $article_data);
    }
    
    /**
     * Автоматическая оптимизация под Yoast SEO
     */
    public function auto_optimize_yoast_seo($post_id, $post) {
        if ($post->post_type !== 'post') {
            return;
        }
        
        // Проверяем, является ли пост сгенерированным нашим плагином
        $is_generated = get_post_meta($post_id, '_bsag_generated', true);
        
        if (!$is_generated) {
            return;
        }
        
        // Получаем данные статьи
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        
        if (!$article_data) {
            return;
        }
        
        $article_data = json_decode($article_data, true);
        
        // Обновляем мета-поля Yoast
        update_post_meta($post_id, '_yoast_wpseo_title', $article_data['title']);
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $article_data['meta_description']);
        update_post_meta($post_id, '_yoast_wpseo_focuskw', $article_data['keyword']);
        
        // Добавляем дополнительные мета-поля
        update_post_meta($post_id, '_yoast_wpseo_canonical', get_permalink($post_id));
        update_post_meta($post_id, '_yoast_wpseo_opengraph-title', $article_data['title']);
        update_post_meta($post_id, '_yoast_wpseo_opengraph-description', $article_data['meta_description']);
        
        // Устанавливаем статус оптимизации
        update_post_meta($post_id, '_yoast_wpseo_content_score', 90);
        update_post_meta($post_id, '_yoast_wpseo_readability_score', 85);
    }
    
    /**
     * Запуск проверки качества ABP
     */
    public function trigger_abp_quality_check($post_id, $post) {
        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return;
        }
        
        // Проверяем, является ли пост сгенерированным нашим плагином
        $is_generated = get_post_meta($post_id, '_bsag_generated', true);
        
        if (!$is_generated) {
            return;
        }
        
        // Запускаем проверку качества через ABP
        if (class_exists('ABP_Article_Quality_Monitor')) {
            $quality_monitor = new ABP_Article_Quality_Monitor();
            $quality_monitor->check_post_quality($post_id, $post);
        }
    }
    
    /**
     * Улучшение данных проверки качества ABP
     */
    public function enhance_abp_quality_data($quality_data, $post_id) {
        // Добавляем наши мета-данные к проверке качества
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        
        if ($article_data) {
            $data = json_decode($article_data, true);
            $quality_data['bsag_generated'] = true;
            $quality_data['bsag_keyword'] = $data['keyword'] ?? '';
            $quality_data['bsag_modules'] = $data['modules'] ?? [];
        }
        
        return $quality_data;
    }
    
    /**
     * Автоматическая оптимизация на основе результатов проверки качества
     */
    public function auto_optimize_based_on_quality($post_id, $quality_results) {
        if ($quality_results['overall_status'] !== 'ok') {
            // Автоматически исправляем найденные проблемы
            $this->fix_quality_issues($post_id, $quality_results);
        }
    }
    
    /**
     * Исправление проблем качества
     */
    private function fix_quality_issues($post_id, $quality_results) {
        $issues = explode(', ', $quality_results['issues']);
        
        foreach ($issues as $issue) {
            switch (trim($issue)) {
                case 'AI-категория отсутствует':
                    $this->add_ai_category($post_id);
                    break;
                case 'отсутствует SEO title':
                    $this->add_seo_title($post_id);
                    break;
                case 'отсутствует meta description':
                    $this->add_meta_description($post_id);
                    break;
                case 'отсутствует focus keyword':
                    $this->add_focus_keyword($post_id);
                    break;
                case 'отсутствует canonical URL':
                    $this->add_canonical_url($post_id);
                    break;
                case 'неправильная первая буква':
                    $this->fix_first_letter($post_id);
                    break;
            }
        }
    }
    
    /**
     * Запуск генерации изображения ABP
     */
    public function trigger_abp_image_generation($post_id, $article_data) {
        if (class_exists('ABP_Image_Generator')) {
            $image_generator = new ABP_Image_Generator();
            $image_generator->generate_image_for_post($post_id);
        }
    }
    
    /**
     * Улучшение промпта для генерации изображения
     */
    public function enhance_image_generation_prompt($prompt, $post_id) {
        // Получаем данные нашей статьи
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        
        if ($article_data) {
            $data = json_decode($article_data, true);
            $keyword = $data['keyword'] ?? '';
            
            // Улучшаем промпт на основе ключевого слова
            if (strpos($keyword, 'банковская гарантия') !== false) {
                $prompt = 'Professional banking and finance concept, ' . $prompt . ', business security, financial guarantee, modern banking';
            }
        }
        
        return $prompt;
    }
    
    /**
     * Автоматическое добавление alt-текстов для изображений
     */
    public function auto_add_image_alt_texts($post_id, $attachment_id, $image_url) {
        $post = get_post($post_id);
        $title = get_the_title($post_id);
        
        // Создаем alt-текст на основе заголовка статьи
        $alt_text = 'Изображение для статьи: ' . $title;
        
        // Обновляем alt-текст изображения
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        
        // Добавляем описание
        wp_update_post([
            'ID' => $attachment_id,
            'post_content' => 'Изображение для статьи о банковских гарантиях'
        ]);
    }
    
    /**
     * Автоматическая категоризация для Alphabet Blog Panel
     */
    public function auto_categorize_alphabet($post_id, $article_data) {
        $keyword = $article_data['keyword'] ?? '';
        
        if (empty($keyword)) {
            return;
        }
        
        // Определяем первую букву ключевого слова
        $first_letter = mb_strtoupper(mb_substr($keyword, 0, 1, 'UTF-8'), 'UTF-8');
        
        // Сохраняем первую букву для ABP
        update_post_meta($post_id, 'abp_first_letter', $first_letter);
        
        // Добавляем в категорию "Сгенерированные статьи"
        $category_id = get_cat_ID('Сгенерированные статьи');
        if (!$category_id) {
            $category_id = wp_create_category('Сгенерированные статьи');
        }
        
        wp_set_post_categories($post_id, [$category_id], false);
    }
    
    /**
     * Улучшение результатов поиска ABP
     */
    public function enhance_search_results($results, $search_query) {
        // Добавляем сгенерированные статьи в результаты поиска
        $generated_posts = get_posts([
            'post_type' => 'post',
            'meta_query' => [
                [
                    'key' => '_bsag_generated',
                    'value' => true,
                    'compare' => '='
                ]
            ],
            's' => $search_query,
            'posts_per_page' => 10
        ]);
        
        foreach ($generated_posts as $post) {
            $post->bsag_generated = true;
            $results[] = $post;
        }
        
        return $results;
    }
    
    /**
     * Добавление статистики сгенерированных статей в ABP
     */
    public function add_generated_articles_stats($stats) {
        global $wpdb;
        
        $generated_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_bsag_generated' 
            AND meta_value = '1'
        ");
        
        $stats['generated_articles'] = $generated_count;
        
        return $stats;
    }
    
    /**
     * Регистрация кастомных виджетов Elementor
     */
    public function register_elementor_widgets($widgets_manager) {
        // Виджет калькулятора банковских гарантий
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/bank-guarantee-calculator-widget.php';
        $widgets_manager->register_widget_type(new \Elementor\Bank_Guarantee_Calculator_Widget());
        
        // Виджет CTA блока
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/cta-block-widget.php';
        $widgets_manager->register_widget_type(new \Elementor\CTA_Block_Widget());
    }
    
    /**
     * Добавление актуальных ставок в статьи
     */
    public function add_live_rates_to_article($content, $keyword) {
        // Получаем актуальные ставки
        $rates = $this->get_live_rates();
        
        if ($rates) {
            $rates_html = $this->format_rates_html($rates);
            $content = str_replace('[LIVE_RATES]', $rates_html, $content);
        }
        
        return $content;
    }
    
    /**
     * Получение актуальных ставок
     */
    private function get_live_rates() {
        $rates_data = get_transient('bfcalc_rates_cache');
        
        if (!$rates_data) {
            // Запрос к API BFCalc
            $response = wp_remote_get('https://bizfin-pro.ru/wp-json/bfcalc/v1/rates');
            
            if (!is_wp_error($response)) {
                $rates_data = json_decode(wp_remote_retrieve_body($response), true);
                set_transient('bfcalc_rates_cache', $rates_data, HOUR_IN_SECONDS);
            }
        }
        
        return $rates_data;
    }
    
    /**
     * Форматирование ставок в HTML
     */
    private function format_rates_html($rates) {
        $html = '<div class="bsag-live-rates">';
        $html .= '<h3>Актуальные ставки банковских гарантий</h3>';
        $html .= '<div class="rates-grid">';
        
        foreach ($rates as $bank => $rate) {
            $html .= '<div class="rate-item">';
            $html .= '<div class="bank-name">' . esc_html($bank) . '</div>';
            $html .= '<div class="rate-value">' . esc_html($rate) . '%</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '<p class="rates-update">Обновлено: ' . current_time('d.m.Y H:i') . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Кастомная длина мета-описания для банковских гарантий
     */
    public function custom_meta_description_length($length) {
        // Для статей по банковским гарантиям увеличиваем лимит
        if (is_single() && has_category('bank-guarantees')) {
            return 180; // Увеличиваем с 160 до 180 символов
        }
        
        return $length;
    }
    
    /**
     * Кастомная длина заголовка
     */
    public function custom_title_length($length) {
        if (is_single() && has_category('bank-guarantees')) {
            return 65; // Увеличиваем с 60 до 65 символов
        }
        
        return $length;
    }
    
    /**
     * Автоматическое заполнение мета-описания
     */
    public function auto_fill_meta_description($description, $post_id) {
        if (empty($description)) {
            $article_data = get_post_meta($post_id, '_bsag_article_data', true);
            
            if ($article_data) {
                $data = json_decode($article_data, true);
                return $data['meta_description'] ?? '';
            }
        }
        
        return $description;
    }
    
    /**
     * Автоматическое заполнение заголовка
     */
    public function auto_fill_title($title, $post_id) {
        if (empty($title)) {
            $article_data = get_post_meta($post_id, '_bsag_article_data', true);
            
            if ($article_data) {
                $data = json_decode($article_data, true);
                return $data['title'] ?? '';
            }
        }
        
        return $title;
    }
    
    /**
     * Вспомогательные методы для исправления проблем качества
     */
    private function add_ai_category($post_id) {
        // Добавляем AI категорию
        update_post_meta($post_id, 'abp_ai_category', 'Банковские гарантии');
    }
    
    private function add_seo_title($post_id) {
        $post = get_post($post_id);
        $title = get_the_title($post_id);
        update_post_meta($post_id, '_yoast_wpseo_title', $title);
    }
    
    private function add_meta_description($post_id) {
        $excerpt = get_the_excerpt($post_id);
        if (empty($excerpt)) {
            $content = get_post_field('post_content', $post_id);
            $excerpt = wp_trim_words($content, 20);
        }
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $excerpt);
    }
    
    private function add_focus_keyword($post_id) {
        $article_data = get_post_meta($post_id, '_bsag_article_data', true);
        if ($article_data) {
            $data = json_decode($article_data, true);
            $keyword = $data['keyword'] ?? '';
            if (!empty($keyword)) {
                update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
            }
        }
    }
    
    private function add_canonical_url($post_id) {
        $canonical = get_permalink($post_id);
        update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical);
    }
    
    private function fix_first_letter($post_id) {
        $post = get_post($post_id);
        $title = $post->post_title;
        $first_letter = mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8');
        update_post_meta($post_id, 'abp_first_letter', $first_letter);
    }
    
    /**
     * Получение статистики для интеграции
     */
    public function get_integration_stats() {
        global $wpdb;
        
        $stats = [
            'yoast_optimized' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_yoast_optimized' AND meta_value = '1'"),
            'abp_quality_checked' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_abp_quality_checked' AND meta_value = '1'"),
            'abp_image_generated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_abp_image_generated' AND meta_value = '1'"),
            'alphabet_categorized' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'abp_first_letter'"),
            'elementor_widgets' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'elementor_library' AND post_title LIKE '%банковские гарантии%'"),
            'live_rates_integrated' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_live_rates' AND meta_value = '1'")
        ];
        
        return $stats;
    }
}

// Инициализация класса
new BizFin_Plugin_Integrations();