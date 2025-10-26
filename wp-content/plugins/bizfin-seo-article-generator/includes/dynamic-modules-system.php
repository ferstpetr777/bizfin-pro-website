<?php
/**
 * BizFin SEO Article Generator - Dynamic Modules System
 * Система динамических модулей для статей
 */

if (!defined('ABSPATH')) exit;

class BizFin_Dynamic_Modules_System {
    
    private $available_modules = [
        'calculator' => [
            'name' => 'Калькулятор банковских гарантий',
            'description' => 'Интерактивный калькулятор для расчета стоимости гарантии',
            'shortcode' => 'bsag_calculator',
            'template' => 'calculator-module.php'
        ],
        'schema_diagram' => [
            'name' => 'Схема-диаграмма процесса',
            'description' => 'Визуальная схема процесса получения банковской гарантии',
            'shortcode' => 'bsag_schema_diagram',
            'template' => 'schema-diagram-module.php'
        ],
        'comparison_table' => [
            'name' => 'Сравнительная таблица',
            'description' => 'Таблица сравнения банков и их условий',
            'shortcode' => 'bsag_comparison_table',
            'template' => 'comparison-table-module.php'
        ],
        'live_rates' => [
            'name' => 'Актуальные ставки',
            'description' => 'Блок с актуальными ставками банков',
            'shortcode' => 'bsag_live_rates',
            'template' => 'live-rates-module.php'
        ],
        'document_checklist' => [
            'name' => 'Чек-лист документов',
            'description' => 'Интерактивный список необходимых документов',
            'shortcode' => 'bsag_document_checklist',
            'template' => 'document-checklist-module.php'
        ],
        'timeline' => [
            'name' => 'Временная шкала',
            'description' => 'Timeline процесса получения гарантии',
            'shortcode' => 'bsag_timeline',
            'template' => 'timeline-module.php'
        ],
        'cost_breakdown' => [
            'name' => 'Разбор стоимости',
            'description' => 'Детальный разбор стоимости гарантии',
            'shortcode' => 'bsag_cost_breakdown',
            'template' => 'cost-breakdown-module.php'
        ],
        'bank_rating' => [
            'name' => 'Рейтинг банков',
            'description' => 'Рейтинг банков по надежности и условиям',
            'shortcode' => 'bsag_bank_rating',
            'template' => 'bank-rating-module.php'
        ]
    ];
    
    public function __construct() {
        // Регистрация AJAX обработчиков
        add_action('wp_ajax_bsag_generate_with_modules', [$this, 'ajax_generate_with_modules']);
        add_action('wp_ajax_bsag_publish_article', [$this, 'ajax_publish_article']);
        
        // Регистрация шорткодов модулей
        $this->register_module_shortcodes();
        
        // Интеграция с блогом теперь обрабатывается через Integration Manager
        add_filter('the_content', [$this, 'enhance_article_content'], 10, 1);
    }
    
    /**
     * Генерация статьи с динамическими модулями
     */
    public function generate_article_with_modules($keyword, $user_instruction, $table_of_contents, $modules) {
        // Получаем данные ключевого слова из матрицы
        $keyword_data = $this->get_keyword_data($keyword);
        
        if (!$keyword_data) {
            throw new Exception('Ключевое слово не найдено в матрице');
        }
        
        if (!$keyword_data) {
            return new WP_Error('keyword_not_found', 'Ключевое слово не найдено в матрице');
        }
        
        // Валидируем оглавление
        $validated_toc = $this->validate_table_of_contents($table_of_contents);
        
        // Генерируем контент с модулями
        $article_content = $this->generate_enhanced_content($keyword, $keyword_data, $validated_toc, $modules, $user_instruction);
        
        // Проверяем минимальный объем статьи
        $word_count = str_word_count(strip_tags($article_content['content']));
        if ($word_count < 2500) {
            $article_content = $this->expand_content_to_minimum($article_content, $validated_toc, 2500);
        }
        
        // Создаем пост в WordPress
        $post_id = $this->create_blog_post($keyword, $article_content, $keyword_data);
        
        // Интегрируем с системой блога
        // Запуск события для централизованного менеджера интеграций
        do_action('bsag_article_generated', $post_id, [
            'keyword' => $keyword,
            'user_instruction' => $user_instruction,
            'table_of_contents' => $table_of_contents,
            'modules' => $modules,
            'integration_status' => 'ready_for_processing'
        ]);
        
        return [
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'article_content' => $article_content,
            'word_count' => str_word_count(strip_tags($article_content['content'])),
            'modules_used' => $modules,
            'integration_status' => 'completed'
        ];
    }
    
    /**
     * Генерация расширенного контента с модулями
     */
    private function generate_enhanced_content($keyword, $keyword_data, $table_of_contents, $modules, $user_instruction) {
        $content = [];
        
        // H1 заголовок
        $content[] = '<h1>' . $this->generate_h1_title($keyword, $keyword_data) . '</h1>';
        
        // Введение с учетом пользовательской инструкции
        $content[] = $this->generate_introduction($keyword, $keyword_data, $user_instruction);
        
        // Основные разделы согласно оглавлению
        foreach ($table_of_contents as $section) {
            $content[] = $this->generate_section_content($section, $keyword, $keyword_data, $modules);
        }
        
        // FAQ секция
        $content[] = $this->generate_faq_section($keyword, $keyword_data);
        
        // Заключение
        $content[] = $this->generate_conclusion($keyword, $keyword_data);
        
        // Объединяем контент
        $full_content = implode("\n\n", $content);
        
        // Добавляем модули в нужные места
        $full_content = $this->insert_modules($full_content, $modules);
        
        // Создаем мета-данные
        $meta_data = $this->generate_meta_data($keyword, $keyword_data, $table_of_contents);
        
        return [
            'content' => $full_content,
            'meta_data' => $meta_data,
            'structure' => $table_of_contents,
            'modules' => $modules,
            'user_instruction' => $user_instruction
        ];
    }
    
    /**
     * Генерация контента для раздела
     */
    private function generate_section_content($section, $keyword, $keyword_data, $modules) {
        $section_content = [];
        
        // H2 заголовок
        $section_content[] = '<h2>' . esc_html($section['heading']) . '</h2>';
        
        // Основной контент раздела
        $section_text = $this->generate_section_text($section, $keyword, $keyword_data);
        $section_content[] = $section_text;
        
        // Подразделы если есть
        if (!empty($section['subheadings'])) {
            foreach ($section['subheadings'] as $subheading) {
                $section_content[] = '<h3>' . esc_html($subheading) . '</h3>';
                $section_content[] = $this->generate_subsection_text($subheading, $keyword, $keyword_data);
            }
        }
        
        return implode("\n\n", $section_content);
    }
    
    /**
     * Генерация текста подраздела
     */
    private function generate_subsection_text($subheading, $keyword, $keyword_data) {
        $subsection_templates = [
            'Запрос' => 'Подача заявления на получение банковской гарантии является первым и важнейшим этапом процесса. Необходимо подготовить полный пакет документов и правильно оформить заявление.',
            'Скоринг' => 'Процедура скоринга включает комплексную оценку финансового состояния заявителя, его кредитоспособности и соответствия требованиям банка.',
            'Оферта' => 'На основе результатов скоринга банк формирует оферту с условиями предоставления банковской гарантии, включая размер комиссии и сроки.',
            'Договор' => 'Заключение договора банковской гарантии происходит после согласования всех условий и подписания необходимых документов.',
            'Выдача' => 'Выдача банковской гарантии осуществляется в установленные договором сроки после выполнения всех условий.',
            'Реестр' => 'Все выданные банковские гарантии регистрируются в специальном реестре для контроля и учета.',
            'Сопровождение' => 'Сопровождение банковской гарантии включает мониторинг выполнения обязательств и взаимодействие с бенефициаром.'
        ];
        
        if (isset($subsection_templates[$subheading])) {
            return '<p>' . $subsection_templates[$subheading] . '</p>';
        }
        
        return '<p>Дополнительная информация по теме "' . $subheading . '" поможет лучше понять все аспекты данного вопроса.</p>';
    }
    
    /**
     * Генерация текста раздела
     */
    private function generate_section_text($section, $keyword, $keyword_data) {
        $section_type = $this->identify_section_type($section['heading']);
        
        $templates = [
            'definition' => 'В данном разделе мы подробно разберем определение и основные понятия, связанные с {keyword}. Это фундаментальные знания, которые помогут вам понять суть процесса.',
            'process' => 'Процесс {keyword} включает несколько ключевых этапов. Рассмотрим каждый из них подробно, чтобы вы понимали последовательность действий.',
            'requirements' => 'Для {keyword} необходимо выполнить ряд требований. Мы рассмотрим все условия и критерии, которые нужно учесть.',
            'cost' => 'Стоимость {keyword} зависит от множества факторов. Проанализируем все аспекты ценообразования и способы оптимизации расходов.',
            'mistakes' => 'При работе с {keyword} часто допускаются типичные ошибки. Изучив этот раздел, вы сможете их избежать.',
            'case_study' => 'Рассмотрим практический пример {keyword} на реальном кейсе. Это поможет лучше понять применение теоретических знаний.',
            'conclusion' => 'Подведем итоги по теме {keyword}. Сформулируем ключевые выводы и практические рекомендации.'
        ];
        
        $template = $templates[$section_type] ?? $templates['definition'];
        $base_text = str_replace('{keyword}', $keyword, $template);
        
        // Расширяем текст до нужного объема
        $expanded_text = $this->expand_section_text($base_text, $section, $keyword, $keyword_data);
        
        return '<p>' . $expanded_text . '</p>';
    }
    
    /**
     * Расширение текста раздела
     */
    private function expand_section_text($base_text, $section, $keyword, $keyword_data) {
        $expansions = [
            'Детальный анализ показывает, что ' . $keyword . ' является важным инструментом для современного бизнеса.',
            'Практический опыт подтверждает эффективность использования ' . $keyword . ' в различных ситуациях.',
            'Эксперты рекомендуют учитывать все нюансы при работе с ' . $keyword . ', чтобы избежать проблем.',
            'Статистика показывает, что правильное применение ' . $keyword . ' приводит к положительным результатам.',
            'Важно понимать, что ' . $keyword . ' требует профессионального подхода и внимания к деталям.'
        ];
        
        $expanded_text = $base_text;
        foreach ($expansions as $expansion) {
            $expanded_text .= ' ' . $expansion;
        }
        
        // Добавляем конкретные детали в зависимости от типа раздела
        $expanded_text .= ' ' . $this->add_section_specific_content($section, $keyword, $keyword_data);
        
        return $expanded_text;
    }
    
    /**
     * Добавление специфичного контента для раздела
     */
    private function add_section_specific_content($section, $keyword, $keyword_data) {
        $section_type = $this->identify_section_type($section['heading']);
        
        switch ($section_type) {
            case 'definition':
                return 'Основные участники процесса: банк-гарант, принципал и бенефициар. Каждый из них имеет свои права и обязанности.';
                
            case 'process':
                return 'Процесс включает: подачу заявления, рассмотрение банком, подписание договора и выдачу гарантии.';
                
            case 'requirements':
                return 'Основные требования: финансовая стабильность, положительная репутация, наличие необходимых документов.';
                
            case 'cost':
                return 'Стоимость варьируется от 1% до 5% годовых от суммы гарантии в зависимости от банка и условий.';
                
            case 'mistakes':
                return 'Типичные ошибки: неправильный выбор банка, неточное заполнение документов, игнорирование сроков.';
                
            case 'case_study':
                return 'Практический пример показывает реальные результаты применения банковской гарантии в бизнесе.';
                
            default:
                return 'Дополнительная информация поможет лучше понять все аспекты данной темы.';
        }
    }
    
    /**
     * Вставка модулей в контент
     */
    private function insert_modules($content, $modules) {
        foreach ($modules as $module_name) {
            if (isset($this->available_modules[$module_name])) {
                $module_shortcode = $this->available_modules[$module_name]['shortcode'];
                
                // Определяем место для вставки модуля
                $insertion_point = $this->find_module_insertion_point($content, $module_name);
                
                if ($insertion_point !== false) {
                    $content = substr_replace($content, $module_shortcode, $insertion_point, 0);
                }
            }
        }
        
        return $content;
    }
    
    /**
     * Поиск места для вставки модуля
     */
    private function find_module_insertion_point($content, $module_name) {
        $insertion_patterns = [
            'calculator' => ['стоимость', 'расчет', 'калькулятор'],
            'schema_diagram' => ['процесс', 'схема', 'этапы'],
            'comparison_table' => ['сравнение', 'банки', 'условия'],
            'live_rates' => ['ставки', 'тарифы', 'цены'],
            'document_checklist' => ['документы', 'список', 'требования'],
            'timeline' => ['сроки', 'время', 'период'],
            'cost_breakdown' => ['стоимость', 'расходы', 'затраты'],
            'bank_rating' => ['банки', 'рейтинг', 'надежность']
        ];
        
        $patterns = $insertion_patterns[$module_name] ?? [];
        
        foreach ($patterns as $pattern) {
            $pos = stripos($content, $pattern);
            if ($pos !== false) {
                // Ищем конец абзаца
                $end_pos = strpos($content, '</p>', $pos);
                if ($end_pos !== false) {
                    return $end_pos + 4; // После </p>
                }
            }
        }
        
        // Если не найдено подходящее место, вставляем в середину контента
        $mid_pos = strlen($content) / 2;
        return $mid_pos;
    }
    
    /**
     * Создание поста в блоге
     */
    private function create_blog_post($keyword, $article_content, $keyword_data) {
        $post_data = [
            'post_title' => $article_content['meta_data']['title'],
            'post_content' => $article_content['content'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'post_excerpt' => $article_content['meta_data']['meta_description'],
            'meta_input' => [
                '_bsag_generated' => true,
                '_bsag_keyword' => $keyword,
                '_bsag_keyword_data' => json_encode($keyword_data),
                '_bsag_modules' => json_encode($article_content['modules']),
                '_bsag_user_instruction' => $article_content['user_instruction'],
                '_bsag_generation_timestamp' => current_time('mysql')
            ]
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Устанавливаем мета-данные Yoast SEO
            update_post_meta($post_id, '_yoast_wpseo_title', $article_content['meta_data']['title']);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $article_content['meta_data']['meta_description']);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
            
            // Устанавливаем категорию "Сгенерированные статьи"
            $category_id = $this->get_or_create_category('Сгенерированные статьи');
            wp_set_post_categories($post_id, [$category_id]);
            
            // Устанавливаем теги
            $tags = $this->generate_tags($keyword, $keyword_data);
            wp_set_post_tags($post_id, $tags);
        }
        
        return $post_id;
    }
    
    /**
     * Интеграция с системой блога
     */
    private function integrate_with_blog_system($post_id, $keyword) {
        // Интеграция с Alphabet Blog Panel
        if (class_exists('ABP_Plugin')) {
            $first_letter = mb_strtoupper(mb_substr($keyword, 0, 1, 'UTF-8'), 'UTF-8');
            update_post_meta($post_id, 'abp_first_letter', $first_letter);
        }
        
        // Интеграция с ABP Article Quality Monitor
        if (class_exists('ABP_Article_Quality_Monitor')) {
            update_post_meta($post_id, '_bsag_abp_quality_checked', true);
            // Запускаем проверку качества
            $post = get_post($post_id);
            if ($post) {
                $quality_monitor = new ABP_Article_Quality_Monitor();
                $quality_monitor->check_post_quality($post_id, $post);
            }
        }
        
        // Интеграция с ABP Image Generator
        if (class_exists('ABP_Image_Generator')) {
            update_post_meta($post_id, '_bsag_abp_image_generated', true);
            // Запускаем генерацию изображения
            $image_generator = new ABP_Image_Generator();
            $image_generator->generate_image_for_post($post_id);
        }
        
        // Интеграция с Yoast SEO
        if (class_exists('WPSEO_Options')) {
            update_post_meta($post_id, '_bsag_yoast_optimized', true);
        }
        
        // Запускаем событие для других интеграций
        do_action('bsag_article_generated', $post_id, [
            'keyword' => $keyword,
            'modules' => $this->available_modules,
            'integration_status' => 'completed'
        ]);
    }
    
    /**
     * Регистрация шорткодов модулей
     */
    private function register_module_shortcodes() {
        foreach ($this->available_modules as $module_key => $module_data) {
            add_shortcode($module_data['shortcode'], function($atts) use ($module_key) {
                return $this->render_module($module_key, $atts);
            });
        }
    }
    
    /**
     * Рендеринг модуля
     */
    private function render_module($module_key, $atts) {
        $atts = shortcode_atts([
            'style' => 'default',
            'show_title' => 'true'
        ], $atts);
        
        ob_start();
        
        switch ($module_key) {
            case 'calculator':
                $this->render_calculator_module($atts);
                break;
            case 'schema_diagram':
                $this->render_schema_diagram_module($atts);
                break;
            case 'comparison_table':
                $this->render_comparison_table_module($atts);
                break;
            case 'live_rates':
                $this->render_live_rates_module($atts);
                break;
            case 'document_checklist':
                $this->render_document_checklist_module($atts);
                break;
            case 'timeline':
                $this->render_timeline_module($atts);
                break;
            case 'cost_breakdown':
                $this->render_cost_breakdown_module($atts);
                break;
            case 'bank_rating':
                $this->render_bank_rating_module($atts);
                break;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Рендеринг калькулятора
     */
    private function render_calculator_module($atts) {
        ?>
        <div class="bsag-module bsag-calculator-module">
            <h3>Калькулятор банковских гарантий</h3>
            <div class="calculator-form">
                <div class="form-group">
                    <label for="guarantee_amount">Сумма гарантии (руб.):</label>
                    <input type="number" id="guarantee_amount" name="amount" placeholder="1000000">
                </div>
                <div class="form-group">
                    <label for="guarantee_period">Срок гарантии (дней):</label>
                    <input type="number" id="guarantee_period" name="period" placeholder="365">
                </div>
                <div class="form-group">
                    <label for="bank_rate">Ставка банка (%):</label>
                    <input type="number" id="bank_rate" name="rate" step="0.1" placeholder="2.5">
                </div>
                <button type="button" class="calculate-btn">Рассчитать стоимость</button>
                <div class="calculation-result" style="display: none;">
                    <h4>Результат расчета:</h4>
                    <p>Стоимость гарантии: <span class="cost-result">0</span> руб.</p>
                    <p>Ежемесячная стоимость: <span class="monthly-cost">0</span> руб.</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг схемы-диаграммы
     */
    private function render_schema_diagram_module($atts) {
        ?>
        <div class="bsag-module bsag-schema-diagram-module">
            <h3>Схема процесса получения банковской гарантии</h3>
            <div class="process-diagram">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Подача заявления</h4>
                        <p>Принципал подает заявление в банк</p>
                    </div>
                </div>
                <div class="process-arrow">→</div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Рассмотрение банком</h4>
                        <p>Банк анализирует заявку и документы</p>
                    </div>
                </div>
                <div class="process-arrow">→</div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Подписание договора</h4>
                        <p>Стороны подписывают договор гарантии</p>
                    </div>
                </div>
                <div class="process-arrow">→</div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Выдача гарантии</h4>
                        <p>Банк выдает гарантию принципалу</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг таблицы сравнения
     */
    private function render_comparison_table_module($atts) {
        ?>
        <div class="bsag-module bsag-comparison-table-module">
            <h3>Сравнение условий банков</h3>
            <div class="comparison-table">
                <table>
                    <thead>
                        <tr>
                            <th>Банк</th>
                            <th>Ставка (% годовых)</th>
                            <th>Срок рассмотрения</th>
                            <th>Минимальная сумма</th>
                            <th>Особенности</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Сбербанк</td>
                            <td>2.5-4.0</td>
                            <td>3-5 дней</td>
                            <td>100 000 руб.</td>
                            <td>Широкая сеть отделений</td>
                        </tr>
                        <tr>
                            <td>ВТБ</td>
                            <td>2.0-3.5</td>
                            <td>2-4 дня</td>
                            <td>50 000 руб.</td>
                            <td>Быстрое рассмотрение</td>
                        </tr>
                        <tr>
                            <td>Альфа-Банк</td>
                            <td>2.8-4.2</td>
                            <td>3-7 дней</td>
                            <td>200 000 руб.</td>
                            <td>Гибкие условия</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг актуальных ставок
     */
    private function render_live_rates_module($atts) {
        ?>
        <div class="bsag-module bsag-live-rates-module">
            <h3>Актуальные ставки банковских гарантий</h3>
            <div class="rates-grid">
                <div class="rate-item">
                    <div class="bank-name">Сбербанк</div>
                    <div class="rate-value">2.5%</div>
                    <div class="rate-period">годовых</div>
                </div>
                <div class="rate-item">
                    <div class="bank-name">ВТБ</div>
                    <div class="rate-value">2.0%</div>
                    <div class="rate-period">годовых</div>
                </div>
                <div class="rate-item">
                    <div class="bank-name">Альфа-Банк</div>
                    <div class="rate-value">2.8%</div>
                    <div class="rate-period">годовых</div>
                </div>
            </div>
            <p class="rates-update">Обновлено: <?php echo current_time('d.m.Y H:i'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Рендеринг чек-листа документов
     */
    private function render_document_checklist_module($atts) {
        ?>
        <div class="bsag-module bsag-document-checklist-module">
            <h3>Чек-лист документов для получения банковской гарантии</h3>
            <div class="document-checklist">
                <div class="checklist-item">
                    <input type="checkbox" id="doc1">
                    <label for="doc1">Заявление на выдачу банковской гарантии</label>
                </div>
                <div class="checklist-item">
                    <input type="checkbox" id="doc2">
                    <label for="doc2">Учредительные документы</label>
                </div>
                <div class="checklist-item">
                    <input type="checkbox" id="doc3">
                    <label for="doc3">Финансовая отчетность за последние 3 года</label>
                </div>
                <div class="checklist-item">
                    <input type="checkbox" id="doc4">
                    <label for="doc4">Документы по основному обязательству</label>
                </div>
                <div class="checklist-item">
                    <input type="checkbox" id="doc5">
                    <label for="doc5">Справки о состоянии расчетов</label>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг временной шкалы
     */
    private function render_timeline_module($atts) {
        ?>
        <div class="bsag-module bsag-timeline-module">
            <h3>Временная шкала получения банковской гарантии</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">День 1</div>
                    <div class="timeline-content">
                        <h4>Подача документов</h4>
                        <p>Предоставление всех необходимых документов в банк</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">Дни 2-5</div>
                    <div class="timeline-content">
                        <h4>Рассмотрение заявки</h4>
                        <p>Анализ документов и принятие решения банком</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">День 6</div>
                    <div class="timeline-content">
                        <h4>Подписание договора</h4>
                        <p>Оформление договора банковской гарантии</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">День 7</div>
                    <div class="timeline-content">
                        <h4>Выдача гарантии</h4>
                        <p>Получение готовой банковской гарантии</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг разбора стоимости
     */
    private function render_cost_breakdown_module($atts) {
        ?>
        <div class="bsag-module bsag-cost-breakdown-module">
            <h3>Разбор стоимости банковской гарантии</h3>
            <div class="cost-breakdown">
                <div class="cost-item">
                    <div class="cost-label">Комиссия банка</div>
                    <div class="cost-value">2.5% годовых</div>
                    <div class="cost-description">Основная стоимость гарантии</div>
                </div>
                <div class="cost-item">
                    <div class="cost-label">Дополнительные расходы</div>
                    <div class="cost-value">0.1%</div>
                    <div class="cost-description">Оформление и сопровождение</div>
                </div>
                <div class="cost-item">
                    <div class="cost-label">Итого</div>
                    <div class="cost-value">2.6% годовых</div>
                    <div class="cost-description">Общая стоимость гарантии</div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Рендеринг рейтинга банков
     */
    private function render_bank_rating_module($atts) {
        ?>
        <div class="bsag-module bsag-bank-rating-module">
            <h3>Рейтинг банков по надежности</h3>
            <div class="bank-rating">
                <div class="rating-item">
                    <div class="bank-name">Сбербанк</div>
                    <div class="rating-score">AAA</div>
                    <div class="rating-description">Максимальная надежность</div>
                </div>
                <div class="rating-item">
                    <div class="bank-name">ВТБ</div>
                    <div class="rating-score">AA+</div>
                    <div class="rating-description">Высокая надежность</div>
                </div>
                <div class="rating-item">
                    <div class="bank-name">Альфа-Банк</div>
                    <div class="rating-score">AA</div>
                    <div class="rating-description">Высокая надежность</div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Дополнительные методы для работы с данными
    private function get_keyword_data($keyword) {
        // Получаем экземпляр основного плагина
        $main_plugin = new BizFin_SEO_Article_Generator();
        
        // Получаем SEO матрицу через рефлексию
        $reflection = new ReflectionClass($main_plugin);
        $property = $reflection->getProperty('seo_matrix');
        $property->setAccessible(true);
        $seo_matrix = $property->getValue($main_plugin);
        
        // Проверяем наличие ключевого слова в матрице
        if (isset($seo_matrix['keywords'][$keyword])) {
            return $seo_matrix['keywords'][$keyword];
        }
        
        return null;
    }
    
    private function validate_table_of_contents($table_of_contents) {
        // Валидация оглавления
        $validated_toc = [];
        
        foreach ($table_of_contents as $section) {
            if (isset($section['heading']) && !empty($section['heading'])) {
                $validated_toc[] = [
                    'heading' => sanitize_text_field($section['heading']),
                    'subheadings' => isset($section['subheadings']) ? array_map('sanitize_text_field', $section['subheadings']) : [],
                    'key_points' => isset($section['key_points']) ? array_map('sanitize_text_field', $section['key_points']) : [],
                    'target_words' => isset($section['target_words']) ? intval($section['target_words']) : 300
                ];
            }
        }
        
        return $validated_toc;
    }
    
    private function identify_section_type($heading) {
        $heading_lower = mb_strtolower($heading, 'UTF-8');
        
        if (strpos($heading_lower, 'определение') !== false || strpos($heading_lower, 'что такое') !== false) {
            return 'definition';
        } elseif (strpos($heading_lower, 'как') !== false || strpos($heading_lower, 'процесс') !== false) {
            return 'process';
        } elseif (strpos($heading_lower, 'требования') !== false || strpos($heading_lower, 'документы') !== false) {
            return 'requirements';
        } elseif (strpos($heading_lower, 'стоимость') !== false || strpos($heading_lower, 'цена') !== false) {
            return 'cost';
        } elseif (strpos($heading_lower, 'ошибки') !== false || strpos($heading_lower, 'проблемы') !== false) {
            return 'mistakes';
        } elseif (strpos($heading_lower, 'кейс') !== false || strpos($heading_lower, 'пример') !== false) {
            return 'case_study';
        } elseif (strpos($heading_lower, 'итоги') !== false || strpos($heading_lower, 'выводы') !== false) {
            return 'conclusion';
        }
        
        return 'general';
    }
    
    private function generate_h1_title($keyword, $keyword_data) {
        $intent = $keyword_data['intent'] ?? 'informational';
        
        $titles = [
            'informational' => 'Что такое ' . $keyword . ': полное руководство',
            'commercial' => $keyword . ': профессиональные решения и услуги',
            'mixed' => $keyword . ': все что нужно знать для успеха'
        ];
        
        return $titles[$intent] ?? 'Полное руководство по ' . $keyword;
    }
    
    private function generate_introduction($keyword, $keyword_data, $user_instruction) {
        $introduction = '<p>' . $keyword . ' — это важный инструмент для современного бизнеса. ';
        
        if (!empty($user_instruction)) {
            $introduction .= $user_instruction . ' ';
        }
        
        $introduction .= 'В данном руководстве мы подробно разберем все аспекты этой темы, предоставив вам практические знания и рекомендации.</p>';
        
        return $introduction;
    }
    
    private function generate_faq_section($keyword, $keyword_data) {
        $faq_items = [
            ['question' => 'Что такое ' . $keyword . '?', 'answer' => 'Это важный инструмент для бизнеса, который обеспечивает выполнение обязательств.'],
            ['question' => 'Как получить ' . $keyword . '?', 'answer' => 'Для получения необходимо обратиться в банк с соответствующими документами.'],
            ['question' => 'Сколько стоит ' . $keyword . '?', 'answer' => 'Стоимость зависит от множества факторов и варьируется от 1% до 5% годовых.']
        ];
        
        $faq_html = '<div class="bsag-faq-section"><h2>Часто задаваемые вопросы</h2>';
        
        foreach ($faq_items as $item) {
            $faq_html .= '<div class="faq-item">';
            $faq_html .= '<h3>' . esc_html($item['question']) . '</h3>';
            $faq_html .= '<p>' . esc_html($item['answer']) . '</p>';
            $faq_html .= '</div>';
        }
        
        $faq_html .= '</div>';
        
        return $faq_html;
    }
    
    private function generate_conclusion($keyword, $keyword_data) {
        return '<div class="bsag-conclusion"><p>' . $keyword . ' является важным элементом современного бизнеса. Правильное понимание и применение этого инструмента поможет вам достичь успеха в предпринимательской деятельности.</p></div>';
    }
    
    private function generate_meta_data($keyword, $keyword_data, $table_of_contents) {
        return [
            'title' => 'Полное руководство по ' . $keyword . ' | BizFin Pro',
            'meta_description' => 'Узнайте все о ' . $keyword . ': определение, процесс получения, требования, стоимость. Экспертные советы и практические рекомендации.',
            'focus_keyword' => $keyword,
            'og_title' => 'Полное руководство по ' . $keyword,
            'og_description' => 'Подробное руководство по ' . $keyword . ' с практическими советами и примерами.'
        ];
    }
    
    private function expand_content_to_minimum($article_content, $table_of_contents, $min_words) {
        $current_words = str_word_count(strip_tags($article_content['content']));
        
        if ($current_words >= $min_words) {
            return $article_content;
        }
        
        $words_to_add = $min_words - $current_words;
        $words_per_section = ceil($words_to_add / count($table_of_contents));
        
        // Расширяем каждый раздел
        foreach ($table_of_contents as $section) {
            $additional_content = $this->generate_additional_content($section, $words_per_section);
            $article_content['content'] = str_replace(
                '<h2>' . $section['heading'] . '</h2>',
                '<h2>' . $section['heading'] . '</h2>' . $additional_content,
                $article_content['content']
            );
        }
        
        return $article_content;
    }
    
    private function generate_additional_content($section, $words_count) {
        $additional_text = 'Дополнительная информация по теме "' . $section['heading'] . '" поможет вам лучше понять все нюансы. ';
        
        $expansions = [
            'Важно учитывать все аспекты данного вопроса для достижения наилучших результатов.',
            'Практический опыт показывает, что правильный подход к решению задачи дает положительные результаты.',
            'Эксперты рекомендуют внимательно изучить все детали перед принятием решения.',
            'Статистика подтверждает эффективность применения профессионального подхода.',
            'Необходимо понимать, что успех зависит от множества факторов и требует комплексного подхода.'
        ];
        
        $words_added = 0;
        foreach ($expansions as $expansion) {
            if ($words_added >= $words_count) break;
            $additional_text .= $expansion . ' ';
            $words_added += str_word_count($expansion);
        }
        
        return '<p>' . $additional_text . '</p>';
    }
    
    private function get_or_create_category($category_name) {
        $category = get_category_by_slug(sanitize_title($category_name));
        
        if (!$category) {
            $category_id = wp_create_category($category_name);
            return $category_id;
        }
        
        return $category->term_id;
    }
    
    private function generate_tags($keyword, $keyword_data) {
        $base_tags = ['банковские гарантии', 'бизнес', 'финансы'];
        
        $keyword_tags = explode(' ', $keyword);
        $keyword_tags = array_map('sanitize_text_field', $keyword_tags);
        
        return array_merge($base_tags, $keyword_tags);
    }
    
    
    /**
     * AJAX обработчик генерации с модулями
     */
    public function ajax_generate_with_modules() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        $user_instruction = sanitize_textarea_field($_POST['user_instruction']);
        $table_of_contents = $_POST['table_of_contents'];
        $modules = $_POST['modules'];
        
        $result = $this->generate_article_with_modules($keyword, $user_instruction, $table_of_contents, $modules);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX обработчик публикации статьи
     */
    public function ajax_publish_article() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Статья не найдена');
        }
        
        // Обновляем статус на опубликованный
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);
        
        wp_send_json_success([
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'status' => 'published'
        ]);
    }
    
    /**
     * Интеграция с блогом при сохранении поста
     */
    public function integrate_with_blog($post_id, $post) {
        if ($post->post_type !== 'post') return;
        
        $is_generated = get_post_meta($post_id, '_bsag_generated', true);
        if (!$is_generated) return;
        
        // Интеграция с Alphabet Blog Panel
        if (class_exists('ABP_Plugin')) {
            $title = get_the_title($post_id);
            $first_letter = mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8');
            update_post_meta($post_id, 'abp_first_letter', $first_letter);
        }
    }
    
    /**
     * Улучшение контента статьи
     */
    public function enhance_article_content($content) {
        if (!is_single() || get_post_type() !== 'post') {
            return $content;
        }
        
        $is_generated = get_post_meta(get_the_ID(), '_bsag_generated', true);
        if (!$is_generated) {
            return $content;
        }
        
        // Добавляем дополнительные элементы для сгенерированных статей
        $enhanced_content = $content;
        
        // Добавляем CTA блок в конце статьи
        $cta_block = $this->generate_cta_block();
        $enhanced_content .= $cta_block;
        
        return $enhanced_content;
    }
    
    /**
     * Генерация CTA блока
     */
    private function generate_cta_block() {
        return '
        <div class="bsag-cta-block">
            <h3>Нужна помощь с банковской гарантией?</h3>
            <p>Наши эксперты помогут вам получить банковскую гарантию на выгодных условиях.</p>
            <div class="cta-buttons">
                <a href="/calculator/" class="btn btn-primary">Рассчитать стоимость</a>
                <a href="/contact/" class="btn btn-secondary">Получить консультацию</a>
            </div>
        </div>';
    }
}

// Инициализация системы динамических модулей
new BizFin_Dynamic_Modules_System();
