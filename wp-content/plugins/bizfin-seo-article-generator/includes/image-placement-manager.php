<?php
/**
 * Менеджер размещения изображений для BizFin SEO Article Generator
 * 
 * Отвечает за правильное размещение изображений в статьях согласно матрице
 */

class BizFin_Image_Placement_Manager {
    
    private $main_plugin;
    private $placement_rules;
    
    public function __construct($main_plugin) {
        $this->main_plugin = $main_plugin;
        $this->load_placement_rules();
    }
    
    /**
     * Загружает правила размещения изображений из SEO матрицы
     */
    private function load_placement_rules() {
        $seo_matrix = $this->main_plugin->get_seo_matrix();
        $this->placement_rules = $seo_matrix['mandatory_image_placement'] ?? [];
    }
    
    /**
     * Вставляет изображение в контент после оглавления
     */
    public function insert_image_after_toc($content, $post_id) {
        if (!$this->placement_rules['enabled']) {
            return $content;
        }
        
        // Получаем featured image
        $featured_image_id = get_post_thumbnail_id($post_id);
        
        if (!$featured_image_id) {
            error_log("BizFin Image Placement Manager: No featured image found for post {$post_id}");
            return $content;
        }
        
        // Получаем URL изображения
        $image_url = wp_get_attachment_image_url($featured_image_id, 'large');
        $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
        
        if (!$image_url) {
            error_log("BizFin Image Placement Manager: Could not get image URL for post {$post_id}");
            return $content;
        }
        
        // Создаём HTML изображения
        $image_html = $this->create_image_html($image_url, $image_alt, $post_id);
        
        // Вставляем изображение после оглавления
        $content = $this->insert_image_after_toc_in_content($content, $image_html);
        
        return $content;
    }
    
    /**
     * Создаёт HTML для изображения с iOS-стилями
     */
    private function create_image_html($image_url, $image_alt, $post_id) {
        $post_title = get_the_title($post_id);
        
        // Если нет alt-текста, создаём его
        if (empty($image_alt)) {
            $image_alt = "Изображение для статьи: {$post_title}";
        }
        
        // Создаём HTML с iOS-стилями
        $html = '<figure class="article-featured-image ios-style-image">' . "\n";
        $html .= '  <img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" loading="lazy" width="960" height="540" />' . "\n";
        $html .= '  <figcaption>' . esc_html($image_alt) . '</figcaption>' . "\n";
        $html .= '</figure>' . "\n";
        
        return $html;
    }
    
    /**
     * Вставляет изображение в контент после оглавления
     */
    private function insert_image_after_toc_in_content($content, $image_html) {
        // Ищем блок оглавления
        $toc_pattern = '/(<nav class="toc">.*?<\/nav>)/s';
        
        if (preg_match($toc_pattern, $content, $matches)) {
            // Вставляем изображение после оглавления
            $content = str_replace($matches[1], $matches[1] . "\n\n" . $image_html, $content);
        } else {
            // Если оглавления нет, ищем первый H2 и вставляем перед ним
            $h2_pattern = '/(<h2[^>]*>.*?<\/h2>)/';
            if (preg_match($h2_pattern, $content, $matches)) {
                $content = str_replace($matches[1], $image_html . "\n\n" . $matches[1], $content);
            } else {
                // Если H2 тоже нет, вставляем в начало контента
                $content = $image_html . "\n\n" . $content;
            }
        }
        
        return $content;
    }
    
    /**
     * Проверяет, есть ли изображение в правильной позиции
     */
    public function validate_image_placement($content, $post_id) {
        $errors = [];
        
        // Проверяем наличие featured image
        $featured_image_id = get_post_thumbnail_id($post_id);
        if (!$featured_image_id) {
            $errors[] = "Отсутствует featured image для поста {$post_id}";
        }
        
        // Проверяем наличие изображения в контенте после оглавления
        if (!$this->has_image_after_toc($content)) {
            $errors[] = "Изображение не размещено после оглавления в контенте";
        }
        
        // Проверяем alt-текст
        if ($featured_image_id) {
            $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
            if (empty($image_alt)) {
                $errors[] = "Отсутствует alt-текст для изображения";
            }
        }
        
        return $errors;
    }
    
    /**
     * Проверяет, есть ли изображение после оглавления
     */
    private function has_image_after_toc($content) {
        // Ищем блок оглавления
        $toc_pattern = '/(<nav class="toc">.*?<\/nav>)/s';
        
        if (preg_match($toc_pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $toc_end = $matches[0][1] + strlen($matches[0][0]);
            $content_after_toc = substr($content, $toc_end);
            
            // Проверяем, есть ли изображение в контенте после оглавления
            return strpos($content_after_toc, '<img') !== false || strpos($content_after_toc, '<figure') !== false;
        }
        
        return false;
    }
    
    /**
     * Автоматически исправляет размещение изображения
     */
    public function auto_fix_image_placement($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $content = $post->post_content;
        $updated_content = $this->insert_image_after_toc($content, $post_id);
        
        if ($content !== $updated_content) {
            // Обновляем контент поста
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $updated_content
            ]);
            
            error_log("BizFin Image Placement Manager: Fixed image placement for post {$post_id}");
            return true;
        }
        
        return false;
    }
    
    /**
     * Обрабатывает событие создания featured image
     */
    public function handle_featured_image_created($post_id, $attachment_id) {
        // Автоматически вставляем изображение в контент
        $this->auto_fix_image_placement($post_id);
        
        // Добавляем alt-текст если его нет
        $this->ensure_image_alt_text($attachment_id, $post_id);
    }
    
    /**
     * Обеспечивает наличие alt-текста для изображения
     */
    private function ensure_image_alt_text($attachment_id, $post_id) {
        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        if (empty($image_alt)) {
            $post_title = get_the_title($post_id);
            $alt_text = "Изображение для статьи: {$post_title}";
            
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
            
            // Также обновляем описание изображения
            wp_update_post([
                'ID' => $attachment_id,
                'post_content' => "Изображение для статьи о банковских гарантиях"
            ]);
        }
    }
    
    /**
     * Получает статистику размещения изображений
     */
    public function get_image_placement_stats() {
        global $wpdb;
        
        $stats = [
            'total_posts' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'"),
            'posts_with_featured_image' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'post' AND p.post_status = 'publish' AND pm.meta_key = '_thumbnail_id'"),
            'posts_with_correct_placement' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bsag_image_placement_fixed' AND meta_value = '1'")
        ];
        
        $stats['placement_percentage'] = $stats['total_posts'] > 0 ? 
            round(($stats['posts_with_correct_placement'] / $stats['total_posts']) * 100, 2) : 0;
        
        return $stats;
    }
    
    /**
     * Массовое исправление размещения изображений
     */
    public function bulk_fix_image_placement($post_ids = []) {
        if (empty($post_ids)) {
            // Получаем все посты без правильного размещения изображений
            global $wpdb;
            $post_ids = $wpdb->get_col("
                SELECT p.ID 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = 'post' 
                AND p.post_status = 'publish' 
                AND pm.meta_key = '_thumbnail_id'
                AND p.ID NOT IN (
                    SELECT post_id 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_bsag_image_placement_fixed' 
                    AND meta_value = '1'
                )
            ");
        }
        
        $fixed_count = 0;
        
        foreach ($post_ids as $post_id) {
            if ($this->auto_fix_image_placement($post_id)) {
                update_post_meta($post_id, '_bsag_image_placement_fixed', true);
                $fixed_count++;
            }
        }
        
        return $fixed_count;
    }
}
