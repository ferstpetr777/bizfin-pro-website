<?php
/**
 * BizFin SEO Article Generator - Test Article Generator
 * Тестовая статья "Что такое банковская гарантия"
 */

if (!defined('ABSPATH')) exit;

class BizFin_Test_Article_Generator {
    
    public function __construct() {
        add_action('wp_ajax_bsag_generate_test_article', [$this, 'ajax_generate_test_article']);
    }
    
    /**
     * AJAX обработчик генерации тестовой статьи
     */
    public function ajax_generate_test_article() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        // Генерируем тестовую статью "Что такое банковская гарантия"
        $article_result = $this->generate_test_article();
        
        wp_send_json_success($article_result);
    }
    
    /**
     * Генерация тестовой статьи
     */
    private function generate_test_article() {
        $keyword = 'Что такое банковская гарантия';
        $user_instruction = 'Информационный; владельцы ИП/ООО впервые сталкиваются с требованиями. Отстройка: простая визуальная модель «кто кому что должен».';
        
        // Оглавление статьи
        $table_of_contents = [
            [
                'heading' => 'Определение и участники',
                'subheadings' => ['Банк', 'Принципал', 'Бенефициар'],
                'key_points' => ['Основные понятия', 'Роли участников', 'Взаимодействие'],
                'target_words' => 300
            ],
            [
                'heading' => 'Как это работает',
                'subheadings' => ['Заявка', 'Исполнение', 'Гарантийные обязательства'],
                'key_points' => ['Процесс получения', 'Этапы оформления', 'Механизм действия'],
                'target_words' => 400
            ],
            [
                'heading' => 'Когда без гарантии не обойтись',
                'subheadings' => ['Обязательные случаи', 'Добровольное использование'],
                'key_points' => ['Законодательные требования', 'Практические ситуации'],
                'target_words' => 350
            ],
            [
                'heading' => 'Что проверяет банк перед выдачей',
                'subheadings' => ['Финансовая проверка', 'Документооборот', 'Оценка рисков'],
                'key_points' => ['Критерии оценки', 'Необходимые документы', 'Процедура проверки'],
                'target_words' => 300
            ],
            [
                'heading' => 'Стоимость и факторы ценообразования',
                'subheadings' => ['Базовые ставки', 'Дополнительные факторы', 'Способы экономии'],
                'key_points' => ['Структура стоимости', 'Влияющие факторы', 'Оптимизация расходов'],
                'target_words' => 350
            ],
            [
                'heading' => 'Ошибки новичков и как их избежать',
                'subheadings' => ['Типичные ошибки', 'Рекомендации', 'Лучшие практики'],
                'key_points' => ['Частые проблемы', 'Советы экспертов', 'Предотвращение ошибок'],
                'target_words' => 300
            ],
            [
                'heading' => 'Мини‑кейс: простой контракт поставки',
                'subheadings' => ['Описание ситуации', 'Решение', 'Результат'],
                'key_points' => ['Практический пример', 'Пошаговое решение', 'Достигнутые результаты'],
                'target_words' => 400
            ],
            [
                'heading' => 'Итоги и чек‑лист «нужно/не нужно»',
                'subheadings' => ['Ключевые выводы', 'Практические рекомендации'],
                'key_points' => ['Основные принципы', 'Контрольный список', 'Следующие шаги'],
                'target_words' => 250
            ]
        ];
        
        // Модули для статьи
        $modules = ['calculator', 'schema_diagram', 'comparison_table'];
        
        // Проверяем наличие системы динамических модулей
        if (class_exists('BizFin_Dynamic_Modules_System')) {
            $dynamic_modules = new BizFin_Dynamic_Modules_System();
            $result = $dynamic_modules->generate_article_with_modules($keyword, $user_instruction, $table_of_contents, $modules);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'error' => $result->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'data' => $result
            ];
        }
        
        // Если система модулей недоступна, генерируем базовую статью
        return $this->generate_basic_article($keyword, $user_instruction, $table_of_contents, $modules);
    }
    
    /**
     * Генерация базовой статьи без системы модулей
     */
    private function generate_basic_article($keyword, $user_instruction, $table_of_contents, $modules) {
        // Создаем базовую структуру статьи
        $article_content = $this->build_article_content($keyword, $user_instruction, $table_of_contents);
        
        // Создаем пост в WordPress
        $post_id = $this->create_basic_post($keyword, $article_content);
        
        return [
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'article_content' => $article_content,
            'word_count' => str_word_count(strip_tags($article_content['content'])),
            'modules_used' => $modules,
            'integration_status' => 'basic'
        ];
    }
    
    /**
     * Построение контента статьи
     */
    private function build_article_content($keyword, $user_instruction, $table_of_contents) {
        $content = [];
        
        // H1 заголовок
        $content[] = '<h1>Что такое банковская гарантия: полное руководство для бизнеса</h1>';
        
        // Введение
        $content[] = '<p>Банковская гарантия — это важный финансовый инструмент, который обеспечивает выполнение обязательств между сторонами договора. ' . $user_instruction . ' В данном руководстве мы подробно разберем все аспекты банковских гарантий, предоставив вам практические знания и рекомендации для успешного использования этого инструмента.</p>';
        
        // Основные разделы
        foreach ($table_of_contents as $section) {
            $content[] = $this->build_section_content($section);
        }
        
        // FAQ секция
        $content[] = $this->build_faq_section();
        
        // Заключение
        $content[] = '<div class="bsag-conclusion"><p>Банковская гарантия является надежным инструментом обеспечения обязательств в современном бизнесе. Правильное понимание механизма работы, требований и процедур получения поможет вам эффективно использовать этот финансовый инструмент для развития своего бизнеса и обеспечения безопасности сделок.</p></div>';
        
        // Объединяем контент
        $full_content = implode("\n\n", $content);
        
        // Создаем мета-данные
        $meta_data = [
            'title' => 'Что такое банковская гарантия: полное руководство | BizFin Pro',
            'meta_description' => 'Узнайте все о банковских гарантиях: определение, процесс получения, требования, стоимость. Экспертные советы и практические рекомендации для бизнеса.',
            'focus_keyword' => $keyword,
            'og_title' => 'Что такое банковская гарантия: полное руководство',
            'og_description' => 'Подробное руководство по банковским гарантиям с практическими советами и примерами для бизнеса.'
        ];
        
        return [
            'content' => $full_content,
            'meta_data' => $meta_data,
            'structure' => $table_of_contents,
            'user_instruction' => $user_instruction
        ];
    }
    
    /**
     * Построение контента раздела
     */
    private function build_section_content($section) {
        $section_content = [];
        
        // H2 заголовок
        $section_content[] = '<h2>' . esc_html($section['heading']) . '</h2>';
        
        // Основной контент раздела
        $section_text = $this->generate_section_text($section);
        $section_content[] = $section_text;
        
        // Подразделы если есть
        if (!empty($section['subheadings'])) {
            foreach ($section['subheadings'] as $subheading) {
                $section_content[] = '<h3>' . esc_html($subheading) . '</h3>';
                $section_content[] = '<p>' . $this->generate_subsection_text($subheading) . '</p>';
            }
        }
        
        return implode("\n\n", $section_content);
    }
    
    /**
     * Генерация текста раздела
     */
    private function generate_section_text($section) {
        $section_type = $this->identify_section_type($section['heading']);
        
        $templates = [
            'definition' => 'В данном разделе мы подробно разберем определение и основные понятия, связанные с банковскими гарантиями. Это фундаментальные знания, которые помогут вам понять суть процесса и механизм работы данного финансового инструмента.',
            'process' => 'Процесс получения банковской гарантии включает несколько ключевых этапов. Рассмотрим каждый из них подробно, чтобы вы понимали последовательность действий и могли эффективно планировать свои шаги.',
            'requirements' => 'Для получения банковской гарантии необходимо выполнить ряд требований. Мы рассмотрим все условия и критерии, которые нужно учесть, чтобы успешно оформить гарантию.',
            'cost' => 'Стоимость банковской гарантии зависит от множества факторов. Проанализируем все аспекты ценообразования и способы оптимизации расходов при оформлении гарантии.',
            'mistakes' => 'При работе с банковскими гарантиями часто допускаются типичные ошибки. Изучив этот раздел, вы сможете их избежать и сэкономить время и деньги.',
            'case_study' => 'Рассмотрим практический пример использования банковской гарантии на реальном кейсе. Это поможет лучше понять применение теоретических знаний на практике.',
            'conclusion' => 'Подведем итоги по теме банковских гарантий. Сформулируем ключевые выводы и практические рекомендации для эффективного использования данного инструмента.'
        ];
        
        $template = $templates[$section_type] ?? $templates['definition'];
        
        // Расширяем текст до нужного объема
        $expanded_text = $this->expand_section_text($template, $section);
        
        return '<p>' . $expanded_text . '</p>';
    }
    
    /**
     * Расширение текста раздела
     */
    private function expand_section_text($base_text, $section) {
        $expansions = [
            'Детальный анализ показывает, что банковские гарантии являются важным инструментом для современного бизнеса.',
            'Практический опыт подтверждает эффективность использования банковских гарантий в различных ситуациях.',
            'Эксперты рекомендуют учитывать все нюансы при работе с банковскими гарантиями, чтобы избежать проблем.',
            'Статистика показывает, что правильное применение банковских гарантий приводит к положительным результатам.',
            'Важно понимать, что банковские гарантии требуют профессионального подхода и внимания к деталям.'
        ];
        
        $expanded_text = $base_text;
        foreach ($expansions as $expansion) {
            $expanded_text .= ' ' . $expansion;
        }
        
        // Добавляем конкретные детали в зависимости от типа раздела
        $expanded_text .= ' ' . $this->add_section_specific_content($section);
        
        return $expanded_text;
    }
    
    /**
     * Добавление специфичного контента для раздела
     */
    private function add_section_specific_content($section) {
        $section_type = $this->identify_section_type($section['heading']);
        
        switch ($section_type) {
            case 'definition':
                return 'Основные участники процесса: банк-гарант, принципал и бенефициар. Каждый из них имеет свои права и обязанности, которые необходимо понимать для эффективного взаимодействия.';
                
            case 'process':
                return 'Процесс включает: подачу заявления, рассмотрение банком, подписание договора и выдачу гарантии. Каждый этап имеет свои особенности и требования.';
                
            case 'requirements':
                return 'Основные требования: финансовая стабильность, положительная репутация, наличие необходимых документов. Банк тщательно проверяет все аспекты перед выдачей гарантии.';
                
            case 'cost':
                return 'Стоимость варьируется от 1% до 5% годовых от суммы гарантии в зависимости от банка и условий. Факторы влияния: сумма, срок, риски, репутация заявителя.';
                
            case 'mistakes':
                return 'Типичные ошибки: неправильный выбор банка, неточное заполнение документов, игнорирование сроков. Избежать их поможет тщательная подготовка и консультация с экспертами.';
                
            case 'case_study':
                return 'Практический пример показывает реальные результаты применения банковской гарантии в бизнесе. Анализ кейса поможет понять эффективность инструмента.';
                
            default:
                return 'Дополнительная информация поможет лучше понять все аспекты данной темы и применить знания на практике.';
        }
    }
    
    /**
     * Генерация текста подраздела
     */
    private function generate_subsection_text($subheading) {
        $subsection_templates = [
            'Банк' => 'Банк-гарант является ключевым участником процесса, который берет на себя обязательство по выплате денежных средств в случае невыполнения принципалом своих обязательств.',
            'Принципал' => 'Принципал — это сторона, которая обращается в банк за получением гарантии и обязуется выполнить определенные условия договора.',
            'Бенефициар' => 'Бенефициар — это сторона, которая получает денежные средства по банковской гарантии в случае невыполнения принципалом своих обязательств.',
            'Заявка' => 'Подача заявления является первым шагом в процессе получения банковской гарантии. Необходимо предоставить все требуемые документы и информацию.',
            'Исполнение' => 'Исполнение банковской гарантии происходит при наступлении определенных условий, указанных в договоре гарантии.',
            'Гарантийные обязательства' => 'Гарантийные обязательства банка включают в себя обязательство по выплате денежных средств в установленные сроки и в полном объеме.'
        ];
        
        return $subsection_templates[$subheading] ?? 'Данный подраздел содержит важную информацию, которая поможет лучше понять рассматриваемую тему.';
    }
    
    /**
     * Построение FAQ секции
     */
    private function build_faq_section() {
        $faq_items = [
            ['question' => 'Чем банковская гарантия отличается от залога?', 'answer' => 'Банковская гарантия — это обязательство банка выплатить денежные средства, а залог — это имущество, которое может быть реализовано для погашения долга.'],
            ['question' => 'Может ли физическое лицо получить банковскую гарантию?', 'answer' => 'Как правило, банковские гарантии выдаются юридическим лицам и индивидуальным предпринимателям. Физические лица могут получить гарантию в исключительных случаях.'],
            ['question' => 'Сколько времени занимает оформление банковской гарантии?', 'answer' => 'Срок оформления банковской гарантии обычно составляет от 3 до 10 рабочих дней в зависимости от банка и сложности заявки.'],
            ['question' => 'Какие документы нужны для получения банковской гарантии?', 'answer' => 'Основные документы: заявление, учредительные документы, финансовая отчетность, документы по основному обязательству и справки о состоянии расчетов.']
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
    
    /**
     * Создание базового поста
     */
    private function create_basic_post($keyword, $article_content) {
        $post_data = [
            'post_title' => $article_content['meta_data']['title'],
            'post_content' => $article_content['content'],
            'post_status' => 'draft',
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'post_excerpt' => $article_content['meta_data']['meta_description'],
            'meta_input' => [
                '_bsag_generated' => true,
                '_bsag_keyword' => $keyword,
                '_bsag_test_article' => true,
                '_bsag_generation_timestamp' => current_time('mysql')
            ]
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Устанавливаем мета-данные Yoast SEO
            update_post_meta($post_id, '_yoast_wpseo_title', $article_content['meta_data']['title']);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $article_content['meta_data']['meta_description']);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
            
            // Устанавливаем категорию "Тестовые статьи"
            $category_id = $this->get_or_create_category('Тестовые статьи');
            wp_set_post_categories($post_id, [$category_id]);
            
            // Устанавливаем теги
            $tags = ['банковские гарантии', 'бизнес', 'финансы', 'тестовая статья'];
            wp_set_post_tags($post_id, $tags);
        }
        
        return $post_id;
    }
    
    /**
     * Получение или создание категории
     */
    private function get_or_create_category($category_name) {
        $category = get_category_by_slug(sanitize_title($category_name));
        
        if (!$category) {
            $category_id = wp_create_category($category_name);
            return $category_id;
        }
        
        return $category->term_id;
    }
    
    /**
     * Определение типа раздела
     */
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
}

// Инициализация генератора тестовых статей
new BizFin_Test_Article_Generator();