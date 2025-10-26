<?php
/**
 * Gutenberg блок для алфавитного меню
 */

if (!defined('ABSPATH')) exit;

class ABP_V2_Alphabet_Block {
    
    public function __construct() {
        add_action('init', [$this, 'register_block']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
    }
    
    public function register_block() {
        register_block_type('abp-v2/alphabet-menu', [
            'editor_script' => 'abp-v2-alphabet-block-editor',
            'editor_style' => 'abp-v2-alphabet-block-editor',
            'style' => 'abp-v2-alphabet-block',
            'render_callback' => [$this, 'render_block'],
            'supports' => [
                'align' => ['full', 'wide']
            ],
            'attributes' => [
                'showSearch' => [
                    'type' => 'boolean',
                    'default' => true
                ],
                'showTitle' => [
                    'type' => 'boolean', 
                    'default' => true
                ]
            ]
        ]);
    }
    
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'abp-v2-alphabet-block-editor',
            plugins_url('blocks/alphabet-block-editor.js', dirname(__FILE__)),
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            '2.0.0'
        );
        
        wp_enqueue_style(
            'abp-v2-alphabet-block-editor',
            plugins_url('blocks/alphabet-block-editor.css', dirname(__FILE__)),
            ['wp-edit-blocks'],
            '2.0.0'
        );
    }
    
    public function render_block($attributes) {
        $show_search = $attributes['showSearch'] ?? true;
        $show_title = $attributes['showTitle'] ?? true;
        $align = $attributes['align'] ?? '';
        
        // Подключаем стили
        wp_enqueue_style('abp-v2-css');
        
        $align_class = $align ? 'align' . $align : '';
        $output = '<div class="abp-v2-alphabet-block ' . esc_attr($align_class) . '">';
        
        if ($show_title) {
            $output .= '
            <div class="abp-v2-main-header-content">
                <div class="abp-v2-main-title">
                    <h1>Блог</h1>
                </div>
                <div class="abp-v2-main-subtitle">
                    <p>Найдите в алфавитном порядке</p>
                </div>';
            
            if ($show_search) {
                $output .= '
                <div class="abp-v2-main-search">
                    <input type="text" id="abp-v2-search-input" placeholder="Поиск по ключевым словам..." />
                    <button id="abp-v2-search-btn" type="button">🔍</button>
                </div>';
            }
            
            $output .= '</div>';
        }
        
        $output .= '<div class="abp-v2-main-alphabet">';
        $output .= do_shortcode('[abp_v2_alphabet_only]');
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

new ABP_V2_Alphabet_Block();
