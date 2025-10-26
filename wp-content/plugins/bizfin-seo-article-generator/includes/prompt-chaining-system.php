<?php
/**
 * BizFin SEO Article Generator - Prompt Chaining System
 * Система цепочек промптов на основе архитектуры ALwrity
 */

if (!defined('ABSPATH')) exit;

class BizFin_Prompt_Chaining_System {
    
    private $chaining_phases = [
        'phase_1' => 'data_analysis',
        'phase_2' => 'structure_generation',
        'phase_3' => 'content_creation',
        'phase_4' => 'optimization_validation'
    ];
    
    private $quality_gates = [
        'content_uniqueness' => true,
        'factual_accuracy' => 0.85,
        'professional_tone' => 0.80,
        'industry_relevance' => 0.90,
        'seo_optimization' => 0.85
    ];
    
    public function __construct() {
        // Регистрация AJAX обработчиков для цепочек промптов
        add_action('wp_ajax_bsag_start_prompt_chaining', [$this, 'ajax_start_prompt_chaining']);
        add_action('wp_ajax_bsag_execute_prompt_phase', [$this, 'ajax_execute_prompt_phase']);
        add_action('wp_ajax_bsag_validate_quality_gate', [$this, 'ajax_validate_quality_gate']);
    }
    
    /**
     * Запуск системы цепочек промптов
     */
    public function start_prompt_chaining($keyword, $keyword_data) {
        $session_id = $this->create_chaining_session($keyword, $keyword_data);
        
        // Начинаем с Phase 1: Data Analysis
        $phase_1_result = $this->execute_phase_1_data_analysis($keyword, $keyword_data);
        
        // Сохраняем результат фазы
        $this->save_phase_result($session_id, 'phase_1', $phase_1_result);
        
        return [
            'session_id' => $session_id,
            'current_phase' => 'phase_1',
            'phase_result' => $phase_1_result,
            'next_phase' => 'phase_2',
            'progress' => 25
        ];
    }
    
    /**
     * Phase 1: Data Analysis & Strategy Foundation
     */
    private function execute_phase_1_data_analysis($keyword, $keyword_data) {
        $prompt = $this->build_phase_1_prompt($keyword, $keyword_data);
        
        $analysis_result = $this->call_ai_api($prompt, 'data_analysis');
        
        return [
            'keyword_analysis' => $this->parse_keyword_analysis($analysis_result),
            'audience_analysis' => $this->parse_audience_analysis($analysis_result),
            'competitive_analysis' => $this->parse_competitive_analysis($analysis_result),
            'content_strategy' => $this->parse_content_strategy($analysis_result),
            'quality_gates_passed' => $this->validate_phase_1_quality_gates($analysis_result)
        ];
    }
    
    /**
     * Phase 2: Structure Generation
     */
    private function execute_phase_2_structure_generation($keyword, $keyword_data, $phase_1_result) {
        $prompt = $this->build_phase_2_prompt($keyword, $keyword_data, $phase_1_result);
        
        $structure_result = $this->call_ai_api($prompt, 'structure_generation');
        
        return [
            'article_structure' => $this->parse_article_structure($structure_result),
            'heading_hierarchy' => $this->parse_heading_hierarchy($structure_result),
            'content_sections' => $this->parse_content_sections($structure_result),
            'seo_optimization' => $this->parse_seo_optimization($structure_result),
            'quality_gates_passed' => $this->validate_phase_2_quality_gates($structure_result)
        ];
    }
    
    /**
     * Phase 3: Content Creation
     */
    private function execute_phase_3_content_creation($keyword, $keyword_data, $phase_1_result, $phase_2_result) {
        $prompt = $this->build_phase_3_prompt($keyword, $keyword_data, $phase_1_result, $phase_2_result);
        
        $content_result = $this->call_ai_api($prompt, 'content_creation');
        
        return [
            'article_content' => $this->parse_article_content($content_result),
            'meta_data' => $this->parse_meta_data($content_result),
            'internal_links' => $this->parse_internal_links($content_result),
            'cta_blocks' => $this->parse_cta_blocks($content_result),
            'faq_section' => $this->parse_faq_section($content_result),
            'quality_gates_passed' => $this->validate_phase_3_quality_gates($content_result)
        ];
    }
    
    /**
     * Phase 4: Optimization & Validation
     */
    private function execute_phase_4_optimization_validation($keyword, $keyword_data, $phase_1_result, $phase_2_result, $phase_3_result) {
        $prompt = $this->build_phase_4_prompt($keyword, $keyword_data, $phase_1_result, $phase_2_result, $phase_3_result);
        
        $optimization_result = $this->call_ai_api($prompt, 'optimization_validation');
        
        return [
            'final_content' => $this->parse_final_content($optimization_result),
            'seo_optimization' => $this->parse_final_seo_optimization($optimization_result),
            'quality_assessment' => $this->parse_quality_assessment($optimization_result),
            'recommendations' => $this->parse_final_recommendations($optimization_result),
            'quality_gates_passed' => $this->validate_phase_4_quality_gates($optimization_result)
        ];
    }
    
    /**
     * Построение промпта для Phase 1: Data Analysis
     */
    private function build_phase_1_prompt($keyword, $keyword_data) {
        return "Ты эксперт по банковским гарантиям и SEO-аналитик. Проведи комплексный анализ ключевого слова '{$keyword}' для создания высококачественной статьи.

КОНТЕКСТ:
- Ключевое слово: {$keyword}
- Интент: {$keyword_data['intent']}
- Целевая аудитория: {$keyword_data['target_audience']}
- Структура: {$keyword_data['structure']}

ЗАДАЧИ ФАЗЫ 1 - АНАЛИЗ ДАННЫХ:

1. АНАЛИЗ КЛЮЧЕВОГО СЛОВА:
- Определи семантическое ядро ключевого слова
- Выяви LSI-ключевые слова и синонимы
- Проанализируй поисковые интенты пользователей
- Оцени конкурентность и сложность

2. АНАЛИЗ ЦЕЛЕВОЙ АУДИТОРИИ:
- Определи сегменты аудитории
- Выяви болевые точки и потребности
- Проанализируй уровень экспертизы
- Определи мотивы и цели

3. КОНКУРЕНТНЫЙ АНАЛИЗ:
- Изучи топ-10 конкурентов по ключевому слову
- Выяви пробелы в контенте
- Определи возможности для дифференциации
- Проанализируй сильные и слабые стороны конкурентов

4. СТРАТЕГИЯ КОНТЕНТА:
- Определи уникальный угол подачи
- Выбери оптимальный формат статьи
- Определи ключевые сообщения
- Сформулируй ценностное предложение

ФОРМАТ ОТВЕТА (JSON):
{
  \"keyword_analysis\": {
    \"semantic_core\": [\"ключ1\", \"ключ2\"],
    \"lsi_keywords\": [\"lsi1\", \"lsi2\"],
    \"search_intents\": [\"интент1\", \"интент2\"],
    \"competition_level\": \"высокий/средний/низкий\"
  },
  \"audience_analysis\": {
    \"segments\": [\"сегмент1\", \"сегмент2\"],
    \"pain_points\": [\"боль1\", \"боль2\"],
    \"expertise_level\": \"начинающий/средний/экспертный\",
    \"goals\": [\"цель1\", \"цель2\"]
  },
  \"competitive_analysis\": {
    \"top_competitors\": [\"конкурент1\", \"конкурент2\"],
    \"content_gaps\": [\"пробел1\", \"пробел2\"],
    \"differentiation_opportunities\": [\"возможность1\", \"возможность2\"],
    \"competitive_advantages\": [\"преимущество1\", \"преимущество2\"]
  },
  \"content_strategy\": {
    \"unique_angle\": \"уникальный угол\",
    \"content_format\": \"формат\",
    \"key_messages\": [\"сообщение1\", \"сообщение2\"],
    \"value_proposition\": \"ценностное предложение\"
  }
}

ВАЖНО: Анализ должен быть глубоким, конкретным и основанным на реальных данных рынка банковских гарантий.";
    }
    
    /**
     * Построение промпта для Phase 2: Structure Generation
     */
    private function build_phase_2_prompt($keyword, $keyword_data, $phase_1_result) {
        return "На основе анализа Phase 1 создай оптимальную структуру статьи для ключевого слова '{$keyword}'.

ДАННЫЕ ИЗ PHASE 1:
" . json_encode($phase_1_result, JSON_UNESCAPED_UNICODE) . "

ЗАДАЧИ ФАЗЫ 2 - ГЕНЕРАЦИЯ СТРУКТУРЫ:

1. СТРУКТУРА СТАТЬИ:
- Создай логичную иерархию заголовков H1-H2-H3
- ОБЯЗАТЕЛЬНО: Минимум 2500 слов для всей статьи
- Распредели длину по секциям: введение 350-500 слов, каждая H2 секция 300-450 слов, FAQ 400-600 слов, CTA 80-150 слов
- Спланируй поток информации от проблемы к решению
- Учти потребности целевой аудитории

2. SEO ОПТИМИЗАЦИЯ:
- Распредели ключевые слова по заголовкам
- Оптимизируй плотность ключевых слов
- Спланируй внутренние ссылки
- Определи места для CTA блоков

3. КОНТЕНТНЫЕ РАЗДЕЛЫ:
- Создай детальный план каждого раздела
- Определи ключевые моменты для освещения
- Спланируй примеры и кейсы
- Подготовь FAQ секцию

4. ВИЗУАЛЬНЫЕ ЭЛЕМЕНТЫ:
- Определи места для изображений
- Спланируй инфографику и схемы
- Подготовь предложения по визуальному контенту

ФОРМАТ ОТВЕТА (JSON):
{
  \"article_structure\": {
    \"h1\": \"Основной заголовок\",
    \"h2_sections\": [
      {
        \"heading\": \"Заголовок H2\",
        \"subheadings\": [\"H3.1\", \"H3.2\"],
        \"key_points\": [\"пункт1\", \"пункт2\"],
        \"target_words\": 350,
        \"keywords\": [\"ключ1\", \"ключ2\"]
      }
    ],
    \"total_word_count\": 2500
  },
  \"seo_optimization\": {
    \"keyword_distribution\": {
      \"h1\": \"ключевое слово\",
      \"h2_keywords\": [\"ключ1\", \"ключ2\"],
      \"target_density\": 2.5
    },
    \"internal_links\": [
      {\"text\": \"текст ссылки\", \"url\": \"/url/\", \"anchor\": \"якорь\"}
    ],
    \"cta_placement\": [\"after_intro\", \"middle\", \"conclusion\"]
  },
  \"content_sections\": {
    \"introduction\": \"план введения\",
    \"main_sections\": [\"план раздела1\", \"план раздела2\"],
    \"conclusion\": \"план заключения\",
    \"faq_section\": [\"вопрос1\", \"вопрос2\"]
  }
}

ВАЖНО: Структура должна быть логичной, SEO-оптимизированной и ориентированной на пользователя.";
    }
    
    /**
     * Построение промпта для Phase 3: Content Creation
     */
    private function build_phase_3_prompt($keyword, $keyword_data, $phase_1_result, $phase_2_result) {
        return "Создай высококачественный контент статьи на основе структуры Phase 2 для ключевого слова '{$keyword}'.

ДАННЫЕ ИЗ PHASE 1:
" . json_encode($phase_1_result, JSON_UNESCAPED_UNICODE) . "

ДАННЫЕ ИЗ PHASE 2:
" . json_encode($phase_2_result, JSON_UNESCAPED_UNICODE) . "

ЗАДАЧИ ФАЗЫ 3 - СОЗДАНИЕ КОНТЕНТА:

1. СОЗДАНИЕ СТАТЬИ:
- Напиши полный текст статьи согласно структуре
- КРИТИЧЕСКИ ВАЖНО: Статья должна содержать минимум 2500 слов
- Используй профессиональную терминологию банковских гарантий
- Включи практические примеры и кейсы для расширения контента
- Создай ценность для целевой аудитории
- Добавляй детали, пошаговые инструкции, чек-листы для достижения нужной длины

2. МЕТА-ДАННЫЕ:
- Создай SEO-оптимизированный заголовок
- Напиши привлекательное мета-описание
- Подготовь Open Graph теги
- Создай структурированные данные Schema.org

3. ВНУТРЕННИЕ ССЫЛКИ:
- Добавь релевантные внутренние ссылки
- Используй естественные анкоры
- Ссылайся на полезные страницы сайта
- Включи ссылки на калькулятор и формы

4. CTA БЛОКИ:
- Создай призывы к действию в нужных местах
- Адаптируй CTA под интент пользователя
- Включи контактную информацию
- Добавь ссылки на услуги

ФОРМАТ ОТВЕТА (JSON):
{
  \"article_content\": \"<h1>Заголовок</h1><p>Контент статьи...</p>\",
  \"meta_data\": {
    \"title\": \"SEO заголовок\",
    \"meta_description\": \"Мета-описание\",
    \"og_title\": \"OG заголовок\",
    \"og_description\": \"OG описание\",
    \"schema_markup\": \"JSON-LD разметка\"
  },
  \"internal_links\": [
    {\"text\": \"текст\", \"url\": \"/url/\", \"position\": \"after_intro\"}
  ],
  \"cta_blocks\": [
    {\"type\": \"calculator\", \"text\": \"Рассчитать\", \"position\": \"middle\"}
  ],
  \"faq_section\": [
    {\"question\": \"Вопрос\", \"answer\": \"Ответ\"}
  ]
}

ВАЖНО: Контент должен быть уникальным, экспертного уровня и максимально полезным для читателей.";
    }
    
    /**
     * Построение промпта для Phase 4: Optimization & Validation
     */
    private function build_phase_4_prompt($keyword, $keyword_data, $phase_1_result, $phase_2_result, $phase_3_result) {
        return "Проведи финальную оптимизацию и валидацию статьи для ключевого слова '{$keyword}'.

ДАННЫЕ ИЗ ПРЕДЫДУЩИХ ФАЗ:
Phase 1: " . json_encode($phase_1_result, JSON_UNESCAPED_UNICODE) . "
Phase 2: " . json_encode($phase_2_result, JSON_UNESCAPED_UNICODE) . "
Phase 3: " . json_encode($phase_3_result, JSON_UNESCAPED_UNICODE) . "

ЗАДАЧИ ФАЗЫ 4 - ОПТИМИЗАЦИЯ И ВАЛИДАЦИЯ:

1. ВАЛИДАЦИЯ ДЛИНЫ СТАТЬИ:
- ОБЯЗАТЕЛЬНО: Подсчитай количество слов в статье (минимум 2500 слов)
- Если недобор: добавь дополнительные примеры, детали, подзаголовки
- Проверь распределение слов по секциям согласно плану
- Убедись в отсутствии \"воды\" - только качественный контент

2. SEO ОПТИМИЗАЦИЯ:
- Проверь и оптимизируй плотность ключевых слов
- Убедись в правильной структуре заголовков
- Проверь внутренние ссылки и анкоры
- Оптимизируй мета-данные

2. КАЧЕСТВО КОНТЕНТА:
- Проверь уникальность контента
- Оцени профессиональный тон
- Проверь фактическую точность
- Убедись в релевантности отрасли

3. ПОЛЬЗА ДЛЯ ПОЛЬЗОВАТЕЛЯ:
- Проверь ценность контента для ЦА
- Убедись в практической применимости
- Проверь логичность изложения
- Оцени читаемость и доступность

4. ФИНАЛЬНАЯ ВАЛИДАЦИЯ:
- Проверь соответствие всем требованиям
- Убедись в отсутствии ошибок
- Проверь соответствие бренду
- Подготовь рекомендации по улучшению

ФОРМАТ ОТВЕТА (JSON):
{
  \"final_content\": \"<h1>Оптимизированный заголовок</h1><p>Финальный контент...</p>\",
  \"seo_optimization\": {
    \"keyword_density\": 2.3,
    \"heading_structure\": \"correct\",
    \"internal_links_count\": 5,
    \"meta_optimization\": \"optimized\"
  },
  \"quality_assessment\": {
    \"uniqueness_score\": 0.95,
    \"professional_tone_score\": 0.88,
    \"factual_accuracy_score\": 0.92,
    \"industry_relevance_score\": 0.94,
    \"overall_quality_score\": 0.92
  },
  \"recommendations\": [
    {\"category\": \"seo\", \"message\": \"рекомендация\", \"priority\": \"high\"}
  ],
  \"validation_passed\": true
}

ВАЖНО: Финальная статья должна соответствовать всем критериям качества и быть готовой к публикации.";
    }
    
    /**
     * Вызов AI API
     */
    private function call_ai_api($prompt, $context) {
        // Здесь будет интеграция с OpenAI API
        // Пока возвращаем заглушку
        return [
            'content' => 'Заглушка для ' . $context,
            'status' => 'success'
        ];
    }
    
    /**
     * Создание сессии цепочки промптов
     */
    private function create_chaining_session($keyword, $keyword_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_prompt_sessions';
        
        $session_id = wp_generate_uuid4();
        
        $wpdb->insert(
            $table_name,
            [
                'session_id' => $session_id,
                'keyword' => $keyword,
                'keyword_data' => json_encode($keyword_data),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
        
        return $session_id;
    }
    
    /**
     * Сохранение результата фазы
     */
    private function save_phase_result($session_id, $phase, $result) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_prompt_sessions';
        
        $wpdb->update(
            $table_name,
            [
                $phase . '_result' => json_encode($result),
                'updated_at' => current_time('mysql')
            ],
            ['session_id' => $session_id],
            ['%s', '%s'],
            ['%s']
        );
    }
    
    /**
     * AJAX обработчик запуска цепочки промптов
     */
    public function ajax_start_prompt_chaining() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        $keyword_data = $_POST['keyword_data'];
        
        $result = $this->start_prompt_chaining($keyword, $keyword_data);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX обработчик выполнения фазы
     */
    public function ajax_execute_prompt_phase() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $session_id = sanitize_text_field($_POST['session_id']);
        $phase = sanitize_text_field($_POST['phase']);
        
        // Получаем данные сессии
        $session_data = $this->get_session_data($session_id);
        
        // Выполняем соответствующую фазу
        switch ($phase) {
            case 'phase_2':
                $result = $this->execute_phase_2_structure_generation(
                    $session_data['keyword'],
                    json_decode($session_data['keyword_data'], true),
                    json_decode($session_data['phase_1_result'], true)
                );
                break;
            case 'phase_3':
                $result = $this->execute_phase_3_content_creation(
                    $session_data['keyword'],
                    json_decode($session_data['keyword_data'], true),
                    json_decode($session_data['phase_1_result'], true),
                    json_decode($session_data['phase_2_result'], true)
                );
                break;
            case 'phase_4':
                $result = $this->execute_phase_4_optimization_validation(
                    $session_data['keyword'],
                    json_decode($session_data['keyword_data'], true),
                    json_decode($session_data['phase_1_result'], true),
                    json_decode($session_data['phase_2_result'], true),
                    json_decode($session_data['phase_3_result'], true)
                );
                break;
            default:
                wp_send_json_error('Неизвестная фаза');
                return;
        }
        
        // Сохраняем результат
        $this->save_phase_result($session_id, $phase, $result);
        
        wp_send_json_success($result);
    }
    
    /**
     * Получение данных сессии
     */
    private function get_session_data($session_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_prompt_sessions';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE session_id = %s", $session_id),
            ARRAY_A
        );
    }
    
    /**
     * Парсинг результатов (заглушки)
     */
    private function parse_keyword_analysis($result) { return []; }
    private function parse_audience_analysis($result) { return []; }
    private function parse_competitive_analysis($result) { return []; }
    private function parse_content_strategy($result) { return []; }
    private function parse_article_structure($result) { return []; }
    private function parse_heading_hierarchy($result) { return []; }
    private function parse_content_sections($result) { return []; }
    private function parse_seo_optimization($result) { return []; }
    private function parse_article_content($result) { return ''; }
    private function parse_meta_data($result) { return []; }
    private function parse_internal_links($result) { return []; }
    private function parse_cta_blocks($result) { return []; }
    private function parse_faq_section($result) { return []; }
    private function parse_final_content($result) { return ''; }
    private function parse_final_seo_optimization($result) { return []; }
    private function parse_quality_assessment($result) { return []; }
    private function parse_final_recommendations($result) { return []; }
    
    /**
     * Валидация Quality Gates (заглушки)
     */
    private function validate_phase_1_quality_gates($result) { return true; }
    private function validate_phase_2_quality_gates($result) { return true; }
    private function validate_phase_3_quality_gates($result) { return true; }
    private function validate_phase_4_quality_gates($result) { return true; }
    
    /**
     * Валидация длины статьи
     */
    public function validate_article_length($content, $min_words = 2500) {
        $word_count = $this->count_words($content);
        
        return [
            'word_count' => $word_count,
            'min_required' => $min_words,
            'meets_requirement' => $word_count >= $min_words,
            'deficit' => max(0, $min_words - $word_count),
            'percentage' => round(($word_count / $min_words) * 100, 1)
        ];
    }
    
    /**
     * Подсчет слов в контенте
     */
    private function count_words($content) {
        // Удаляем HTML теги
        $clean_content = strip_tags($content);
        
        // Удаляем служебные элементы
        $clean_content = preg_replace('/<!--.*?-->/s', '', $clean_content);
        $clean_content = preg_replace('/<script.*?<\/script>/s', '', $clean_content);
        $clean_content = preg_replace('/<style.*?<\/style>/s', '', $clean_content);
        
        // Декодируем HTML entities
        $clean_content = html_entity_decode($clean_content, ENT_QUOTES, 'UTF-8');
        
        // Нормализуем пробелы
        $clean_content = preg_replace('/\s+/', ' ', $clean_content);
        $clean_content = trim($clean_content);
        
        // Подсчитываем слова с учетом кириллицы
        $words = preg_split('/\p{L}+/u', $clean_content, -1, PREG_SPLIT_NO_EMPTY);
        
        return count($words);
    }
    
    /**
     * Генерация expansion prompt для доращивания статьи
     */
    public function generate_expansion_prompt($content, $target_section, $deficit_words) {
        return "Статья содержит недостаточно слов. Нужно добавить {$deficit_words} слов в раздел '{$target_section}'.
        
ТЕКУЩИЙ КОНТЕНТ РАЗДЕЛА:
{$content}

ЗАДАЧИ ДОРАЩИВАНИЯ:
1. Добавь практические примеры и кейсы
2. Включи пошаговые инструкции
3. Добавь детальные объяснения процессов
4. Создай чек-листы или списки
5. Добавь подзаголовки с дополнительным контентом

ВАЖНО: 
- Сохраняй качество контента
- Избегай \"воды\" и повторений
- Добавляй только полезную информацию
- Минимум {$deficit_words} слов

ФОРМАТ ОТВЕТА: Расширенный контент раздела с добавленными словами.";
    }
}

// Инициализация системы цепочек промптов
new BizFin_Prompt_Chaining_System();

