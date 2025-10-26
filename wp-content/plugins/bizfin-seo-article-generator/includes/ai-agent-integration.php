<?php
/**
 * BizFin SEO Article Generator - AI Agent Integration
 * Интеграция с ИИ-агентом для генерации статей
 */

if (!defined('ABSPATH')) exit;

class BizFin_AI_Agent_Integration {
    
    private $openai_api_key;
    private $openai_model = 'gpt-4';
    private $max_tokens = 4000;
    private $temperature = 0.7;
    
    public function __construct() {
        $this->openai_api_key = get_option('bsag_openai_api_key', '');
        
        // Регистрируем AJAX обработчики
        add_action('wp_ajax_bsag_ai_generate_article', [$this, 'ajax_generate_article']);
        add_action('wp_ajax_bsag_ai_analyze_keyword', [$this, 'ajax_analyze_keyword']);
        add_action('wp_ajax_bsag_ai_optimize_content', [$this, 'ajax_optimize_content']);
    }
    
    /**
     * Генерация статьи через ИИ-агента
     */
    public function generate_article($keyword, $keyword_data, $structure, $seo_requirements) {
        $prompt = $this->build_article_prompt($keyword, $keyword_data, $structure, $seo_requirements);
        
        $response = $this->call_openai_api($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $this->parse_article_response($response, $keyword);
    }
    
    /**
     * Построение промпта для генерации статьи
     */
    private function build_article_prompt($keyword, $keyword_data, $structure, $seo_requirements) {
        $prompt = "Ты профессиональный SEO-копирайтер и эксперт по банковским гарантиям. Создай высококачественную статью для сайта bizfin-pro.ru.\n\n";
        
        $prompt .= "КЛЮЧЕВОЕ СЛОВО: {$keyword}\n";
        $prompt .= "ИНТЕНТ ЗАПРОСА: {$keyword_data['intent']}\n";
        $prompt .= "СТРУКТУРА СТАТЬИ: {$keyword_data['structure']}\n";
        $prompt .= "ЦЕЛЕВАЯ АУДИТОРИЯ: {$keyword_data['target_audience']}\n";
        $prompt .= "ОБЪЕМ СТАТЬИ: {$keyword_data['word_count']} слов\n";
        $prompt .= "ТИП CTA: {$keyword_data['cta_type']}\n\n";
        
        $prompt .= "ТРЕБОВАНИЯ К СТРУКТУРЕ:\n";
        $prompt .= "H1: {$structure['h1']}\n";
        $prompt .= "Разделы H2:\n";
        foreach ($structure['h2_sections'] as $index => $section) {
            $prompt .= ($index + 1) . ". {$section}\n";
        }
        
        $prompt .= "\nSEO ТРЕБОВАНИЯ:\n";
        $prompt .= "- Длина Title: до {$seo_requirements['title_length']} символов\n";
        $prompt .= "- Мета-описание: до {$seo_requirements['meta_description_length']} символов\n";
        $prompt .= "- Минимум слов: {$seo_requirements['word_count_min']}\n";
        $prompt .= "- Плотность ключевых слов: {$seo_requirements['keyword_density'][0]}-{$seo_requirements['keyword_density'][1]}%\n";
        $prompt .= "- Внутренние ссылки: {$seo_requirements['internal_links'][0]}-{$seo_requirements['internal_links'][1]}\n";
        $prompt .= "- CTA блоки: {$seo_requirements['cta_blocks'][0]}-{$seo_requirements['cta_blocks'][1]}\n\n";
        
        $prompt .= "ТЕМАТИКА: Банковские гарантии, тендеры, государственные закупки, бизнес-финансы\n";
        $prompt .= "СТИЛЬ: Профессиональный, экспертный, полезный для ЦА\n";
        $prompt .= "ФОРМАТ: HTML с правильной структурой заголовков\n\n";
        
        $prompt .= "СОЗДАЙ СТАТЬЮ В СЛЕДУЮЩЕМ ФОРМАТЕ:\n";
        $prompt .= "```json\n";
        $prompt .= "{\n";
        $prompt .= '  "title": "SEO-заголовок статьи",' . "\n";
        $prompt .= '  "meta_description": "Мета-описание статьи",' . "\n";
        $prompt .= '  "h1": "Основной заголовок",' . "\n";
        $prompt .= '  "content": "HTML контент статьи с правильной структурой заголовков",' . "\n";
        $prompt .= '  "internal_links": [' . "\n";
        $prompt .= '    {"text": "Текст ссылки", "url": "/url/", "anchor": "якорь"}' . "\n";
        $prompt .= '  ],' . "\n";
        $prompt .= '  "cta_blocks": [' . "\n";
        $prompt .= '    {"type": "calculator", "text": "Рассчитать стоимость", "position": "after_intro"}' . "\n";
        $prompt .= '  ],' . "\n";
        $prompt .= '  "faq_section": [' . "\n";
        $prompt .= '    {"question": "Вопрос", "answer": "Ответ"}' . "\n";
        $prompt .= '  ],' . "\n";
        $prompt .= '  "schema_markup": "JSON-LD разметка для статьи"' . "\n";
        $prompt .= "}\n";
        $prompt .= "```\n\n";
        
        $prompt .= "ВАЖНО:\n";
        $prompt .= "1. Статья должна быть уникальной и не повторять существующий контент\n";
        $prompt .= "2. Используй ключевое слово естественно, без переспама\n";
        $prompt .= "3. Добавь практические примеры и кейсы\n";
        $prompt .= "4. Включи актуальную информацию по банковским гарантиям\n";
        $prompt .= "5. Создай ценность для читателя\n";
        $prompt .= "6. Оптимизируй под поисковые системы\n";
        $prompt .= "7. Добавь призывы к действию в нужных местах\n";
        
        return $prompt;
    }
    
    /**
     * Вызов OpenAI API
     */
    private function call_openai_api($prompt) {
        if (empty($this->openai_api_key)) {
            return new WP_Error('no_api_key', 'OpenAI API ключ не настроен');
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->openai_api_key,
            'Content-Type' => 'application/json'
        ];
        
        $body = [
            'model' => $this->openai_model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты профессиональный SEO-копирайтер и эксперт по банковским гарантиям. Создавай высококачественный, уникальный контент, оптимизированный под поисковые системы.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature
        ];
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 60
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Ошибка API: ' . $response_body);
        }
        
        $data = json_decode($response_body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'Неверный ответ от API');
        }
        
        return $data['choices'][0]['message']['content'];
    }
    
    /**
     * Парсинг ответа от ИИ
     */
    private function parse_article_response($response, $keyword) {
        // Извлекаем JSON из ответа
        $json_start = strpos($response, '```json');
        $json_end = strpos($response, '```', $json_start + 7);
        
        if ($json_start === false || $json_end === false) {
            return new WP_Error('invalid_format', 'Неверный формат ответа от ИИ');
        }
        
        $json_content = substr($response, $json_start + 7, $json_end - $json_start - 7);
        $article_data = json_decode($json_content, true);
        
        if (!$article_data) {
            return new WP_Error('invalid_json', 'Ошибка парсинга JSON ответа');
        }
        
        // Валидация обязательных полей
        $required_fields = ['title', 'meta_description', 'h1', 'content'];
        foreach ($required_fields as $field) {
            if (!isset($article_data[$field]) || empty($article_data[$field])) {
                return new WP_Error('missing_field', "Отсутствует обязательное поле: {$field}");
            }
        }
        
        // Добавляем метаданные
        $article_data['keyword'] = $keyword;
        $article_data['generated_at'] = current_time('mysql');
        $article_data['ai_model'] = $this->openai_model;
        $article_data['word_count'] = str_word_count(strip_tags($article_data['content']));
        
        return $article_data;
    }
    
    /**
     * Анализ ключевого слова
     */
    public function analyze_keyword($keyword) {
        $prompt = "Проанализируй ключевое слово '{$keyword}' для тематики банковских гарантий.\n\n";
        $prompt .= "Предоставь анализ в следующем формате:\n";
        $prompt .= "- Интент запроса (информационный/коммерческий/смешанный)\n";
        $prompt .= "- Целевая аудитория\n";
        $prompt .= "- Конкурентные преимущества\n";
        $prompt .= "- Рекомендуемая структура статьи\n";
        $prompt .= "- SEO-требования\n";
        $prompt .= "- Ключевые моменты для освещения\n\n";
        $prompt .= "Отвечай кратко и по делу.";
        
        $response = $this->call_openai_api($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }
    
    /**
     * Оптимизация контента
     */
    public function optimize_content($content, $keyword, $seo_requirements) {
        $prompt = "Оптимизируй следующий контент под SEO требования:\n\n";
        $prompt .= "КЛЮЧЕВОЕ СЛОВО: {$keyword}\n";
        $prompt .= "SEO ТРЕБОВАНИЯ:\n";
        $prompt .= "- Плотность ключевых слов: {$seo_requirements['keyword_density'][0]}-{$seo_requirements['keyword_density'][1]}%\n";
        $prompt .= "- Минимум слов: {$seo_requirements['word_count_min']}\n";
        $prompt .= "- Внутренние ссылки: {$seo_requirements['internal_links'][0]}-{$seo_requirements['internal_links'][1]}\n\n";
        $prompt .= "КОНТЕНТ ДЛЯ ОПТИМИЗАЦИИ:\n";
        $prompt .= $content . "\n\n";
        $prompt .= "Предоставь оптимизированную версию контента.";
        
        $response = $this->call_openai_api($prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }
    
    /**
     * AJAX обработчик генерации статьи
     */
    public function ajax_generate_article() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        
        if (empty($keyword)) {
            wp_send_json_error('Ключевое слово не указано');
        }
        
        // Получаем данные ключевого слова из матрицы
        $seo_matrix = get_option('bsag_seo_matrix', []);
        
        if (!isset($seo_matrix['keywords'][$keyword])) {
            wp_send_json_error('Данные ключевого слова не найдены');
        }
        
        $keyword_data = $seo_matrix['keywords'][$keyword];
        $structure = $seo_matrix['article_structures'][$keyword_data['structure']];
        $seo_requirements = $seo_matrix['seo_requirements'];
        
        // Генерируем статью
        $result = $this->generate_article($keyword, $keyword_data, $structure, $seo_requirements);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Сохраняем статью в базу данных
        $this->save_generated_article($result);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX обработчик анализа ключевого слова
     */
    public function ajax_analyze_keyword() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        
        if (empty($keyword)) {
            wp_send_json_error('Ключевое слово не указано');
        }
        
        $analysis = $this->analyze_keyword($keyword);
        
        if (is_wp_error($analysis)) {
            wp_send_json_error($analysis->get_error_message());
        }
        
        wp_send_json_success(['analysis' => $analysis]);
    }
    
    /**
     * AJAX обработчик оптимизации контента
     */
    public function ajax_optimize_content() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        $content = wp_kses_post($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);
        
        if (empty($content) || empty($keyword)) {
            wp_send_json_error('Контент или ключевое слово не указаны');
        }
        
        $seo_requirements = get_option('bsag_seo_requirements', []);
        
        $optimized_content = $this->optimize_content($content, $keyword, $seo_requirements);
        
        if (is_wp_error($optimized_content)) {
            wp_send_json_error($optimized_content->get_error_message());
        }
        
        wp_send_json_success(['optimized_content' => $optimized_content]);
    }
    
    /**
     * Сохранение сгенерированной статьи
     */
    private function save_generated_article($article_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_articles';
        
        $wpdb->insert(
            $table_name,
            [
                'keyword' => $article_data['keyword'],
                'article_content' => json_encode($article_data),
                'seo_meta' => json_encode([
                    'title' => $article_data['title'],
                    'meta_description' => $article_data['meta_description'],
                    'word_count' => $article_data['word_count']
                ]),
                'status' => 'generated',
                'ai_agent_response' => json_encode($article_data),
                'generated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Получение статистики генерации
     */
    public function get_generation_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_articles';
        
        $stats = [
            'total_articles' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'published_articles' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'published'"),
            'draft_articles' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'draft'"),
            'generated_articles' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'generated'"),
            'recent_articles' => $wpdb->get_results("SELECT keyword, generated_at FROM $table_name ORDER BY generated_at DESC LIMIT 10")
        ];
        
        return $stats;
    }
    
    /**
     * Настройка API ключа
     */
    public function set_api_key($api_key) {
        $this->openai_api_key = $api_key;
        update_option('bsag_openai_api_key', $api_key);
    }
    
    /**
     * Получение API ключа
     */
    public function get_api_key() {
        return $this->openai_api_key;
    }
    
    /**
     * Проверка статуса API
     */
    public function check_api_status() {
        if (empty($this->openai_api_key)) {
            return ['status' => 'error', 'message' => 'API ключ не настроен'];
        }
        
        // Простой тестовый запрос
        $test_response = $this->call_openai_api('Тест подключения. Ответь одним словом: "OK"');
        
        if (is_wp_error($test_response)) {
            return ['status' => 'error', 'message' => $test_response->get_error_message()];
        }
        
        return ['status' => 'success', 'message' => 'API работает корректно'];
    }
}

// Инициализация класса
new BizFin_AI_Agent_Integration();

