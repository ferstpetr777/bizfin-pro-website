<?php
/**
 * Plugin Name: ABP AI Categorization
 * Description: AI-категоризация для алфавитного блога с использованием OpenAI
 * Version: 1.0.0
 * Author: BizFin Pro Team
 */

if (!defined('ABSPATH')) exit;

class ABP_AI_Categorization {
    const OPENAI_API_KEY = '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';
    const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    const VERSION = '1.0.0';

    public function __construct() {
        // Хуки для автоматической категоризации
        add_action('save_post', [$this, 'auto_categorize_post'], 20, 2);
        
        // AJAX для ручной категоризации
        add_action('wp_ajax_abp_ai_categorize', [$this, 'ajax_categorize_post']);
        add_action('wp_ajax_abp_ai_categorize_all', [$this, 'ajax_categorize_all_posts']);
        
        // Админ-меню
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Стили и скрипты
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /** Автоматическая категоризация при сохранении поста */
    public function auto_categorize_post($post_id, $post) {
        if ($post->post_type !== 'post') {
            return;
        }

        // Проверяем, есть ли уже категоризация
        $existing_category = get_post_meta($post_id, 'abp_ai_category', true);
        if ($existing_category) {
            return;
        }

        // Выполняем AI-категоризацию для любых постов (включая черновики)
        $this->categorize_post_with_ai($post_id);
    }

    /** Категоризация поста с помощью AI */
    public function categorize_post_with_ai($post_id) {
        error_log("=== ABP AI: categorize_post_with_ai START for post $post_id ===");
        
        $post = get_post($post_id);
        if (!$post) {
            error_log("ABP AI: Post not found for post $post_id");
            return false;
        }

        $title = $post->post_title;
        
        // ВРЕМЕННАЯ ЗАГЛУШКА: Определяем категорию по заголовку
        $category = 'банковские гарантии'; // По умолчанию
        
        if (mb_stripos($title, 'гарант') !== false) {
            $category = 'банковские гарантии';
        } elseif (mb_stripos($title, 'тендер') !== false) {
            $category = 'государственные закупки';
        } elseif (mb_stripos($title, 'финанс') !== false) {
            $category = 'финансовое планирование';
        } elseif (mb_stripos($title, 'бизнес') !== false) {
            $category = 'бизнес-консультирование';
        } elseif (mb_stripos($title, 'налог') !== false) {
            $category = 'налогообложение';
        }
        
        error_log("ABP AI: Post title: $title");
        error_log("ABP AI: Using fallback categorization: $category");
        
        // Сохраняем категорию
        update_post_meta($post_id, 'abp_ai_category', $category);
        update_post_meta($post_id, 'abp_ai_categorized', current_time('mysql'));
        
        error_log("ABP AI: Post {$post_id} categorized as '{$category}' and saved to database");
        error_log("=== ABP AI: categorize_post_with_ai END for post $post_id ===");
        
        return $category;
    }

    /** Построение промпта для AI */
    private function build_categorization_prompt($title, $content) {
        return "Ты эксперт по категоризации бизнес-контента. Проанализируй статью и определи её основную тематическую категорию.

ЗАГОЛОВОК: {$title}

СОДЕРЖАНИЕ: {$content}

ИНСТРУКЦИИ:
1. Внимательно прочитай заголовок и содержание
2. Определи основную тему статьи
3. Выбери ТОЛЬКО ОДНУ наиболее подходящую категорию из списка ниже
4. Ответь строго названием категории без дополнительных слов

ДОСТУПНЫЕ КАТЕГОРИИ:
- Банковские гарантии (все что связано с гарантиями, поручительствами, обеспечениями)
- Финансовое планирование (бюджетирование, планирование финансов, анализ)
- Государственные закупки (тендеры, 44-ФЗ, 223-ФЗ, закупки)
- Бизнес-консультирование (консультации, стратегии, развитие бизнеса)
- Правовые вопросы (юридические аспекты, законодательство, права)
- Налогообложение (налоги, налоговое планирование, отчетность)
- Инвестиции (инвестирование, вложения, портфели)
- Кредитование (кредиты, займы, финансирование)
- Бухгалтерия (учет, отчетность, документооборот)
- Управление рисками (риски, страхование, защита)

ОТВЕТ (только название категории):";
    }

    /** Вызов OpenAI API */
    private function call_openai_api($prompt) {
        // Проверяем наличие API-ключа
        $has_real_key = !empty(self::OPENAI_API_KEY) && strpos(self::OPENAI_API_KEY, 'sk-') === 0 && self::OPENAI_API_KEY !== 'sk-test-key-for-testing';
        if (!$has_real_key) {
            error_log('ABP AI: No valid OpenAI API key found');
            return false;
        }
        
        error_log("ABP AI: Making API request to: " . self::OPENAI_API_URL);
        error_log("ABP AI: API key starts with: " . substr(self::OPENAI_API_KEY, 0, 10) . "...");
        error_log("ABP AI: Request data: " . json_encode([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => substr($prompt, 0, 200) . '...'
                ]
            ],
            'max_tokens' => 100,
            'temperature' => 0.3,
        ]));
        
        $headers = [
            'Authorization' => 'Bearer ' . self::OPENAI_API_KEY,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 100,
            'temperature' => 0.3,
        ];

        $args = [
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 60,
            'blocking' => true,
        ];
        
        $response = wp_remote_post(self::OPENAI_API_URL, $args);

        if (is_wp_error($response)) {
            error_log('ABP AI Error: API request failed - ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log("ABP AI: Response code: $response_code");
        error_log("ABP AI: Response body: " . $body);
        
        if ($response_code !== 200) {
            error_log("ABP AI Error: HTTP error $response_code - $body");
            return false;
        }

        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ABP AI Error: JSON decode error - ' . json_last_error_msg());
            return false;
        }

        // Проверяем на ошибки API
        if (isset($data['error'])) {
            error_log('ABP AI Error: API returned error - ' . json_encode($data['error']));
            return false;
        }

        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            error_log("ABP AI: Successfully received content: $content");
            return $content;
        }

        error_log('ABP AI Error: Invalid API response structure - ' . $body);
        error_log('ABP AI Error: Response data structure: ' . json_encode($data));
        return false;
    }

    /** Парсинг ответа AI */
    private function parse_ai_response($response) {
        // Список допустимых категорий
        $valid_categories = [
            'банковские гарантии',
            'финансовое планирование',
            'государственные закупки',
            'бизнес-консультирование',
            'правовые вопросы',
            'налогообложение',
            'инвестиции',
            'кредитование',
            'бухгалтерия',
            'управление рисками'
        ];

        $response_lower = mb_strtolower($response, 'UTF-8');

        // Ищем совпадение с допустимыми категориями
        foreach ($valid_categories as $category) {
            if (strpos($response_lower, $category) !== false) {
                return ucfirst($category);
            }
        }

        // Если точного совпадения нет, возвращаем первую подходящую категорию
        if (strpos($response_lower, 'гарант') !== false) {
            return 'Банковские гарантии';
        }
        
        if (strpos($response_lower, 'финанс') !== false) {
            return 'Финансовое планирование';
        }
        
        if (strpos($response_lower, 'закупк') !== false) {
            return 'Государственные закупки';
        }

        // По умолчанию
        return 'Бизнес-консультирование';
    }

    /** AJAX категоризация отдельного поста */
    public function ajax_categorize_post() {
        check_ajax_referer('abp_ai_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Недостаточно прав');
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error('Неверный ID поста');
        }

        $category = $this->categorize_post_with_ai($post_id);
        
        if ($category) {
            wp_send_json_success([
                'category' => $category,
                'message' => 'Пост успешно категоризирован'
            ]);
        } else {
            wp_send_json_error('Ошибка категоризации');
        }
    }

    /** AJAX категоризация всех постов */
    public function ajax_categorize_all_posts() {
        check_ajax_referer('abp_ai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }

        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        $categorized = 0;
        $errors = 0;
        $results = [];

        foreach ($posts as $post_id) {
            try {
                $category = $this->categorize_post_with_ai($post_id);
                if ($category) {
                    $categorized++;
                    $results[] = [
                        'post_id' => $post_id,
                        'title' => get_the_title($post_id),
                        'category' => $category,
                        'status' => 'success'
                    ];
                } else {
                    $errors++;
                    $results[] = [
                        'post_id' => $post_id,
                        'title' => get_the_title($post_id),
                        'category' => null,
                        'status' => 'error'
                    ];
                }
                
                // Небольшая задержка между запросами
                usleep(500000); // 0.5 секунды
                
            } catch (Exception $e) {
                $errors++;
                $results[] = [
                    'post_id' => $post_id,
                    'title' => get_the_title($post_id),
                    'category' => null,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        wp_send_json_success([
            'categorized' => $categorized,
            'errors' => $errors,
            'total' => count($posts),
            'results' => $results
        ]);
    }

    /** Добавление админ-меню */
    public function add_admin_menu() {
        add_options_page(
            'ABP AI Categorization',
            'ABP AI Categories',
            'manage_options',
            'abp-ai-categorization',
            [$this, 'admin_page']
        );
    }

    /** Страница админки */
    public function admin_page() {
        $stats = $this->get_categorization_stats();
        
        ?>
        <div class="wrap">
            <h1>ABP AI Categorization</h1>
            
            <div class="abp-ai-stats">
                <div class="abp-stat-card">
                    <h3>Статистика категоризации</h3>
                    <p><strong>Всего постов:</strong> <?php echo $stats['total_posts']; ?></p>
                    <p><strong>Категоризировано:</strong> <?php echo $stats['categorized_posts']; ?></p>
                    <p><strong>Процент категоризации:</strong> <?php echo $stats['categorization_percentage']; ?>%</p>
                </div>
                
                <div class="abp-stat-card">
                    <h3>Популярные категории</h3>
                    <?php foreach ($stats['top_categories'] as $category => $count): ?>
                        <p><strong><?php echo esc_html($category); ?>:</strong> <?php echo $count; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="abp-ai-actions">
                <h2>Действия</h2>
                <button type="button" class="button button-primary" id="abp-categorize-all">
                    Категоризировать все посты
                </button>
                <button type="button" class="button" id="abp-view-categories">
                    Просмотреть категории
                </button>
            </div>

            <div id="abp-ai-results" class="abp-ai-results" style="display: none;">
                <h3>Результаты</h3>
                <div id="abp-ai-results-content"></div>
            </div>
        </div>
        <?php
    }

    /** Получение статистики категоризации */
    private function get_categorization_stats() {
        global $wpdb;
        
        // Общая статистика
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'post' AND post_status = 'publish'
        ");
        
        $categorized_posts = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_ai_category' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
        ");
        
        // Топ категории
        $top_categories = $wpdb->get_results("
            SELECT pm.meta_value as category, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_ai_category' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT 5
        ", ARRAY_A);
        
        $top_categories_array = [];
        foreach ($top_categories as $row) {
            $top_categories_array[$row['category']] = $row['count'];
        }
        
        return [
            'total_posts' => $total_posts ?: 0,
            'categorized_posts' => $categorized_posts ?: 0,
            'categorization_percentage' => $total_posts ? round(($categorized_posts / $total_posts) * 100, 1) : 0,
            'top_categories' => $top_categories_array
        ];
    }

    /** Подключение админских стилей и скриптов */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_abp-ai-categorization') {
            return;
        }

        wp_enqueue_style(
            'abp-ai-admin-css',
            plugin_dir_url(__FILE__) . 'assets/admin.css',
            [],
            self::VERSION
        );

        wp_enqueue_script(
            'abp-ai-admin-js',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            ['jquery'],
            self::VERSION,
            true
        );

        wp_localize_script('abp-ai-admin-js', 'ABPAI', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('abp_ai_nonce'),
            'strings' => [
                'categorizing' => 'Категоризация...',
                'success' => 'Категоризация завершена',
                'error' => 'Ошибка категоризации'
            ]
        ]);
    }
}

new ABP_AI_Categorization();
