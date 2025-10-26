<?php
/**
 * BizFin Gutenberg Block Manager
 * 
 * Управляет точечными изменениями в статьях через Gutenberg блоки
 * вместо полной перезаписи контента
 */

if (!defined('ABSPATH')) {
    exit;
}

class BizFin_Gutenberg_Block_Manager {
    
    private $main_plugin;
    
    public function __construct($main_plugin) {
        $this->main_plugin = $main_plugin;
        add_action('init', [$this, 'register_custom_blocks']);
        add_action('wp_ajax_bsag_update_block', [$this, 'ajax_update_block']);
        add_action('wp_ajax_bsag_remove_duplicate_images', [$this, 'ajax_remove_duplicate_images']);
    }
    
    /**
     * Регистрация кастомных Gutenberg блоков
     */
    public function register_custom_blocks() {
        // Регистрируем блок для вводных секций
        register_block_type('bizfin/intro-section', [
            'attributes' => [
                'simpleDefinition' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'sympatheticExample' => [
                    'type' => 'string', 
                    'default' => ''
                ],
                'tocContent' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ],
            'render_callback' => [$this, 'render_intro_section_block']
        ]);
        
        // Регистрируем блок для изображений с правильными стилями
        register_block_type('bizfin/article-image', [
            'attributes' => [
                'imageUrl' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'altText' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'caption' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'position' => [
                    'type' => 'string',
                    'default' => 'after_toc'
                ]
            ],
            'render_callback' => [$this, 'render_article_image_block']
        ]);
        
        // Регистрируем блок для контентных секций
        register_block_type('bizfin/content-section', [
            'attributes' => [
                'sectionId' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'sectionTitle' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'sectionContent' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'hasExample' => [
                    'type' => 'boolean',
                    'default' => false
                ]
            ],
            'render_callback' => [$this, 'render_content_section_block']
        ]);
    }
    
    /**
     * Рендер блока вводной секции
     */
    public function render_intro_section_block($attributes) {
        $simple_definition = $attributes['simpleDefinition'] ?? '';
        $sympathetic_example = $attributes['sympatheticExample'] ?? '';
        $toc_content = $attributes['tocContent'] ?? '';
        
        ob_start();
        ?>
        <section class="intro">
            <p><?php echo esc_html($simple_definition); ?></p>
            <p><?php echo esc_html($sympathetic_example); ?></p>
            <?php if ($toc_content): ?>
                <nav class="toc">
                    <strong>Содержание:</strong>
                    <?php echo wp_kses_post($toc_content); ?>
                </nav>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Рендер блока изображения с правильными стилями
     */
    public function render_article_image_block($attributes) {
        $image_url = $attributes['imageUrl'] ?? '';
        $alt_text = $attributes['altText'] ?? '';
        $caption = $attributes['caption'] ?? '';
        $position = $attributes['position'] ?? 'after_toc';
        
        if (!$image_url) {
            return '';
        }
        
        ob_start();
        ?>
        <figure class="article-featured-image ios-style-image" data-position="<?php echo esc_attr($position); ?>">
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($alt_text); ?>" 
                 loading="lazy" 
                 width="960" 
                 height="540" />
            <?php if ($caption): ?>
                <figcaption><?php echo esc_html($caption); ?></figcaption>
            <?php endif; ?>
        </figure>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Рендер блока контентной секции
     */
    public function render_content_section_block($attributes) {
        $section_id = $attributes['sectionId'] ?? '';
        $section_title = $attributes['sectionTitle'] ?? '';
        $section_content = $attributes['sectionContent'] ?? '';
        $has_example = $attributes['hasExample'] ?? false;
        
        ob_start();
        ?>
        <section id="<?php echo esc_attr($section_id); ?>">
            <?php if ($section_title): ?>
                <h2><?php echo esc_html($section_title); ?></h2>
            <?php endif; ?>
            <div class="section-content">
                <?php echo wp_kses_post($section_content); ?>
            </div>
            <?php if ($has_example): ?>
                <div class="example">
                    <p><strong>Пример:</strong> [Пример будет добавлен автоматически]</p>
                </div>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX обработчик для обновления конкретного блока
     */
    public function ajax_update_block() {
        check_ajax_referer('bsag_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $block_type = sanitize_text_field($_POST['block_type'] ?? '');
        $block_attributes = $_POST['block_attributes'] ?? [];
        $block_index = intval($_POST['block_index'] ?? 0);
        
        if (!$post_id || !$block_type) {
            wp_send_json_error('Неверные параметры');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Пост не найден');
        }
        
        // Парсим Gutenberg блоки
        $blocks = parse_blocks($post->post_content);
        
        // Находим и обновляем нужный блок
        $updated = $this->update_specific_block($blocks, $block_type, $block_attributes, $block_index);
        
        if ($updated) {
            // Пересобираем контент из блоков
            $new_content = $this->blocks_to_content($blocks);
            
            // Обновляем пост
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $new_content
            ]);
            
            wp_send_json_success('Блок успешно обновлен');
        } else {
            wp_send_json_error('Блок не найден или не обновлен');
        }
    }
    
    /**
     * AJAX обработчик для удаления дублирующихся изображений
     */
    public function ajax_remove_duplicate_images() {
        check_ajax_referer('bsag_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error('Неверный ID поста');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Пост не найден');
        }
        
        // Удаляем дублирующиеся изображения
        $cleaned_content = $this->remove_duplicate_images($post->post_content);
        
        if ($cleaned_content !== $post->post_content) {
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $cleaned_content
            ]);
            
            wp_send_json_success('Дублирующиеся изображения удалены');
        } else {
            wp_send_json_success('Дублирующиеся изображения не найдены');
        }
    }
    
    /**
     * Обновление конкретного блока в массиве блоков
     */
    private function update_specific_block(&$blocks, $block_type, $new_attributes, $target_index) {
        $current_index = 0;
        
        foreach ($blocks as &$block) {
            if ($block['blockName'] === $block_type) {
                if ($current_index === $target_index) {
                    $block['attrs'] = array_merge($block['attrs'] ?? [], $new_attributes);
                    return true;
                }
                $current_index++;
            }
            
            // Рекурсивно проверяем вложенные блоки
            if (!empty($block['innerBlocks'])) {
                if ($this->update_specific_block($block['innerBlocks'], $block_type, $new_attributes, $target_index)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Удаление дублирующихся изображений из контента
     */
    private function remove_duplicate_images($content) {
        // Находим все изображения
        preg_match_all('/<figure[^>]*>.*?<\/figure>/s', $content, $matches);
        
        if (count($matches[0]) <= 1) {
            return $content; // Нет дубликатов
        }
        
        // Оставляем только первое изображение (которое в правильной позиции)
        $first_image = $matches[0][0];
        $content_without_images = preg_replace('/<figure[^>]*>.*?<\/figure>/s', '', $content);
        
        // Вставляем первое изображение в правильную позицию (после оглавления)
        $toc_pattern = '/(<nav class="toc">.*?<\/nav>)/s';
        if (preg_match($toc_pattern, $content_without_images, $toc_matches)) {
            $content_without_images = str_replace(
                $toc_matches[0], 
                $toc_matches[0] . "\n\n" . $first_image, 
                $content_without_images
            );
        }
        
        return $content_without_images;
    }
    
    /**
     * Конвертация массива блоков обратно в контент
     */
    private function blocks_to_content($blocks) {
        $content = '';
        
        foreach ($blocks as $block) {
            if ($block['blockName'] === null) {
                // Обычный HTML контент
                $content .= $block['innerHTML'];
            } else {
                // Gutenberg блок
                $content .= render_block($block);
            }
        }
        
        return $content;
    }
    
    /**
     * Создание блока изображения с правильными стилями
     */
    public function create_styled_image_block($image_url, $alt_text, $caption = '', $position = 'after_toc') {
        $block_attributes = [
            'imageUrl' => $image_url,
            'altText' => $alt_text,
            'caption' => $caption,
            'position' => $position
        ];
        
        return [
            'blockName' => 'bizfin/article-image',
            'attrs' => $block_attributes,
            'innerHTML' => '',
            'innerContent' => [''],
            'innerBlocks' => []
        ];
    }
    
    /**
     * Добавление CSS стилей для изображений в iOS стиле
     */
    public function enqueue_block_styles() {
        $css = "
        .ios-style-image {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            overflow: hidden;
            margin: 20px 0;
            background: #fff;
        }
        
        .ios-style-image img {
            width: 100%;
            height: auto;
            aspect-ratio: 16/9;
            object-fit: cover;
            display: block;
        }
        
        .ios-style-image figcaption {
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.02);
            font-size: 14px;
            color: #666;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .article-featured-image {
            max-width: 100%;
            margin: 20px auto;
        }
        ";
        
        wp_add_inline_style('wp-block-library', $css);
    }
}

