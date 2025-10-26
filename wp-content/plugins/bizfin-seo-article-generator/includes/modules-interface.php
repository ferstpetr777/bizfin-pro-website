<?php
/**
 * BizFin SEO Article Generator - Modules Interface
 * Интерфейс для работы с динамическими модулями
 */

if (!defined('ABSPATH')) exit;

class BizFin_Modules_Interface {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_modules_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_modules_assets']);
    }
    
    /**
     * Добавление меню для модулей
     */
    public function add_modules_menu() {
        add_submenu_page(
            'bizfin-seo-article-generator',
            __('Динамические модули', 'bizfin-seo-article-generator'),
            __('Модули', 'bizfin-seo-article-generator'),
            'manage_options',
            'bizfin-modules',
            [$this, 'render_modules_page']
        );
    }
    
    /**
     * Подключение ресурсов для модулей
     */
    public function enqueue_modules_assets($hook) {
        if (strpos($hook, 'bizfin-modules') !== false) {
            wp_enqueue_style(
                'bsag-modules-style',
                plugin_dir_url(__FILE__) . '../assets/css/modules.css',
                [],
                '1.0.0'
            );
            
            wp_enqueue_script(
                'bsag-modules-script',
                plugin_dir_url(__FILE__) . '../assets/js/modules.js',
                ['jquery'],
                '1.0.0',
                true
            );
            
            wp_localize_script('bsag-modules-script', 'bsag_modules_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bsag_modules_nonce')
            ]);
        }
    }
    
    /**
     * Рендеринг страницы модулей
     */
    public function render_modules_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Динамические модули для статей', 'bizfin-seo-article-generator'); ?></h1>
            
            <div class="bsag-modules-container">
                <!-- Форма создания статьи с модулями -->
                <div class="bsag-modules-form">
                    <h2><?php _e('Создание статьи с динамическими модулями', 'bizfin-seo-article-generator'); ?></h2>
                    
                    <form id="bsag-modules-form">
                        <div class="form-group">
                            <label for="keyword"><?php _e('Ключевое слово:', 'bizfin-seo-article-generator'); ?></label>
                            <input type="text" id="keyword" name="keyword" class="form-control" placeholder="<?php _e('Например: Что такое банковская гарантия', 'bizfin-seo-article-generator'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="user_instruction"><?php _e('Инструкция для статьи:', 'bizfin-seo-article-generator'); ?></label>
                            <textarea id="user_instruction" name="user_instruction" class="form-control" rows="3" placeholder="<?php _e('Опишите, какую статью вы хотите создать...', 'bizfin-seo-article-generator'); ?>" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><?php _e('Оглавление статьи:', 'bizfin-seo-article-generator'); ?></label>
                            <div id="table-of-contents">
                                <div class="toc-item">
                                    <input type="text" name="toc_heading[]" placeholder="<?php _e('Заголовок раздела', 'bizfin-seo-article-generator'); ?>" class="form-control">
                                    <textarea name="toc_subheadings[]" placeholder="<?php _e('Подзаголовки (по одному на строку)', 'bizfin-seo-article-generator'); ?>" class="form-control" rows="2"></textarea>
                                    <textarea name="toc_key_points[]" placeholder="<?php _e('Ключевые моменты (по одному на строку)', 'bizfin-seo-article-generator'); ?>" class="form-control" rows="2"></textarea>
                                    <input type="number" name="toc_target_words[]" placeholder="<?php _e('Целевое количество слов', 'bizfin-seo-article-generator'); ?>" class="form-control" value="300" min="100" max="1000">
                                    <button type="button" class="button remove-toc-item"><?php _e('Удалить', 'bizfin-seo-article-generator'); ?></button>
                                </div>
                            </div>
                            <button type="button" id="add-toc-item" class="button"><?php _e('Добавить раздел', 'bizfin-seo-article-generator'); ?></button>
                        </div>
                        
                        <div class="form-group">
                            <label><?php _e('Выберите модули:', 'bizfin-seo-article-generator'); ?></label>
                            <div class="modules-grid">
                                <div class="module-item">
                                    <input type="checkbox" id="module_calculator" name="modules[]" value="calculator">
                                    <label for="module_calculator">
                                        <strong>Калькулятор банковских гарантий</strong>
                                        <span>Интерактивный калькулятор для расчета стоимости гарантии</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_schema_diagram" name="modules[]" value="schema_diagram">
                                    <label for="module_schema_diagram">
                                        <strong>Схема-диаграмма процесса</strong>
                                        <span>Визуальная схема процесса получения банковской гарантии</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_comparison_table" name="modules[]" value="comparison_table">
                                    <label for="module_comparison_table">
                                        <strong>Сравнительная таблица</strong>
                                        <span>Таблица сравнения банков и их условий</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_live_rates" name="modules[]" value="live_rates">
                                    <label for="module_live_rates">
                                        <strong>Актуальные ставки</strong>
                                        <span>Блок с актуальными ставками банков</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_document_checklist" name="modules[]" value="document_checklist">
                                    <label for="module_document_checklist">
                                        <strong>Чек-лист документов</strong>
                                        <span>Интерактивный список необходимых документов</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_timeline" name="modules[]" value="timeline">
                                    <label for="module_timeline">
                                        <strong>Временная шкала</strong>
                                        <span>Timeline процесса получения гарантии</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_cost_breakdown" name="modules[]" value="cost_breakdown">
                                    <label for="module_cost_breakdown">
                                        <strong>Разбор стоимости</strong>
                                        <span>Детальный разбор стоимости гарантии</span>
                                    </label>
                                </div>
                                
                                <div class="module-item">
                                    <input type="checkbox" id="module_bank_rating" name="modules[]" value="bank_rating">
                                    <label for="module_bank_rating">
                                        <strong>Рейтинг банков</strong>
                                        <span>Рейтинг банков по надежности и условиям</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="button button-primary"><?php _e('Сгенерировать статью', 'bizfin-seo-article-generator'); ?></button>
                            <button type="button" id="test-with-modules" class="button"><?php _e('Тест с модулями', 'bizfin-seo-article-generator'); ?></button>
                        </div>
                    </form>
                </div>
                
                <!-- Результат генерации -->
                <div id="generation-result" class="bsag-generation-result" style="display: none;">
                    <h3><?php _e('Результат генерации', 'bizfin-seo-article-generator'); ?></h3>
                    <div id="result-content"></div>
                </div>
                
                <!-- Статистика модулей -->
                <div class="bsag-modules-stats">
                    <h3><?php _e('Статистика использования модулей', 'bizfin-seo-article-generator'); ?></h3>
                    <div id="modules-stats-content">
                        <p><?php _e('Загрузка статистики...', 'bizfin-seo-article-generator'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .bsag-modules-container {
            max-width: 1200px;
            margin: 20px 0;
        }
        
        .bsag-modules-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .toc-item {
            border: 1px solid #e5e5e5;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .toc-item input,
        .toc-item textarea {
            margin-bottom: 10px;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .module-item {
            border: 1px solid #e5e5e5;
            padding: 15px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .module-item input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .module-item label {
            cursor: pointer;
            display: flex;
            align-items: flex-start;
        }
        
        .module-item label strong {
            display: block;
            margin-bottom: 5px;
            color: #0073aa;
        }
        
        .module-item label span {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .bsag-generation-result {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .bsag-modules-stats {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .notification {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .notification.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .notification.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        </style>
        <?php
    }
}

// Инициализация интерфейса модулей
new BizFin_Modules_Interface();

