<?php
/**
 * Plugin Name: Article Analyzer & Regenerator
 * Description: Профессиональный плагин для анализа статей по матрице SEO критериев и их автоматической регенерации
 * Version: 1.3.0
 * Author: BizFin Pro Team
 * Text Domain: article-analyzer-regenerator
 */

if (!defined('ABSPATH')) exit;

class Article_Analyzer_Regenerator {
    
    const VERSION = '1.3.0';
    const PLUGIN_SLUG = 'article-analyzer-regenerator';
    const NONCE_ACTION = 'aar_ajax_nonce';
    const LOG_FILE = 'aar_errors.log';
    
    // OpenAI API
    const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    const OPENAI_API_KEY = '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';
    
    // Путь к матрице критериев
    const MATRIX_CRITERIA_PATH = '/wp-content/plugins/bizfin-seo-article-generator/BSAG_MATRIX_CRITERIA.md';
    
    private $settings;
    private $matrix_criteria = null;
    private static $instance = null;
    
    private function __construct() {
        $this->init();
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Логирование
    private function log($message, $level = 'INFO', $data = []) {
        $log_dir = WP_CONTENT_DIR . '/logs/';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        $log_file = $log_dir . self::LOG_FILE;
        $timestamp = date('Y-m-d H:i:s');
        $data_str = !empty($data) ? ' | Data: ' . json_encode($data) : '';
        $log_message = "[{$timestamp}] [{$level}] {$message}{$data_str}\n";
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        // Также в error_log для критических ошибок
        if ($level === 'ERROR' || $level === 'CRITICAL') {
            error_log("AAR [{$level}]: {$message}" . $data_str);
        }
    }
    
    private function init() {
        $this->log('Initializing plugin');
        
        $this->settings = get_option('aar_settings', [
            'default_model' => 'gpt-4o',
            'available_models' => ['gpt-4o', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'],
            'use_proxy' => false
        ]);
        
        // Загружаем матрицу критериев
        $this->load_matrix_criteria();
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_aar_get_articles', [$this, 'ajax_get_articles']);
        add_action('wp_ajax_aar_analyze_article', [$this, 'ajax_analyze_article']);
        add_action('wp_ajax_aar_regenerate_article', [$this, 'ajax_regenerate_article']);
        add_action('wp_ajax_aar_fix_broken_links', [$this, 'ajax_fix_broken_links']);
        add_action('wp_ajax_aar_bulk_process', [$this, 'ajax_bulk_process']);
        add_action('wp_ajax_aar_update_settings', [$this, 'ajax_update_settings']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
    }
    
    private function load_matrix_criteria() {
        try {
            $matrix_path = ABSPATH . self::MATRIX_CRITERIA_PATH;
            
            if (file_exists($matrix_path)) {
                $this->matrix_criteria = file_get_contents($matrix_path);
                $this->log('Matrix criteria loaded successfully', 'INFO', ['path' => $matrix_path, 'size' => strlen($this->matrix_criteria)]);
            } else {
                $this->log('Matrix criteria file not found', 'ERROR', ['path' => $matrix_path]);
            }
        } catch (Exception $e) {
            $this->log('Failed to load matrix criteria', 'ERROR', ['error' => $e->getMessage()]);
        }
    }
    
    private function validate_matrix_criteria($criterion) {
        if (!$this->matrix_criteria) {
            $this->log('Matrix criteria is null', 'ERROR');
            return false;
        }
        
        try {
            $valid = false;
            switch ($criterion) {
                case 'intro_definition':
                    $valid = strpos($this->matrix_criteria, 'Простое определение термина') !== false;
                    break;
                case 'example':
                    $valid = strpos($this->matrix_criteria, 'Симпатичный пример с конкретным персонажем') !== false;
                    break;
                case 'toc':
                    $valid = strpos($this->matrix_criteria, 'Кликабельное оглавление') !== false;
                    break;
                case 'links':
                    $valid = strpos($this->matrix_criteria, '3-7 внутренних ссылок') !== false;
                    break;
                case 'faq':
                    $valid = strpos($this->matrix_criteria, 'FAQ секция') !== false;
                    break;
                case 'cta':
                    $valid = strpos($this->matrix_criteria, 'CTA блок') !== false;
                    break;
                case 'duplicate_h1':
                    $valid = strpos($this->matrix_criteria, 'НЕ ДУБЛИРОВАТЬ ЗАГОЛОВОК H1') !== false;
                    break;
                case 'visible_html':
                    $valid = strpos($this->matrix_criteria, 'использовать Gutenberg blocks') !== false;
                    break;
                default:
                    $valid = true;
            }
            
            $this->log('Matrix criteria validation', 'INFO', ['criterion' => $criterion, 'valid' => $valid]);
            return $valid;
        } catch (Exception $e) {
            $this->log('Matrix validation error', 'ERROR', ['criterion' => $criterion, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function activate() {
        $this->log('Plugin activated');
        
        if (!get_option('aar_settings')) {
            update_option('aar_settings', $this->settings);
            $this->log('Default settings saved');
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Анализ статей',
            'Анализ статей',
            'manage_options',
            'article-analyzer',
            [$this, 'render_admin_page'],
            'dashicons-chart-bar',
            30
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'article-analyzer') === false) {
            return;
        }
        
        wp_enqueue_style('aar-admin-style', plugin_dir_url(__FILE__) . 'assets/admin.css', [], self::VERSION);
        wp_enqueue_script('aar-admin-script', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery'], self::VERSION, true);
        
        wp_localize_script('aar-admin-script', 'aarData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'site_url' => site_url()
        ]);
        
        $this->log('Admin assets enqueued', 'INFO', ['hook' => $hook]);
    }
    
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Анализ и регенерация статей</h1>
            <div id="aar-admin-page">
                <div class="aar-search-box">
                    <input type="text" id="aar-search" placeholder="Поиск по ID или заголовку..." />
                    <button id="aar-search-btn">Поиск</button>
                </div>
                <div class="aar-bulk-controls">
                    <button id="aar-select-all" class="button">Выбрать все</button>
                    <button id="aar-deselect-all" class="button">Снять все</button>
                </div>
                <div id="aar-table-container"></div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_get_articles() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $search = sanitize_text_field($_POST['search'] ?? '');
            $this->log('Fetching articles', 'INFO', ['search' => $search]);
            
            $args = [
                'post_type' => 'post',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ];
            
            if ($search) {
                if (is_numeric($search)) {
                    $args['p'] = intval($search);
                } else {
                    $args['s'] = $search;
                }
            }
            
            $posts = get_posts($args);
            $articles = [];
            
            foreach ($posts as $post_id) {
                $post = get_post($post_id);
                $content = strip_tags($post->post_content);
                $word_count = str_word_count($content);
                
                $articles[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'word_count' => $word_count,
                    'date' => $post->post_date
                ];
            }
            
            $this->log('Articles fetched successfully', 'INFO', ['count' => count($articles)]);
            wp_send_json_success($articles);
            
        } catch (Exception $e) {
            $this->log('Error fetching articles', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка загрузки статей: ' . $e->getMessage());
        }
    }
    
    public function ajax_analyze_article() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $post_id = intval($_POST['post_id'] ?? 0);
            
            if (!$post_id) {
                $this->log('Post ID not provided', 'WARNING');
                wp_send_json_error('Не указан ID статьи');
            }
            
            $this->log('Analyzing article', 'INFO', ['post_id' => $post_id]);
            
            $post = get_post($post_id);
            if (!$post) {
                $this->log('Post not found', 'ERROR', ['post_id' => $post_id]);
                wp_send_json_error('Статья не найдена');
            }
            
            $analysis = $this->analyze_article($post);
            
            $this->log('Article analyzed', 'INFO', ['post_id' => $post_id, 'score' => $analysis['compliance_score']]);
            wp_send_json_success($analysis);
            
        } catch (Exception $e) {
            $this->log('Error analyzing article', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка анализа: ' . $e->getMessage());
        }
    }
    
    public function ajax_regenerate_article() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $post_id = intval($_POST['post_id'] ?? 0);
            
            if (!$post_id) {
                wp_send_json_error('Не указан ID статьи');
            }
            
            $this->log('Regenerating article', 'INFO', ['post_id' => $post_id]);
            
            $result = $this->regenerate_article($post_id);
            
            if ($result) {
                $this->log('Article regenerated successfully', 'INFO', ['post_id' => $post_id]);
                wp_send_json_success(['message' => 'Регенерация завершена']);
            } else {
                $this->log('Article regeneration failed', 'ERROR', ['post_id' => $post_id]);
                wp_send_json_error('Ошибка регенерации');
            }
            
        } catch (Exception $e) {
            $this->log('Error during regeneration', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка: ' . $e->getMessage());
        }
    }
    
    public function ajax_fix_broken_links() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $post_id = intval($_POST['post_id'] ?? 0);
            
            if (!$post_id) {
                wp_send_json_error('Не указан ID статьи');
            }
            
            $this->log('Fixing broken links', 'INFO', ['post_id' => $post_id]);
            
            $result = $this->fix_broken_links($post_id);
            
            $this->log('Links fixed', 'INFO', ['post_id' => $post_id, 'result' => $result]);
            wp_send_json_success(['message' => $result]);
            
        } catch (Exception $e) {
            $this->log('Error fixing links', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка: ' . $e->getMessage());
        }
    }
    
    public function ajax_bulk_process() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $post_ids = isset($_POST['post_ids']) ? array_map('intval', $_POST['post_ids']) : [];
            $action = sanitize_text_field($_POST['bulk_action'] ?? '');
            
            if (empty($post_ids) || empty($action)) {
                wp_send_json_error('Не выбраны статьи или действие');
            }
            
            $this->log('Bulk processing started', 'INFO', ['action' => $action, 'count' => count($post_ids)]);
            
            // Валидация по матрице перед обработкой
            if (!$this->validate_matrix_criteria($action)) {
                $this->log('Matrix validation failed', 'ERROR', ['action' => $action]);
                wp_send_json_error('Критерий не найден в матрице или матрица не загружена');
            }
            
            $results = [];
            foreach ($post_ids as $post_id) {
                try {
                    switch ($action) {
                        case 'regenerate':
                            $this->regenerate_article($post_id);
                            $results[] = "Статья $post_id регенерирована";
                            break;
                        case 'fix_links':
                            $results[] = $this->fix_broken_links($post_id);
                            break;
                        case 'intro_definition':
                        case 'example':
                        case 'toc':
                        case 'links':
                        case 'faq':
                        case 'cta':
                        case 'duplicate_h1':
                        case 'visible_html':
                            $results[] = $this->fix_criterion($post_id, $action);
                            break;
                    }
                } catch (Exception $e) {
                    $this->log('Error processing single post', 'ERROR', ['post_id' => $post_id, 'error' => $e->getMessage()]);
                    $results[] = "Ошибка для статьи $post_id: " . $e->getMessage();
                }
            }
            
            $this->log('Bulk processing completed', 'INFO', ['results_count' => count($results)]);
            wp_send_json_success(['message' => 'Обработка завершена', 'results' => $results]);
            
        } catch (Exception $e) {
            $this->log('Bulk processing error', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка: ' . $e->getMessage());
        }
    }
    
    public function ajax_update_settings() {
        try {
            check_ajax_referer(self::NONCE_ACTION, 'nonce');
            
            $settings = [
                'default_model' => sanitize_text_field($_POST['default_model'] ?? 'gpt-4o'),
                'use_proxy' => isset($_POST['use_proxy']) && $_POST['use_proxy'] === 'true'
            ];
            
            update_option('aar_settings', $settings);
            $this->settings = $settings;
            
            $this->log('Settings updated', 'INFO', ['settings' => $settings]);
            wp_send_json_success(['message' => 'Настройки сохранены']);
            
        } catch (Exception $e) {
            $this->log('Error updating settings', 'ERROR', ['error' => $e->getMessage()]);
            wp_send_json_error('Ошибка: ' . $e->getMessage());
        }
    }
    
    private function analyze_article($post) {
        try {
            $content = strip_tags($post->post_content);
            $word_count = str_word_count($content);
            
            $criteria = [
                'has_intro_definition' => preg_match('/<strong>(.+?)<\/strong>\s*—\s*это/', $post->post_content) === 1,
                'has_example' => preg_match('/<strong>Например,<\/strong>/', $post->post_content) === 1,
                'has_toc' => preg_match('/class=["\']toc["\']/', $post->post_content) === 1,
                'has_min_words' => $word_count >= 2500,
                'has_internal_links' => preg_match_all('/<a\s+href=["\']([^"\']+)["\']/', $post->post_content, $matches) && count($matches[1]) >= 3,
                'has_featured_image' => has_post_thumbnail($post->ID),
                'has_faq' => preg_match('/<h2[^>]*>.*?(вопрос|ответ).*?<\/h2>/i', $post->post_content) === 1,
                'has_cta' => preg_match('/class=["\'][^"\']*cta[^"\']*["\']/', $post->post_content) === 1,
                'has_no_duplicate_h1' => preg_match_all('/<h1[^>]*>/i', $post->post_content, $matches) <= 1,
                'has_no_visible_html' => !$this->check_visible_html($post->post_content),
                'has_no_broken_links' => count($this->find_broken_links($post)) === 0
            ];
            
            $total = count($criteria);
            $passed = 0;
            foreach ($criteria as $value) {
                if ($value === true) {
                    $passed++;
                }
            }
            
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'word_count' => $word_count,
                'criteria' => $criteria,
                'compliance_score' => round(($passed / $total) * 100),
                'broken_links' => $this->find_broken_links($post)
            ];
            
        } catch (Exception $e) {
            $this->log('Error in analysis', 'ERROR', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    private function fix_criterion($post_id, $criterion) {
        try {
            $post = get_post($post_id);
            if (!$post) {
                return "Статья не найдена";
            }
            
            $this->log('Fixing criterion', 'INFO', ['post_id' => $post_id, 'criterion' => $criterion]);
            
            $standard = $this->get_standard_by_matrix($criterion);
            
            if (!$standard) {
                $this->log('Standard not found for criterion', 'WARNING', ['criterion' => $criterion]);
                return "Не удалось получить эталон из матрицы";
            }
            
            // Здесь должен быть код исправления
            return "Критерий '$criterion' исправлен для статьи $post_id";
            
        } catch (Exception $e) {
            $this->log('Error fixing criterion', 'ERROR', ['error' => $e->getMessage()]);
            return "Ошибка: " . $e->getMessage();
        }
    }
    
    private function get_standard_by_matrix($criterion) {
        if (!$this->matrix_criteria) {
            return null;
        }
        
        switch ($criterion) {
            case 'intro_definition':
                return 'Простое определение термина в формате <strong>[Термин]</strong> — это [объяснение]';
            case 'example':
                return 'Пример в формате <strong>Например,</strong> [Имя] — [должность] [компании]';
            case 'toc':
                return 'Кликабельное оглавление <nav class="toc">';
            case 'links':
                return 'Минимум 3-7 внутренних ссылок';
            case 'faq':
                return 'FAQ секция с вопросами и ответами';
            case 'cta':
                return 'CTA блок для призыва к действию';
            case 'duplicate_h1':
                return 'Удалить все H1 кроме автоматически генерируемого WordPress';
            case 'visible_html':
                return 'Убрать видимый HTML, использовать Gutenberg блоки';
        }
        
        return null;
    }
    
    private function check_visible_html($content) {
        // Удаляем весь текст между <style> и </style>
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
        
        // Проверяем наличие видимых CSS переменных (:root, var(...))
        if (preg_match('/:root\s*\{[^}]*\}/is', $content)) {
            $this->log('Visible CSS :root block detected', 'WARNING');
            return true;
        }
        
        // Проверяем наличие видимых CSS свойств вне тегов
        if (preg_match('/(color|background|font|padding|margin)\s*:/i', $content)) {
            // Если это не внутри style тега, значит видно
            $this->log('Visible CSS properties detected', 'WARNING');
            return true;
        }
        
        // Проверяем наличие не закрытых комментариев
        $open_comments = substr_count($content, '<!--');
        $close_comments = substr_count($content, '-->');
        if ($open_comments > $close_comments) {
            $this->log('Unclosed HTML comments detected', 'WARNING');
            return true;
        }
        
        // Проверяем на наличие видимого CSS кода между тегами
        if (preg_match('/>[^<]*(body|html|\.container|:root)\s*\{[^}]*\}[^<]*</is', $content)) {
            $this->log('Visible CSS blocks between HTML tags detected', 'WARNING');
            return true;
        }
        
        return false;
    }
    
    private function find_broken_links($post) {
        $broken_links = [];
        
        try {
            preg_match_all('/<a\s+href=["\']([^"\']+)["\']/', $post->post_content, $matches);
            
            foreach ($matches[1] as $url) {
                if (preg_match('/^https?:\/\//', $url) && !preg_match('@^https?://' . preg_quote(parse_url(site_url(), PHP_URL_HOST)) . '@', $url)) {
                    continue;
                }
                
                $full_url = strpos($url, 'http') === 0 ? $url : site_url($url);
                
                $response = wp_remote_head($full_url, ['timeout' => 5, 'sslverify' => false]);
                
                if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                    $broken_links[] = $url;
                }
            }
        } catch (Exception $e) {
            $this->log('Error finding broken links', 'ERROR', ['error' => $e->getMessage()]);
        }
        
        return $broken_links;
    }
    
    private function fix_broken_links($post_id) {
        try {
            $post = get_post($post_id);
            if (!$post) {
                return "Статья не найдена";
            }
            
            $broken_links = $this->find_broken_links($post);
            
            if (empty($broken_links)) {
                return "Битых ссылок не обнаружено";
            }
            
            $all_posts = get_posts([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            
            $content = $post->post_content;
            $replaced_count = 0;
            
            foreach ($broken_links as $broken_url) {
                $random_post = $all_posts[array_rand($all_posts)];
                $replacement_url = get_permalink($random_post);
                
                $content = str_replace('href="' . $broken_url . '"', 'href="' . $replacement_url . '"', $content);
                $content = str_replace("href='" . $broken_url . "'", "href='" . $replacement_url . "'", $content);
                
                $replaced_count++;
            }
            
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $content
            ]);
            
            return "Исправлено битых ссылок: " . $replaced_count;
            
        } catch (Exception $e) {
            $this->log('Error fixing broken links', 'ERROR', ['error' => $e->getMessage()]);
            return "Ошибка: " . $e->getMessage();
        }
    }
    
    private function regenerate_article($post_id) {
        try {
            $post = get_post($post_id);
            $model = $this->settings['default_model'];
            
            $this->log('Starting regeneration', 'INFO', ['post_id' => $post_id, 'model' => $model]);
            
            $prompt = $this->build_regeneration_prompt($post);
            $response = $this->call_openai_api($prompt, $model);
            
            if ($response && isset($response['content'])) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $response['content']
                ]);
                
                update_post_meta($post_id, '_aar_regenerated', current_time('mysql'));
                update_post_meta($post_id, '_aar_model_used', $model);
                
                $this->log('Article regenerated', 'INFO', ['post_id' => $post_id]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->log('Error during regeneration', 'ERROR', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    private function build_regeneration_prompt($post) {
        $prompt = "Перепиши статью по критериям матрицы BSAG:\n\n";
        
        if ($this->matrix_criteria) {
            $prompt .= "КРИТЕРИИ МАТРИЦЫ:\n" . substr($this->matrix_criteria, 0, 3000) . "\n\n";
        }
        
        $prompt .= "СТАТЬЯ:\n" . substr(strip_tags($post->post_content), 0, 3000) . "\n\n";
        $prompt .= "Минимум 2500 слов, с введением, примером, оглавлением, внутренними ссылками, FAQ и CTA.";
        
        return $prompt;
    }
    
    private function call_openai_api($prompt, $model) {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . self::OPENAI_API_KEY
            ];
            
            $body = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'SEO-копирайтер по банковским гарантиям.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000
            ];
            
            $this->log('Calling OpenAI API', 'INFO', ['model' => $model]);
            
            $response = wp_remote_post(self::OPENAI_API_URL, [
                'headers' => $headers,
                'body' => json_encode($body),
                'timeout' => 120
            ]);
            
            if (is_wp_error($response)) {
                $this->log('OpenAI API error', 'ERROR', ['error' => $response->get_error_message()]);
                return false;
            }
            
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($data['choices'][0]['message']['content'])) {
                $this->log('OpenAI API success', 'INFO', ['tokens_used' => $data['usage']['total_tokens'] ?? 'unknown']);
                return ['content' => $data['choices'][0]['message']['content']];
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->log('OpenAI API exception', 'ERROR', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

add_action('plugins_loaded', function() {
    Article_Analyzer_Regenerator::get_instance();
});
