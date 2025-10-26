<?php
/**
 * Загрузчик стилей для статей плагина BizFin SEO Article Generator
 */

if (!defined('ABSPATH')) exit;

class BizFin_Article_Styles_Loader {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_article_styles']);
        add_action('wp_head', [$this, 'add_inline_styles']);
    }
    
    /**
     * Подключение стилей для статей
     */
    public function enqueue_article_styles() {
        if (is_single() && $this->should_load_article_styles()) {
            wp_enqueue_style(
                'bsag-article-styles',
                plugin_dir_url(__FILE__) . '../assets/css/article-styles.css',
                [],
                '1.0.0'
            );
        }
    }
    
    /**
     * Добавление инлайн стилей в head
     */
    public function add_inline_styles() {
        if (is_single() && $this->should_load_article_styles()) {
            echo '<style>
                /* Дополнительные стили для статей */
                .single-post .entry-content {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                }
                
                .single-post .entry-content .bsag-article {
                    padding: 0;
                }
                
                /* Переопределение стилей темы для статей */
                .single-post .entry-content h1,
                .single-post .entry-content h2,
                .single-post .entry-content h3,
                .single-post .entry-content h4 {
                    margin-top: 2rem;
                    margin-bottom: 1rem;
                }
                
                .single-post .entry-content p {
                    margin-bottom: 1rem;
                }
            </style>';
        }
    }
    
    /**
     * Проверка, нужно ли загружать стили для статьи
     */
    private function should_load_article_styles() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Проверяем, является ли пост сгенерированным плагином
        $is_generated = get_post_meta($post->ID, '_bsag_generated', true);
        $use_styles = get_post_meta($post->ID, '_bsag_use_article_styles', true);
        
        return $is_generated || $use_styles;
    }
}

// Инициализируем загрузчик стилей
new BizFin_Article_Styles_Loader();
?>
