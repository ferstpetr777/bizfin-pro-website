<?php
/**
 * Plugin Name: Yoast Alphabet Integration
 * Description: Интеграция Yoast SEO с алфавитным блогом для автоматической SEO-оптимизации
 * Version: 1.0.0
 * Author: BizFin Pro Team
 * Text Domain: yoast-alphabet-integration
 */

if (!defined('ABSPATH')) exit;

class YoastAlphabetIntegration {
    const VERSION = '1.0.0';
    const NONCE = 'yai_ajax_nonce';

    public function __construct() {
        // Проверяем наличие Yoast SEO
        add_action('admin_init', [$this, 'check_yoast_dependency']);
        
        // Хуки для автоматической SEO-оптимизации
        add_action('save_post', [$this, 'auto_optimize_post_seo'], 20, 2);
        add_action('abp_letter_processed', [$this, 'optimize_letter_archive_seo'], 10, 2);
        
        // AJAX для ручной оптимизации
        add_action('wp_ajax_yai_optimize_post', [$this, 'ajax_optimize_post']);
        add_action('wp_ajax_yai_optimize_all_posts', [$this, 'ajax_optimize_all_posts']);
        
        // Админ-меню
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Стили и скрипты
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Интеграция с Alphabet Blog Panel
        add_action('init', [$this, 'integrate_with_alphabet_blog']);
        
        // SEO для буквенных архивов
        add_filter('wpseo_title', [$this, 'optimize_letter_archive_title']);
        add_filter('wpseo_metadesc', [$this, 'optimize_letter_archive_description']);
        add_filter('wpseo_canonical', [$this, 'optimize_letter_archive_canonical']);
    }

    /** Проверка зависимости от Yoast SEO */
    public function check_yoast_dependency() {
        if (!defined('WPSEO_VERSION')) {
            add_action('admin_notices', [$this, 'yoast_missing_notice']);
        }
    }

    public function yoast_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Yoast Alphabet Integration:</strong> Требуется плагин Yoast SEO для работы. ';
        echo '<a href="' . admin_url('plugin-install.php?s=yoast+seo&tab=search&type=term') . '">Установить Yoast SEO</a>';
        echo '</p></div>';
    }

    /** Интеграция с Alphabet Blog Panel */
    public function integrate_with_alphabet_blog() {
        // Добавляем хук для обработки буквенных архивов
        if (class_exists('ABP_Plugin')) {
            add_action('abp_letter_archive_generated', [$this, 'optimize_letter_archive'], 10, 2);
        }
    }

    /** Автоматическая SEO-оптимизация при сохранении поста */
    public function auto_optimize_post_seo($post_id, $post) {
        // Проверяем, что это пост и он опубликован
        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return;
        }

        // Проверяем наличие Yoast SEO
        if (!defined('WPSEO_VERSION')) {
            return;
        }

        // Получаем ключевое слово из заголовка
        $keyword = $this->extract_keyword_from_title($post->post_title);
        
        if (!$keyword) {
            return;
        }

        // Оптимизируем пост
        $this->optimize_post_for_yoast($post_id, $keyword);
    }

    /** Извлечение ключевого слова из заголовка */
    private function extract_keyword_from_title($title) {
        // Убираем HTML теги
        $title = wp_strip_all_tags($title);
        
        // Убираем знаки препинания и лишние символы
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);
        
        // Разбиваем на слова и берем первые 2-3 слова как ключевое слово
        $words = preg_split('/\s+/', trim($title));
        
        if (count($words) >= 2) {
            return implode(' ', array_slice($words, 0, 2));
        }
        
        return $words[0] ?? '';
    }

    /** Оптимизация поста для Yoast SEO */
    public function optimize_post_for_yoast($post_id, $keyword) {
        error_log("YoastAlphabetIntegration: optimize_post_for_yoast called for post $post_id with keyword: $keyword");
        if (!$keyword || !defined('WPSEO_VERSION')) {
            error_log("YoastAlphabetIntegration: optimize_post_for_yoast failed - no keyword or WPSEO not defined");
            return false;
        }

        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        // Проверяем текущие Yoast мета-данные
        $current_focus_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        $current_meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        $current_title = get_post_meta($post_id, '_yoast_wpseo_title', true);

        $optimizations = [];

        // 1. Фокусное ключевое слово
        if (!$current_focus_kw) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
            $optimizations[] = 'focus_keyword';
        }

        // 2. SEO заголовок
        if (!$current_title) {
            $seo_title = $this->generate_seo_title($post->post_title, $keyword);
            update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
            $optimizations[] = 'seo_title';
        }

        // 3. Мета-описание - всегда перегенерируем для соответствия требованиям
        error_log("YoastAlphabetIntegration: Current meta description: " . ($current_meta_desc ?: 'empty'));
        
        // Проверяем, соответствует ли текущее описание требованиям
        $needs_regeneration = !$current_meta_desc || 
                             !$this->meta_description_starts_with_keyword($current_meta_desc, $keyword) ||
                             !$this->meta_description_matches_title($current_meta_desc, $post->post_title);
        
        if ($needs_regeneration) {
            error_log("YoastAlphabetIntegration: Generating new meta description (current doesn't meet requirements)");
            $meta_desc = $this->generate_meta_description($post, $keyword);
            error_log("YoastAlphabetIntegration: Generated meta description: " . ($meta_desc ?: 'NULL'));
            
            if ($meta_desc) {
                // Используем прямой SQL запрос для надежного сохранения
                global $wpdb;
                
                // Удаляем существующую запись
                $delete_result = $wpdb->delete(
                    $wpdb->postmeta,
                    [
                        'post_id' => $post_id,
                        'meta_key' => '_yoast_wpseo_metadesc'
                    ],
                    ['%d', '%s']
                );
                error_log("YoastAlphabetIntegration: Delete result: " . ($delete_result !== false ? 'SUCCESS' : 'FAILED'));
                
                // Вставляем новую запись
                $insert_result = $wpdb->insert(
                    $wpdb->postmeta,
                    [
                        'post_id' => $post_id,
                        'meta_key' => '_yoast_wpseo_metadesc',
                        'meta_value' => $meta_desc
                    ],
                    ['%d', '%s', '%s']
                );
                error_log("YoastAlphabetIntegration: Insert result: " . ($insert_result !== false ? 'SUCCESS' : 'FAILED'));
                
                if ($insert_result === false) {
                    error_log("YoastAlphabetIntegration: SQL Error: " . $wpdb->last_error);
                }
                
                // Очищаем кэш
                wp_cache_delete($post_id, 'posts');
                wp_cache_delete($post_id, 'post_meta');
                
                $optimizations[] = 'meta_description';
                error_log("YoastAlphabetIntegration: Meta description updated: $meta_desc");
                
                // Проверяем, что действительно сохранилось
                $saved_meta = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
                error_log("YoastAlphabetIntegration: Saved meta description: " . ($saved_meta ?: 'NOT SAVED'));
            } else {
                error_log("YoastAlphabetIntegration: Failed to generate meta description");
            }
        } else {
            error_log("YoastAlphabetIntegration: Meta description meets requirements, keeping current");
        }

        // 4. Canonical URL
        update_post_meta($post_id, '_yoast_wpseo_canonical', get_permalink($post_id));

        // 5. Robots мета
        update_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', '0');
        update_post_meta($post_id, '_yoast_wpseo_meta-robots-nofollow', '0');

        // Логируем оптимизации
        if (!empty($optimizations)) {
            error_log("YAI: Post {$post_id} optimized with: " . implode(', ', $optimizations));
        }

        return !empty($optimizations);
    }

    /** Генерация SEO заголовка с помощью AI */
    private function generate_seo_title($original_title, $keyword) {
        // Если заголовок уже содержит ключевое слово, оставляем как есть
        if (stripos($original_title, $keyword) !== false) {
            return $original_title . ' | BizFin Pro';
        }

        // Используем AI для генерации SEO-оптимизированного заголовка
        $ai_title = $this->generate_ai_seo_title($original_title, $keyword);
        
        if ($ai_title) {
            return $ai_title;
        }

        // Fallback заголовок
        return $keyword . ': Полное руководство | BizFin Pro';
    }

    /** Генерация мета-описания с помощью AI */
    private function generate_meta_description($post, $keyword) {
        // Сначала пробуем AI генерацию
        $ai_desc = $this->generate_ai_meta_description($post, $keyword);
        
        if ($ai_desc && $this->validate_meta_description($ai_desc, $keyword, $post->post_title)) {
            error_log("YoastAlphabetIntegration: AI generated meta description: $ai_desc");
            return $ai_desc;
        }

        error_log("YoastAlphabetIntegration: AI generation failed, using smart fallback");
        
        // Простое fallback мета-описание - всегда работает
        $meta_desc = $keyword . ': полное руководство по банковским гарантиям, финансам и бизнесу в 2024 году.';
        
        // Санитизируем строку от некорректных символов
        $meta_desc = strip_tags($meta_desc);
        $meta_desc = sanitize_text_field($meta_desc);
        
        // Ограничиваем длину до 160 символов
        if (strlen($meta_desc) > 160) {
            $meta_desc = substr($meta_desc, 0, 157) . '...';
        }

        error_log("YoastAlphabetIntegration: Smart fallback meta description: $meta_desc");
        
        // Проверяем, что fallback проходит валидацию
        if ($this->validate_meta_description($meta_desc, $keyword, $post->post_title)) {
            error_log("YoastAlphabetIntegration: Fallback validation passed");
            return $meta_desc;
        } else {
            error_log("YoastAlphabetIntegration: Fallback validation failed, using simple fallback");
            // Простой fallback, который всегда работает
            $simple_desc = $keyword . ': полное руководство по банковским гарантиям и финансам в 2024 году.';
            
            // Санитизируем строку от некорректных символов
            $simple_desc = strip_tags($simple_desc);
            $simple_desc = sanitize_text_field($simple_desc);
            
            if (strlen($simple_desc) > 160) {
                $simple_desc = substr($simple_desc, 0, 157) . '...';
            }
            return $simple_desc;
        }
    }

    /** Проверка соответствия Meta Description требованиям */
    private function validate_meta_description($meta_desc, $keyword, $title) {
        if (!$meta_desc || empty(trim($meta_desc))) {
            error_log("YoastAlphabetIntegration: Meta description is empty");
            return false;
        }
        
        // Проверяем длину
        if (strlen($meta_desc) > 160) {
            error_log("YoastAlphabetIntegration: Meta description too long: " . strlen($meta_desc) . " characters");
            return false;
        }
        
        // Проверяем, начинается ли с ключевого слова
        if (!$this->meta_description_starts_with_keyword($meta_desc, $keyword)) {
            error_log("YoastAlphabetIntegration: Meta description doesn't start with keyword: '$meta_desc' vs '$keyword'");
            return false;
        }
        
        // Проверяем соответствие заголовку
        if (!$this->meta_description_matches_title($meta_desc, $title)) {
            error_log("YoastAlphabetIntegration: Meta description doesn't match title");
            return false;
        }
        
        error_log("YoastAlphabetIntegration: Meta description validation passed");
        return true;
    }

    /** Проверка, начинается ли Meta Description с ключевого слова */
    private function meta_description_starts_with_keyword($meta_desc, $keyword) {
        $meta_desc_lower = mb_strtolower(trim($meta_desc), 'UTF-8');
        $keyword_lower = mb_strtolower(trim($keyword), 'UTF-8');
        
        return mb_strpos($meta_desc_lower, $keyword_lower, 0, 'UTF-8') === 0;
    }

    /** Проверка соответствия Meta Description заголовку */
    private function meta_description_matches_title($meta_desc, $title) {
        // Извлекаем ключевые слова из заголовка
        $title_words = preg_split('/\s+/', mb_strtolower($title, 'UTF-8'));
        $meta_words = preg_split('/\s+/', mb_strtolower($meta_desc, 'UTF-8'));
        
        // Проверяем, есть ли общие значимые слова (длиннее 3 символов)
        $title_significant = array_filter($title_words, function($word) {
            return mb_strlen($word, 'UTF-8') > 3;
        });
        
        $meta_significant = array_filter($meta_words, function($word) {
            return mb_strlen($word, 'UTF-8') > 3;
        });
        
        $common_words = array_intersect($title_significant, $meta_significant);
        
        // Должно быть минимум 2 общих значимых слова
        return count($common_words) >= 2;
    }

    /** Генерация SEO заголовка с помощью GPT-4o Mini */
    private function generate_ai_seo_title($original_title, $keyword) {
        $prompt = "Создай SEO-оптимизированный заголовок для статьи о банковских гарантиях и финансах. 

Исходный заголовок: {$original_title}
Ключевое слово: {$keyword}

Требования:
- Заголовок должен начинаться с ключевого слова
- Длина не более 60 символов
- Привлекательный и информативный
- В стиле делового контента
- Добавь в конец ' | BizFin Pro'

Ответь только заголовком, без дополнительных объяснений.";

        return $this->call_openai_api($prompt);
    }

    /** Генерация мета-описания с помощью GPT-4o Mini */
    private function generate_ai_meta_description($post, $keyword) {
        error_log("YoastAlphabetIntegration: generate_ai_meta_description called for post {$post->ID} with keyword: $keyword");
        $content = wp_strip_all_tags($post->post_content);
        if (strlen($content) > 1000) {
            $content = substr($content, 0, 1000) . '...';
        }

        $prompt = "Создай SEO-оптимизированное мета-описание для статьи о банковских гарантиях и финансах.

Заголовок: {$post->post_title}
Ключевое слово: {$keyword}

КРИТИЧЕСКИ ВАЖНЫЕ ТРЕБОВАНИЯ:
- Мета-описание ДОЛЖНО НАЧИНАТЬСЯ с ключевого слова '{$keyword}'
- Длина НЕ БОЛЕЕ 160 символов
- Должно соответствовать теме заголовка статьи
- Привлекательное описание с призывом к действию
- В стиле делового контента о банковских гарантиях и финансах

Пример структуры: '{$keyword}: [краткое описание] [призыв к действию]'

Ответь только мета-описанием, без дополнительных объяснений.";

        error_log("YoastAlphabetIntegration: Calling OpenAI API for meta description");
        $result = $this->call_openai_api($prompt);
        error_log("YoastAlphabetIntegration: OpenAI API result for meta description: " . ($result ?: 'failed'));
        return $result;
    }

    /** Вызов OpenAI API с GPT-4o Mini */
    private function call_openai_api($prompt) {
        error_log("YoastAlphabetIntegration: call_openai_api called with prompt length: " . strlen($prompt));
        $api_key = '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';
        $api_url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ];

        // Очищаем промпт от некорректных UTF-8 символов
        $clean_prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');
        $clean_prompt = htmlspecialchars($clean_prompt, ENT_QUOTES, 'UTF-8');
        
        $data = [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $clean_prompt
                ]
            ],
            'max_tokens' => 200,
            'temperature' => 0.7,
        ];

        error_log("YoastAlphabetIntegration: Making API request to: $api_url");
        error_log("YoastAlphabetIntegration: Request headers: " . json_encode($headers));
        error_log("YoastAlphabetIntegration: Request data array: " . print_r($data, true));
        
        $json_body = json_encode($data, JSON_UNESCAPED_UNICODE);
        error_log("YoastAlphabetIntegration: JSON encoded body: " . $json_body);
        error_log("YoastAlphabetIntegration: JSON encode error: " . json_last_error_msg());
        
        $response = wp_remote_post($api_url, [
            'headers' => $headers,
            'body' => $json_body,
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ]);

        if (is_wp_error($response)) {
            error_log('YAI OpenAI Error: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log("YAI OpenAI Response code: $response_code");
        error_log("YAI OpenAI Response body: " . $body);
        
        $data = json_decode($body, true);

        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            error_log("YAI OpenAI Success: " . $content);
            return $content;
        }

        // Дополнительная проверка на ошибки API
        if (isset($data['error'])) {
            error_log('YAI OpenAI API Error: ' . $data['error']['message']);
            return false;
        }

        error_log('YAI OpenAI Invalid Response: ' . $body);
        error_log('YAI OpenAI Response Data: ' . print_r($data, true));
        return false;
    }

    /** Оптимизация буквенного архива */
    public function optimize_letter_archive($letter, $post_count) {
        // Сохраняем информацию о буквенном архиве для SEO
        update_option("yai_letter_archive_{$letter}", [
            'letter' => $letter,
            'post_count' => $post_count,
            'last_updated' => current_time('mysql'),
            'seo_optimized' => true
        ]);
    }

    /** Оптимизация заголовка буквенного архива */
    public function optimize_letter_archive_title($title) {
        $letter = get_query_var('abp_letter');
        if ($letter) {
            $letter_upper = mb_strtoupper($letter, 'UTF-8');
            return "Блог — буква «{$letter_upper}» | BizFin Pro";
        }
        return $title;
    }

    /** Оптимизация описания буквенного архива */
    public function optimize_letter_archive_description($description) {
        $letter = get_query_var('abp_letter');
        if ($letter) {
            $letter_upper = mb_strtoupper($letter, 'UTF-8');
            return "Статьи блога на букву {$letter_upper}. Алфавитный рубрикатор: удобный поиск публикаций о банковских гарантиях, финансах и бизнесе.";
        }
        return $description;
    }

    /** Оптимизация canonical URL буквенного архива */
    public function optimize_letter_archive_canonical($canonical) {
        $letter = get_query_var('abp_letter');
        if ($letter) {
            return home_url('/blog/' . rawurlencode($letter) . '/');
        }
        return $canonical;
    }

    /** AJAX оптимизация отдельного поста */
    public function ajax_optimize_post() {
        check_ajax_referer(self::NONCE, 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Недостаточно прав');
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        $keyword = sanitize_text_field($_POST['keyword'] ?? '');

        if (!$post_id || !$keyword) {
            wp_send_json_error('Неверные параметры');
        }

        $result = $this->optimize_post_for_yoast($post_id, $keyword);
        
        if ($result) {
            wp_send_json_success('Пост успешно оптимизирован');
        } else {
            wp_send_json_error('Ошибка оптимизации');
        }
    }

    /** AJAX оптимизация всех постов */
    public function ajax_optimize_all_posts() {
        check_ajax_referer(self::NONCE, 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }

        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        $optimized = 0;
        $errors = 0;

        foreach ($posts as $post_id) {
            $keyword = $this->extract_keyword_from_title(get_the_title($post_id));
            if ($keyword && $this->optimize_post_for_yoast($post_id, $keyword)) {
                $optimized++;
            } else {
                $errors++;
            }
        }

        wp_send_json_success([
            'optimized' => $optimized,
            'errors' => $errors,
            'total' => count($posts)
        ]);
    }

    /** Добавление админ-меню */
    public function add_admin_menu() {
        add_options_page(
            'Yoast Alphabet Integration',
            'Yoast Alphabet SEO',
            'manage_options',
            'yoast-alphabet-integration',
            [$this, 'admin_page']
        );
    }

    /** Страница админки */
    public function admin_page() {
        // Получаем статистику
        $stats = $this->get_optimization_stats();
        
        ?>
        <div class="wrap">
            <h1>Yoast Alphabet Integration</h1>
            
            <div class="yai-stats-grid">
                <div class="yai-stat-card">
                    <h3>Общая статистика</h3>
                    <p><strong>Всего постов:</strong> <?php echo $stats['total_posts']; ?></p>
                    <p><strong>Оптимизировано:</strong> <?php echo $stats['optimized_posts']; ?></p>
                    <p><strong>Процент оптимизации:</strong> <?php echo $stats['optimization_percentage']; ?>%</p>
                </div>
                
                <div class="yai-stat-card">
                    <h3>Буквенные архивы</h3>
                    <p><strong>Активных букв:</strong> <?php echo $stats['active_letters']; ?></p>
                    <p><strong>SEO оптимизировано:</strong> <?php echo $stats['optimized_letters']; ?></p>
                </div>
            </div>

            <div class="yai-actions">
                <h2>Действия</h2>
                <button type="button" class="button button-primary" id="yai-optimize-all">
                    Оптимизировать все посты
                </button>
                <button type="button" class="button" id="yai-check-optimization">
                    Проверить оптимизацию
                </button>
            </div>

            <div id="yai-results" class="yai-results" style="display: none;">
                <h3>Результаты</h3>
                <div id="yai-results-content"></div>
            </div>
        </div>
        <?php
    }

    /** Получение статистики оптимизации */
    private function get_optimization_stats() {
        global $wpdb;
        
        // Общая статистика постов
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'post' AND post_status = 'publish'
        ");
        
        $optimized_posts = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '_yoast_wpseo_focuskw' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
            AND pm.meta_value != ''
        ");
        
        // Статистика буквенных архивов
        $active_letters = $wpdb->get_var("
            SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_first_letter' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
        ");
        
        $optimized_letters = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->options}
            WHERE option_name LIKE 'yai_letter_archive_%'
            AND option_value LIKE '%seo_optimized%'
        ");
        
        return [
            'total_posts' => $total_posts ?: 0,
            'optimized_posts' => $optimized_posts ?: 0,
            'optimization_percentage' => $total_posts ? round(($optimized_posts / $total_posts) * 100, 1) : 0,
            'active_letters' => $active_letters ?: 0,
            'optimized_letters' => $optimized_letters ?: 0
        ];
    }

    /** Подключение админских стилей и скриптов */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_yoast-alphabet-integration') {
            return;
        }

        wp_enqueue_style(
            'yai-admin-css',
            plugin_dir_url(__FILE__) . 'assets/admin.css',
            [],
            self::VERSION
        );

        wp_enqueue_script(
            'yai-admin-js',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            ['jquery'],
            self::VERSION,
            true
        );

        wp_localize_script('yai-admin-js', 'YAI', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE),
            'strings' => [
                'optimizing' => 'Оптимизация...',
                'success' => 'Оптимизация завершена',
                'error' => 'Ошибка оптимизации'
            ]
        ]);
    }
}

new YoastAlphabetIntegration();
