<?php
/**
 * Plugin Name: ABP Image Generator
 * Description: Автоматическая генерация изображений для статей блога с использованием OpenAI DALL-E
 * Version: 1.0.0
 * Author: BizFin Pro Team
 * Text Domain: abp-image-generator
 */

if (!defined('ABSPATH')) exit;

class ABP_Image_Generator {
    
    const VERSION = '1.0.0';
    const OPENAI_API_URL = 'https://api.openai.com/v1/images/generations';
    const OPENAI_API_KEY = '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';
    const NONCE_ACTION = 'abp_image_generator';
    
    // Настройки по умолчанию
    private $default_settings = [
        'auto_generate' => true,
        'model' => 'dall-e-2',
        'size' => '1024x1024',
        'quality' => 'standard',
        'style' => 'natural',
        'max_attempts' => 3,
        'retry_delay' => 5,
        'log_level' => 'info',
        'enable_seo_optimization' => true,
        'auto_alt_text' => true,
        'auto_description' => true
    ];
    
    private $settings;
    private $log_file;
    
    public function __construct() {
        $this->init();
    }
    
    private function init() {
        // Загружаем настройки
        $this->settings = get_option('abp_image_generator_settings', $this->default_settings);
        
        // Инициализируем логирование
        $this->log_file = WP_CONTENT_DIR . '/uploads/abp-image-generator/logs/';
        $this->ensure_log_directory();
        
        // Хуки WordPress
        add_action('save_post', [$this, 'check_post_for_image'], 20, 2);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_abp_generate_image', [$this, 'ajax_generate_image']);
        add_action('wp_ajax_abp_bulk_generate_images', [$this, 'ajax_bulk_generate_images']);
        add_action('wp_ajax_abp_generate_images_by_letter', [$this, 'ajax_generate_images_by_letter']);
        add_action('wp_ajax_abp_get_generation_stats', [$this, 'ajax_get_stats']);
        add_action('wp_ajax_abp_get_posts_without_images', [$this, 'ajax_get_posts_without_images']);
        add_action('wp_ajax_abp_check_post_seo', [$this, 'ajax_check_post_seo']);
        add_action('wp_ajax_abp_test_openai_api', [$this, 'ajax_test_openai_api']);
        add_action('wp_ajax_abp_get_logs', [$this, 'ajax_get_logs']);
        add_action('wp_ajax_abp_check_blog_integration', [$this, 'ajax_check_blog_integration']);
        add_action('wp_ajax_abp_repair_broken_attachments', [$this, 'ajax_repair_broken_attachments']);
        add_action('wp_ajax_abp_attach_existing_image', [$this, 'ajax_attach_existing_image']);
        add_action('wp_ajax_abp_attach_image_by_url', [$this, 'ajax_attach_image_by_url']);
        add_action('wp_ajax_abp_regenerate_thumbnails', [$this, 'ajax_regenerate_thumbnails']);
        add_action('wp_ajax_abp_debug_post_thumbnail', [$this, 'ajax_debug_post_thumbnail']);
        add_action('wp_ajax_abp_regenerate_single_image', [$this, 'ajax_regenerate_single_image']);
        
        // Фронтенд: скрыть верхний вывод featured image темой, если вставлен контент-блок после TOC
        add_filter('body_class', [$this, 'filter_body_class_flag_inline_image']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_styles']);
        
        // Активация и деактивация
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Интеграция с системой блога
        add_action('abp_post_processed', [$this, 'process_post_for_image'], 10, 2);
        
        // Логирование
        $this->log('info', 'ABP Image Generator initialized');
    }
    
    /**
     * Активация плагина
     */
    public function activate() {
        // Создаем директории
        $this->ensure_directories();
        
        // Создаем таблицу для логов
        $this->create_logs_table();
        
        // Устанавливаем настройки по умолчанию
        if (!get_option('abp_image_generator_settings')) {
            update_option('abp_image_generator_settings', $this->default_settings);
        }
        
        $this->log('info', 'Plugin activated');
    }
    
    /**
     * Деактивация плагина
     */
    public function deactivate() {
        $this->log('info', 'Plugin deactivated');
    }
    
    /**
     * Проверка поста на наличие изображения при сохранении
     */
    public function check_post_for_image($post_id, $post) {
        // Проверяем только опубликованные посты типа 'post'
        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return;
        }
        
        // Проверяем, есть ли уже изображение
        if (has_post_thumbnail($post_id)) {
            $this->log('debug', "Post {$post_id} already has featured image");
            // Гарантируем наличие видимого блока изображения после оглавления
            $this->ensure_content_image_after_toc($post_id);
            return;
        }
        
        // Проверяем настройки авто-генерации
        if (!$this->settings['auto_generate']) {
            $this->log('debug', "Auto-generation disabled for post {$post_id}");
            return;
        }
        
        // Генерируем изображение
        $this->generate_image_for_post($post_id);
    }
    
    /**
     * Генерация изображения для поста
     */
    public function generate_image_for_post($post_id) {
        try {
            $this->log('info', "Starting image generation for post {$post_id}");
            
            // Получаем мета-описание статьи
            $meta_description = $this->get_post_meta_description($post_id);
            
            if (empty($meta_description)) {
                $this->log('warning', "No meta description found for post {$post_id}");
                return false;
            }
            
            // Создаем промпт для генерации изображения
            $prompt = $this->create_image_prompt($meta_description);
            
            // Логируем сгенерированный промпт для отладки
            $this->log('info', "Generated prompt for post {$post_id}: " . $prompt);
            
            // ШАГ 1: ГЕНЕРИРУЕМ НОРМАЛЬНУЮ КАРТИНКУ через OpenAI
            $image_url = $this->generate_image_with_openai($prompt);
            
            if (!$image_url) {
                $this->log('error', "Failed to generate image for post {$post_id}");
                return false;
            }
            
            $this->log('info', "Step 1: Generated normal image from OpenAI: {$image_url}");
            
            // ШАГ 2: СОХРАНЯЕМ В БАЗУ ДАННЫХ И МЕДИАТЕКУ
            $attachment_id = $this->upload_image_to_media($image_url, $post_id);
            
            if (!$attachment_id) {
                $this->log('error', "Failed to save image to database and media library for post {$post_id}");
                return false;
            }
            
            $this->log('info', "Step 2: Saved image to database and media library, attachment ID: {$attachment_id}");
            
            // ШАГ 3: ТОЛЬКО ПОТОМ ДОБАВЛЯЕМ В СТАТЬЮ
            $result = set_post_thumbnail($post_id, $attachment_id);
            
            if (!$result) {
                $this->log('error', "Failed to set image as featured image for post {$post_id}");
                return false;
            }
            
            $this->log('info', "Step 3: Added image to article as featured image");
            
            // SEO оптимизация изображения
            if ($this->settings['enable_seo_optimization']) {
                $this->optimize_image_seo($attachment_id, $post_id);
            }
            
            // Логируем успех
            $this->log('info', "Successfully generated and attached image for post {$post_id}");
            
            // Сохраняем информацию о генерации
            $this->save_generation_log($post_id, $attachment_id, $prompt, 'success');

            // Вставляем изображение в контент после оглавления (idempotent)
            $this->ensure_content_image_after_toc($post_id);
            
            return true;
            
        } catch (Exception $e) {
            $this->log('error', "Exception in generate_image_for_post for post {$post_id}: " . $e->getMessage());
            $this->save_generation_log($post_id, null, $prompt ?? '', 'error', $e->getMessage());
            return false;
        }
    }

    /**
     * Вставляет блок изображения после оглавления (или перед первым H2), используя featured image
     * Повторные вызовы безопасны: дубликаты не создаются
     */
    private function ensure_content_image_after_toc($post_id) {
        try {
            $attachment_id = get_post_thumbnail_id($post_id);
            if (!$attachment_id) {
                $this->log('debug', "ensure_content_image_after_toc: no featured image for post {$post_id}");
                return;
            }

            $image_url = wp_get_attachment_image_url($attachment_id, 'large');
            if (!$image_url) {
                $this->log('debug', "ensure_content_image_after_toc: no image url for attachment {$attachment_id}, post {$post_id}");
                return;
            }

            $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if (empty($alt)) {
                $post = get_post($post_id);
                $alt = $post ? wp_strip_all_tags($post->post_title) : '';
            }

            $post = get_post($post_id);
            if (!$post) { return; }
            $content = $post->post_content ?? '';

            // Уже вставлено? Проверяем по URL вложения
            if (strpos($content, $image_url) !== false) {
                $this->log('debug', "ensure_content_image_after_toc: image already present in content for post {$post_id}");
                return;
            }

            // Готовим Gutenberg core/image блок с iOS стилями
            $block = "<!-- wp:image {\"id\":$attachment_id,\"sizeSlug\":\"large\",\"linkDestination\":\"none\",\"className\":\"ios-style-image alignwide\"} -->\n";
            $block .= "<figure class=\"wp-block-image size-large ios-style-image alignwide\"><img src=\"{$image_url}\" alt=\"" . esc_attr($alt) . "\" class=\"wp-image-{$attachment_id}\" /></figure>\n";
            $block .= "<!-- /wp:image -->\n";

            // Ищем позицию: сразу после оглавления (<nav class=\"toc\">)</n+            $inserted = false;
            $tocPos = stripos($content, '<nav class="toc"');
            if ($tocPos !== false) {
                $endNav = stripos($content, '</nav>', $tocPos);
                if ($endNav !== false) {
                    $insertAt = $endNav + 6; // после </nav>
                    $content = substr($content, 0, $insertAt) . "\n" . $block . substr($content, $insertAt);
                    $inserted = true;
                }
            }

            // Fallback: перед первым H2
            if (!$inserted) {
                $h2Pos = stripos($content, '<h2');
                if ($h2Pos !== false) {
                    $content = substr($content, 0, $h2Pos) . $block . substr($content, $h2Pos);
                    $inserted = true;
                }
            }

            // Если не нашли ориентир — добавим после первого блока wp:paragraph
            if (!$inserted) {
                $pPos = stripos($content, '<p');
                if ($pPos !== false) {
                    $content = substr($content, 0, $pPos) . $block . substr($content, $pPos);
                    $inserted = true;
                }
            }

            if ($inserted) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $content,
                ]);
                update_post_meta($post_id, '_abp_content_image_inserted', 1);
                $this->log('info', "ensure_content_image_after_toc: image block inserted for post {$post_id}");
            } else {
                $this->log('debug', "ensure_content_image_after_toc: no suitable insertion point for post {$post_id}");
            }
        } catch (Exception $e) {
            $this->log('error', 'ensure_content_image_after_toc exception: ' . $e->getMessage());
        }
    }

    /**
     * Добавляет класс на <body> для постов, где картинка вставлена после TOC
     */
    public function filter_body_class_flag_inline_image($classes) {
        if (is_single()) {
            $post_id = get_queried_object_id();
            if ($post_id && get_post_meta($post_id, '_abp_content_image_inserted', true)) {
                $classes[] = 'abp-inline-image-after-toc';
            }
        }
        return $classes;
    }

    /**
     * Подключает стили, скрывающие верхнее featured изображение темы,
     * если для поста используется inline-картинка после TOC
     */
    public function enqueue_front_styles() {
        if (!is_single()) { return; }
        $css = '.abp-inline-image-after-toc .entry-header .post-thumb, .abp-inline-image-after-toc .ast-single-post .post-thumb, .abp-inline-image-after-toc .featured-image { display: none !important; }';
        wp_register_style('abp-inline-image-fix', false);
        wp_enqueue_style('abp-inline-image-fix');
        wp_add_inline_style('abp-inline-image-fix', $css);
    }
    
    /**
     * Получение мета-описания поста
     */
    private function get_post_meta_description($post_id) {
        // Пробуем получить из Yoast SEO
        $yoast_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        if (!empty($yoast_description)) {
            return $yoast_description;
        }
        
        // Пробуем получить из RankMath
        $rankmath_description = get_post_meta($post_id, 'rank_math_description', true);
        if (!empty($rankmath_description)) {
            return $rankmath_description;
        }
        
        // Используем excerpt если есть
        $excerpt = get_the_excerpt($post_id);
        if (!empty($excerpt)) {
            return $excerpt;
        }
        
        // Используем первые 160 символов контента
        $content = get_post_field('post_content', $post_id);
        $content = wp_strip_all_tags($content);
        $content = wp_trim_words($content, 30);
        
        return $content;
    }
    
    /**
     * Создание улучшенного промпта для генерации изображения
     */
    private function create_image_prompt($description) {
        // Очищаем описание от HTML тегов и лишних символов
        $clean_description = wp_strip_all_tags($description);
        $clean_description = preg_replace('/[^\p{L}\p{N}\s.,!?-]/u', '', $clean_description);
        
        // Ограничиваем длину промпта (DALL-E имеет лимит)
        $clean_description = wp_trim_words($clean_description, 30);
        
        // Определяем тематику статьи для более точного промпта
        $theme_context = $this->analyze_article_theme($clean_description);
        
        // Создаем детальный промпт для DALL-E 2 с учетом его "тупости"
        $prompt = $this->build_detailed_prompt($clean_description, $theme_context);
        
        return $prompt;
    }
    
    /**
     * Анализ тематики статьи для создания контекстного промпта
     */
    private function analyze_article_theme($description) {
        $description_lower = mb_strtolower($description, 'UTF-8');
        
        // Финансовые темы
        if (strpos($description_lower, 'банк') !== false || strpos($description_lower, 'кредит') !== false) {
            return 'banking';
        }
        if (strpos($description_lower, 'инвестиц') !== false || strpos($description_lower, 'акци') !== false) {
            return 'investment';
        }
        if (strpos($description_lower, 'страхован') !== false || strpos($description_lower, 'гарант') !== false) {
            return 'insurance';
        }
        if (strpos($description_lower, 'налог') !== false || strpos($description_lower, 'бюджет') !== false) {
            return 'tax';
        }
        if (strpos($description_lower, 'бизнес') !== false || strpos($description_lower, 'предприниматель') !== false) {
            return 'business';
        }
        if (strpos($description_lower, 'недвижимость') !== false || strpos($description_lower, 'ипотека') !== false) {
            return 'real_estate';
        }
        if (strpos($description_lower, 'карт') !== false || strpos($description_lower, 'платеж') !== false) {
            return 'payments';
        }
        
        return 'general_finance';
    }
    
    /**
     * Построение детального промпта для DALL-E 2 (АБСТРАКТНОЕ ИСКУССТВО)
     */
    private function build_detailed_prompt($description, $theme) {
        // Выбираем стиль художника в зависимости от темы
        $artist_style = $this->get_artist_style_for_theme($theme);
        
        // Базовые инструкции для абстрактного искусства
        $base_instructions = "High-quality abstract artwork in the style of " . $artist_style . ". ";
        $base_instructions .= "Professional artistic composition with vibrant colors and dynamic forms. ";
        $base_instructions .= "Modern abstract expressionism with sophisticated color palette. ";
        $base_instructions .= "Elegant geometric shapes and flowing organic forms. ";
        $base_instructions .= "Premium artistic quality, museum-worthy abstract painting. ";
        $base_instructions .= "Rich textures and layered composition. ";
        $base_instructions .= "Contemporary abstract art with professional execution. ";
        
        // Специфичные инструкции по темам (АБСТРАКТНОЕ ИСКУССТВО)
        $theme_instructions = $this->get_theme_specific_abstract_instructions($theme);
        
        // Контекстная часть
        $context_part = "The artwork represents the concept of: " . $description . ". ";
        
        // Финальная сборка промпта
        $prompt = $base_instructions . $theme_instructions . $context_part;
        
        // Ограничиваем длину для DALL-E 2 (максимум ~1000 символов)
        if (strlen($prompt) > 900) {
            $prompt = substr($prompt, 0, 900);
            $prompt = rtrim($prompt, '. ') . '.';
        }
        
        return $prompt;
    }
    
    /**
     * Выбор стиля художника в зависимости от темы
     */
    private function get_artist_style_for_theme($theme) {
        $artists = [
            'banking' => 'Wassily Kandinsky',
            'investment' => 'Piet Mondrian', 
            'insurance' => 'Mark Rothko',
            'tax' => 'Paul Klee',
            'business' => 'Jackson Pollock',
            'real_estate' => 'Joan Miró',
            'payments' => 'Kazimir Malevich',
            'general_finance' => 'Willem de Kooning'
        ];
        
        return $artists[$theme] ?? $artists['general_finance'];
    }
    
    /**
     * Получение специфичных инструкций по темам (АБСТРАКТНОЕ ИСКУССТВО)
     */
    private function get_theme_specific_abstract_instructions($theme) {
        $instructions = [
            'banking' => "Deep blues and golds representing trust and stability. Geometric forms suggesting security and structure. Dynamic composition with flowing lines representing financial growth. ",
            'investment' => "Bold geometric patterns in greens and blues symbolizing growth and opportunity. Dynamic intersecting lines representing market movements and portfolio diversification. ",
            'insurance' => "Warm, protective colors in oranges and deep reds. Layered composition suggesting coverage and protection. Organic forms representing security and peace of mind. ",
            'tax' => "Structured geometric patterns in professional grays and blues. Precise lines and organized composition representing accuracy and compliance. ",
            'business' => "Dynamic composition with energetic brushstrokes in corporate colors. Bold forms representing innovation and business growth. ",
            'real_estate' => "Earth tones and architectural forms. Geometric shapes suggesting buildings and property. Warm colors representing home and investment. ",
            'payments' => "Modern digital-inspired patterns in metallic colors. Flowing lines representing money movement and digital transactions. ",
            'general_finance' => "Sophisticated color palette with blues, greens, and golds. Balanced composition representing financial stability and growth potential. "
        ];
        
        return $instructions[$theme] ?? $instructions['general_finance'];
    }
    
    /**
     * Получение специфичных инструкций по темам (БЕЗ ЛЮДЕЙ)
     */
    private function get_theme_specific_instructions_no_people($theme) {
        $instructions = [
            'banking' => "Modern bank office interior with banking equipment, computers, documents, cash registers, and professional furniture. Clean, organized workspace with banking symbols and equipment. Professional, trustworthy atmosphere. ",
            'investment' => "Modern investment office with financial charts, graphs, financial data on computer screens, stock market displays, and investment documents. Professional investment environment with financial tools and equipment. ",
            'insurance' => "Insurance office with insurance documents, policies, contracts, and professional consultation desk. Clean, organized workspace with insurance-related materials and equipment. ",
            'tax' => "Tax office or accounting firm with tax documents, calculators, accounting software on computers, and professional desk setup. Clean, organized workspace with tax-related materials. ",
            'business' => "Modern business office with professional furniture, business documents, meeting room setup, and corporate equipment. Clean, organized workspace with business materials and technology. ",
            'real_estate' => "Real estate office with property documents, keys, property listings, and professional desk setup. Clean, organized workspace with real estate materials and equipment. ",
            'payments' => "Modern payment processing center with credit cards, payment terminals, digital payment systems, and banking equipment. Clean, organized workspace with payment technology. ",
            'general_finance' => "Professional financial office with financial documents, charts, computers with financial software, and professional desk setup. Clean, organized workspace with financial materials and equipment. "
        ];
        
        return $instructions[$theme] ?? $instructions['general_finance'];
    }
    
    /**
     * Получение специфичных инструкций по темам (СТАРАЯ ВЕРСИЯ С ЛЮДЬМИ)
     */
    private function get_theme_specific_instructions($theme) {
        $instructions = [
            'banking' => "Modern bank office with professional bankers in suits. Banking equipment, computers, documents. Professional, trustworthy atmosphere. ",
            'investment' => "Modern investment office with financial advisors and clients. Charts, graphs, financial data on screens. Professional investment environment. ",
            'insurance' => "Insurance office with professional agents and clients. Insurance documents, policies, professional consultation setting. ",
            'tax' => "Tax office or accounting firm with tax professionals and clients. Tax documents, calculators, professional consultation. ",
            'business' => "Modern business office with entrepreneurs and business professionals. Business meeting, handshakes, professional collaboration. ",
            'real_estate' => "Real estate office with agents and clients. Property documents, keys, professional real estate consultation. ",
            'payments' => "Modern payment processing center or bank with payment specialists. Credit cards, payment terminals, digital payment systems. ",
            'general_finance' => "Professional financial office with financial advisors and clients. Financial documents, charts, professional consultation. "
        ];
        
        return $instructions[$theme] ?? $instructions['general_finance'];
    }
    
    /**
     * Генерация изображения через OpenAI DALL-E
     */
    private function generate_image_with_openai($prompt, $attempt = 1) {
        try {
            $this->log('info', "Generating image with OpenAI, attempt {$attempt}");
            
            $request_data = [
                'model' => $this->settings['model'],
                'prompt' => $prompt,
                'n' => 1,
                'size' => $this->settings['size']
            ];
            
            // Добавляем параметры только для DALL-E 3
            if ($this->settings['model'] === 'dall-e-3') {
                $request_data['quality'] = $this->settings['quality'];
                $request_data['style'] = $this->settings['style'];
            }
            
            $response = wp_remote_post(self::OPENAI_API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::OPENAI_API_KEY,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($request_data),
                'timeout' => 120,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'cookies' => [],
                'sslverify' => true,
                'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            ]);
            
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->log('error', 'OpenAI API request failed: ' . $error_message);
                
                // Логируем дополнительную информацию для отладки
                $this->log('error', 'Request data: ' . json_encode($request_data));
                $this->log('error', 'Response code: ' . wp_remote_retrieve_response_code($response));
                $this->log('error', 'Response headers: ' . json_encode(wp_remote_retrieve_headers($response)));
                
                return false;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            // Логируем успешный ответ для отладки
            $this->log('info', 'OpenAI API response received, status: ' . wp_remote_retrieve_response_code($response));
            $this->log('info', 'Response body length: ' . strlen($body));
            
            if (isset($data['error'])) {
                $this->log('error', 'OpenAI API error: ' . $data['error']['message']);
                
                // Повторяем попытку если не достигли лимита
                if ($attempt < $this->settings['max_attempts']) {
                    sleep($this->settings['retry_delay']);
                    return $this->generate_image_with_openai($prompt, $attempt + 1);
                }
                
                return false;
            }
            
            if (isset($data['data'][0]['url'])) {
                $this->log('info', 'Image generated successfully');
                return $data['data'][0]['url'];
            }
            
            $this->log('error', 'No image URL in OpenAI response');
            return false;
            
        } catch (Exception $e) {
            $this->log('error', 'Exception in generate_image_with_openai: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Загрузка изображения в медиатеку WordPress (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    private function upload_image_to_media($image_url, $post_id) {
        try {
            // Получаем информацию о посте для имени файла (сокращенное)
            $post = get_post($post_id);
            $short_title = wp_trim_words($post->post_title, 8, '');
            
            // Транслитерация кириллических символов в латинские
            $filename = $this->transliterate_filename($short_title) . '.png';
            
            // ШАГ 1: ПОЛУЧАЕМ НОРМАЛЬНУЮ КАРТИНКУ (уже получена через OpenAI)
            $image_data = file_get_contents($image_url);
            if (!$image_data) {
                $this->log('error', 'Failed to download image from URL');
                return false;
            }
            
            $this->log('info', "Step 1: Downloaded image from OpenAI: {$image_url}");
            
            // ШАГ 2: СОЗДАЕМ ЗАПИСЬ В БАЗЕ ДАННЫХ ПЕРВЫМ (attachment)
            $attachment = [
                'post_mime_type' => 'image/png',
                'post_title' => $post->post_title,
                'post_content' => "Иллюстрация к статье: " . $post->post_title,
                'post_status' => 'inherit',
                'post_parent' => $post_id
            ];
            
            // Создаем attachment запись в базе данных БЕЗ файла
            $attachment_id = wp_insert_attachment($attachment, '', $post_id);
            
            if (!$attachment_id || is_wp_error($attachment_id)) {
                $this->log('error', 'Failed to create attachment record in database');
                return false;
            }
            
            $this->log('info', "Step 2: Created attachment record in database, ID: {$attachment_id}");
            
            // ШАГ 3: ТЕПЕРЬ ЗАГРУЖАЕМ ФАЙЛ В МЕДИАТЕКУ
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['path'] . '/' . $filename;
            $upload_url = $upload_dir['url'] . '/' . $filename;
            
            // Сохраняем файл
            if (file_put_contents($upload_path, $image_data) === false) {
                $this->log('error', 'Failed to save image file to media library');
                wp_delete_attachment($attachment_id, true); // Удаляем запись из БД
                return false;
            }
            
            // Исправляем права доступа к файлу
            chmod($upload_path, 0644);
            $upload_dir_stat = stat($upload_dir['basedir']);
            if ($upload_dir_stat && isset($upload_dir_stat['uid'], $upload_dir_stat['gid'])) {
                chown($upload_path, $upload_dir_stat['uid']);
                chgrp($upload_path, $upload_dir_stat['gid']);
            }
            
            $this->log('info', "Step 3: Saved image file to media library: {$upload_path}");
            
            // ШАГ 4: ОБНОВЛЯЕМ ЗАПИСЬ В БАЗЕ ДАННЫХ С ПРАВИЛЬНЫМ ПУТЕМ К ФАЙЛУ
            $relative_path = $upload_dir['subdir'] . '/' . $filename;
            update_post_meta($attachment_id, '_wp_attached_file', $relative_path);
            
            // Генерируем метаданные
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            // Устанавливаем ALT текст
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $post->post_title);
            
            $this->log('info', "Step 4: Updated attachment metadata in database");
            
            // ШАГ 5: ПРОВЕРЯЕМ, ЧТО ФАЙЛ ДЕЙСТВИТЕЛЬНО ДОСТУПЕН
            if (!file_exists($upload_path)) {
                $this->log('error', "File does not exist after saving: {$upload_path}");
                return false;
            }
            
            // ШАГ 6: ПРОВЕРЯЕМ, ЧТО ФАЙЛ ДЕЙСТВИТЕЛЬНО ДОСТУПЕН ПО URL
            $test_url = $upload_dir['url'] . '/' . $filename;
            $this->log('info', "Image URL: {$test_url}");
            
            return $attachment_id;
            
        } catch (Exception $e) {
            $this->log('error', 'Exception in upload_image_to_media: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Транслитерация кириллических символов в латинские для имени файла
     */
    private function transliterate_filename($text) {
        $transliteration = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        ];
        
        $transliterated = strtr($text, $transliteration);
        
        // Убираем специальные символы и заменяем пробелы на дефисы
        $transliterated = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $transliterated);
        $transliterated = preg_replace('/\s+/', '-', $transliterated);
        $transliterated = preg_replace('/-+/', '-', $transliterated);
        $transliterated = trim($transliterated, '-');
        
        // Ограничиваем длину имени файла
        if (strlen($transliterated) > 50) {
            $transliterated = substr($transliterated, 0, 50);
            $transliterated = rtrim($transliterated, '-');
        }
        
        return $transliterated ?: 'image-' . $post_id;
    }
    
    /**
     * SEO оптимизация изображения
     */
    private function optimize_image_seo($attachment_id, $post_id) {
        $post = get_post($post_id);
        
        // Alt текст
        if ($this->settings['auto_alt_text']) {
            $alt_text = $post->post_title;
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
        
        // Описание изображения
        if ($this->settings['auto_description']) {
            $description = "Иллюстрация к статье: " . $post->post_title;
            wp_update_post([
                'ID' => $attachment_id,
                'post_content' => $description
            ]);
        }
        // ВАЖНО: не перезаписываем _wp_attached_file — иначе ломаем физический путь к файлу
        // Можно обновить только заголовок вложения для SEO
        wp_update_post([
            'ID' => $attachment_id,
            'post_title' => wp_trim_words($post->post_title, 12, '')
        ]);
        
        $this->log('info', "SEO optimization completed for attachment {$attachment_id} (path preserved)");
    }
    
    /**
     * Проверка SEO соответствия изображения
     */
    public function verify_image_seo($post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return [
                'has_image' => false,
                'seo_score' => 0,
                'issues' => ['Отсутствует главное изображение']
            ];
        }
        
        $issues = [];
        $score = 100;
        
        // Проверяем alt текст
        $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        if (empty($alt_text)) {
            $issues[] = 'Отсутствует alt текст';
            $score -= 30;
        }
        
        // Проверяем описание
        $description = get_post_field('post_content', $thumbnail_id);
        if (empty($description)) {
            $issues[] = 'Отсутствует описание изображения';
            $score -= 20;
        }
        
        // Проверяем размер файла
        $file_path = get_attached_file($thumbnail_id);
        if ($file_path && file_exists($file_path) && filesize($file_path) > 2 * 1024 * 1024) { // 2MB
            $issues[] = 'Изображение слишком большое (>2MB)';
            $score -= 10;
        }
        
        return [
            'has_image' => true,
            'seo_score' => max(0, $score),
            'issues' => $issues,
            'attachment_id' => $thumbnail_id
        ];
    }
    
    /**
     * AJAX обработчик для генерации изображения
     */
    public function ajax_generate_image() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }
        
        $result = $this->generate_image_for_post($post_id);
        
        if ($result) {
            wp_send_json_success(['message' => 'Image generated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to generate image']);
        }
    }
    
    /**
     * AJAX обработчик для массовой генерации
     */
    public function ajax_bulk_generate_images() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $post_ids = $_POST['post_ids'] ?? [];
        
        if (empty($post_ids)) {
            wp_send_json_error(['message' => 'No posts selected']);
        }
        
        $results = [];
        $success_count = 0;
        
        foreach ($post_ids as $post_id) {
            $post_id = intval($post_id);
            if ($post_id && !has_post_thumbnail($post_id)) {
                $result = $this->generate_image_for_post($post_id);
                $results[] = [
                    'post_id' => $post_id,
                    'success' => $result
                ];
                
                if ($result) {
                    $success_count++;
                }
                
                // Задержка между запросами
                sleep(2);
            }
        }
        
        wp_send_json_success([
            'message' => "Generated {$success_count} images",
            'results' => $results
        ]);
    }

    /**
     * Массовая генерация по первой букве заголовка
     */
    public function ajax_generate_images_by_letter() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $letter = sanitize_text_field($_POST['letter'] ?? '');
        if ($letter === '') {
            wp_send_json_error(['message' => 'Не передана буква']);
        }
        $L = mb_strtoupper($letter, 'UTF-8');
        // Находим посты на нужную букву (включая те, что уже имеют featured image)
        $q = new WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
            's'              => $L, // простой фильтр, точнее см. ниже
        ]);

        $processed = 0; $generated = 0; $details = [];
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $post_id = get_the_ID();
                $title = get_the_title();
                $first = mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8');
                if ($first !== $L) { continue; }
                
                $processed++;
                
                // Проверяем, есть ли featured image и корректный ли он
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $should_regenerate = false;
                
                if ($thumbnail_id) {
                    // Проверяем, существует ли физический файл
                    $file_path = get_attached_file($thumbnail_id);
                    if (!$file_path || !file_exists($file_path)) {
                        $should_regenerate = true;
                        $this->log('info', "Post {$post_id} has broken thumbnail, will regenerate");
                    }
                } else {
                    $should_regenerate = true;
                    $this->log('info', "Post {$post_id} has no thumbnail, will generate");
                }
                
                if ($should_regenerate) {
                    $ok = $this->generate_image_for_post($post_id);
                    if ($ok) { $generated++; }
                } else {
                    $ok = true; // Уже есть корректное изображение
                    $this->log('info', "Post {$post_id} already has valid thumbnail");
                }
                
                $details[] = ['post_id' => $post_id, 'ok' => $ok, 'regenerated' => $should_regenerate];
                // бережём API
                sleep(2);
            }
            wp_reset_postdata();
        }

        wp_send_json_success([
            'message'   => "Processed {$processed}, generated {$generated}",
            'processed' => $processed,
            'generated' => $generated,
            'details'   => $details,
        ]);
    }
    
    /**
     * AJAX обработчик для получения статистики
     */
    public function ajax_get_stats() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        global $wpdb;
        
        // Статистика по постам
        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'");
        $posts_with_images = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} p 
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
            WHERE p.post_type = 'post' AND p.post_status = 'publish' 
            AND pm.meta_key = '_thumbnail_id' AND pm.meta_value != ''
        ");
        
        // Статистика генераций
        $generation_stats = $wpdb->get_results("
            SELECT status, COUNT(*) as count 
            FROM {$wpdb->prefix}abp_image_generations 
            GROUP BY status
        ", ARRAY_A);
        
        wp_send_json_success([
            'total_posts' => intval($total_posts),
            'posts_with_images' => intval($posts_with_images),
            'posts_without_images' => intval($total_posts) - intval($posts_with_images),
            'generation_stats' => $generation_stats
        ]);
    }

    /**
     * Прикрепить существующее изображение из медиатеки как featured к посту
     */
    public function ajax_attach_existing_image() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $post_id = intval($_POST['post_id'] ?? 0);
        $attachment_id = intval($_POST['attachment_id'] ?? 0);
        if (!$post_id || !$attachment_id) {
            wp_send_json_error(['message' => 'Invalid post or attachment']);
        }
        if (get_post_type($post_id) !== 'post') {
            wp_send_json_error(['message' => 'Only posts are supported']);
        }
        // Проверим, что attachment существует и это изображение
        $mime = get_post_mime_type($attachment_id);
        if (strpos($mime, 'image/') !== 0) {
            wp_send_json_error(['message' => 'Attachment is not an image']);
        }
        // Чиним недостающие метаданные, если нужно
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            wp_send_json_error(['message' => 'Physical file not found for attachment']);
        }
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $meta = wp_generate_attachment_metadata($attachment_id, $file);
        if ($meta) {
            wp_update_attachment_metadata($attachment_id, $meta);
        }
        // Назначаем featured image
        $ok = set_post_thumbnail($post_id, $attachment_id);
        if (!$ok) {
            wp_send_json_error(['message' => 'Failed to set featured image']);
        }
        wp_send_json_success(['message' => 'Attached successfully']);
    }

    /**
     * Привязать изображение по URL (если attachment не существует — создать)
     */
    public function ajax_attach_image_by_url() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $post_id = intval($_POST['post_id'] ?? 0);
        $url = esc_url_raw($_POST['url'] ?? '');
        if (!$post_id || empty($url)) {
            wp_send_json_error(['message' => 'Invalid post or url']);
        }
        // Находим существующее вложение по GUID (_wp_attached_file тоже попробуем)
        global $wpdb;        
        $attachment_id = (int) $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND guid=%s LIMIT 1", $url));
        if (!$attachment_id) {
            // Попробуем по относительному пути
            $uploads = wp_get_upload_dir();
            $relative = ltrim(str_replace($uploads['baseurl'], '', $url), '/');
            $attachment_id = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value=%s LIMIT 1", $relative));
        }
        if (!$attachment_id) {
            // Создадим attachment: пытаемся сопоставить URL с локальным путём (с учётом urldecode)
            $uploads = wp_get_upload_dir();
            $relative = ltrim(str_replace($uploads['baseurl'], '', $url), '/');
            $decodedRelative = urldecode($relative);
            $path = $uploads['basedir'] . '/' . $decodedRelative;
            if (!file_exists($path)) {
                // Файл не найден локально — пробуем скачать по URL
                $response = wp_remote_get($url, ['timeout' => 30]);
                if (is_wp_error($response)) {
                    wp_send_json_error(['message' => 'Download failed: ' . $response->get_error_message()]);
                }
                $body = wp_remote_retrieve_body($response);
                if (empty($body)) {
                    wp_send_json_error(['message' => 'Empty file body from URL']);
                }
                // Обеспечим директорию
                $targetDir = dirname($path);
                if (!file_exists($targetDir)) { wp_mkdir_p($targetDir); }
                // Имя файла из URL (декодированное)
                $basename = basename(parse_url($url, PHP_URL_PATH));
                $decoded = urldecode($basename);
                $safe = sanitize_file_name($decoded);
                if (!$safe) { $safe = 'image-' . time() . '.webp'; }
                $path = trailingslashit($targetDir) . $safe;
                file_put_contents($path, $body);
                @chmod($path, 0644);
                $relative = ltrim(str_replace($uploads['basedir'], '', $path), '/');
            }
            $filetype = wp_check_filetype(basename($path), null);
            $attachment = [
                'guid'           => trailingslashit($uploads['baseurl']) . ltrim($relative, '/'),
                'post_mime_type' => $filetype['type'] ?: 'image/webp',
                'post_title'     => sanitize_file_name(pathinfo($path, PATHINFO_FILENAME)),
                'post_status'    => 'inherit'
            ];
            $attachment_id = wp_insert_attachment($attachment, $path, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attachment_id, $path);
            if ($attach_data) { wp_update_attachment_metadata($attachment_id, $attach_data); }
            update_post_meta($attachment_id, '_wp_attached_file', ltrim($relative, '/'));
        }
        // Назначаем featured image
        if (!set_post_thumbnail($post_id, $attachment_id)) {
            wp_send_json_error(['message' => 'Failed to set featured image']);
        }
        wp_send_json_success(['message' => 'Image attached by URL', 'attachment_id' => $attachment_id]);
    }

    /**
     * Регенерировать миниатюры для attachment
     */
    public function ajax_regenerate_thumbnails() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $attachment_id = intval($_POST['attachment_id'] ?? 0);
        if (!$attachment_id) {
            wp_send_json_error(['message' => 'Invalid attachment id']);
        }
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            wp_send_json_error(['message' => 'File not found']);
        }
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $meta = wp_generate_attachment_metadata($attachment_id, $file);
        if ($meta) {
            wp_update_attachment_metadata($attachment_id, $meta);
            wp_send_json_success(['message' => 'Thumbnails regenerated']);
        } else {
            wp_send_json_error(['message' => 'Failed to generate thumbnails']);
        }
    }

    /**
     * Диагностика featured image для поста
     */
    public function ajax_debug_post_thumbnail() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post id']);
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => 'Post not found']);
        }
        
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $has_thumbnail = has_post_thumbnail($post_id);
        $thumbnail_html = get_the_post_thumbnail($post_id, 'medium');
        
        $debug_info = [
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'thumbnail_id' => $thumbnail_id,
            'has_post_thumbnail' => $has_thumbnail,
            'thumbnail_html' => $thumbnail_html,
            'thumbnail_url' => $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : null,
            'thumbnail_file' => $thumbnail_id ? get_attached_file($thumbnail_id) : null,
            'file_exists' => $thumbnail_id ? file_exists(get_attached_file($thumbnail_id)) : false
        ];
        
        wp_send_json_success($debug_info);
    }

    /**
     * Восстановить «битое» вложение: найти файл и восстановить метаданные
     */
    public function ajax_repair_broken_attachments() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $attachment_id = intval($_POST['attachment_id'] ?? 0);
        if (!$attachment_id) {
            wp_send_json_error(['message' => 'Invalid attachment id']);
        }
        $file = get_attached_file($attachment_id);
        if ($file && file_exists($file)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $meta = wp_generate_attachment_metadata($attachment_id, $file);
            if ($meta) { wp_update_attachment_metadata($attachment_id, $meta); }
            wp_send_json_success(['message' => 'Attachment metadata regenerated']);
        }
        // Попробуем найти файл по basename в uploads
        $stored = get_post_meta($attachment_id, '_wp_attached_file', true);
        $basename = $stored ? basename($stored) : null;
        if (!$basename) {
            $title = get_the_title($attachment_id);
            $basename = sanitize_file_name(wp_trim_words($title, 8, ''));
        }
        $uploads = wp_get_upload_dir();
        $base = trailingslashit($uploads['basedir']);
        $foundPath = '';
        if ($basename && is_dir($base)) {
            if (class_exists('RecursiveDirectoryIterator')) {
                $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
                foreach ($rii as $f) {
                    if ($f->isDir()) continue;
                    $fname = basename($f->getPathname());
                    if ($fname === $basename || stripos($fname, pathinfo($basename, PATHINFO_FILENAME)) === 0) {
                        $foundPath = $f->getPathname();
                        break;
                    }
                }
            }
        }
        if (!$foundPath || !file_exists($foundPath)) {
            wp_send_json_error(['message' => 'File not found on disk']);
        }
        // Обновляем _wp_attached_file относительным путём
        $relative = ltrim(str_replace($base, '', $foundPath), '/');
        update_post_meta($attachment_id, '_wp_attached_file', $relative);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $meta = wp_generate_attachment_metadata($attachment_id, $foundPath);
        if ($meta) { wp_update_attachment_metadata($attachment_id, $meta); }
        wp_send_json_success(['message' => 'Attachment repaired', 'relative' => $relative]);
    }
    
    /**
     * Добавление админ-меню
     */
    public function add_admin_menu() {
        add_menu_page(
            'ABP Image Generator',
            'Image Generator',
            'manage_options',
            'abp-image-generator',
            [$this, 'admin_page'],
            'dashicons-format-image',
            30
        );
        
        add_submenu_page(
            'abp-image-generator',
            'Настройки',
            'Настройки',
            'manage_options',
            'abp-image-generator-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'abp-image-generator',
            'Статистика',
            'Статистика',
            'manage_options',
            'abp-image-generator-stats',
            [$this, 'stats_page']
        );
        
        add_submenu_page(
            'abp-image-generator',
            'Все статьи',
            'Все статьи',
            'manage_options',
            'abp-image-generator-all-posts',
            [$this, 'all_posts_page']
        );
    }
    
    /**
     * Главная страница админки
     */
    public function admin_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin-main.php';
    }
    
    /**
     * Страница настроек
     */
    public function settings_page() {
        if (isset($_POST['save_settings'])) {
            $this->save_settings();
        }
        
        include plugin_dir_path(__FILE__) . 'templates/admin-settings.php';
    }
    
    /**
     * Страница статистики
     */
    public function stats_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin-stats.php';
    }
    
    /**
     * Сохранение настроек
     */
    private function save_settings() {
        $new_settings = [
            'auto_generate' => isset($_POST['auto_generate']),
            'model' => sanitize_text_field($_POST['model'] ?? 'dall-e-2'),
            'size' => sanitize_text_field($_POST['size'] ?? '1024x1024'),
            'quality' => sanitize_text_field($_POST['quality'] ?? 'standard'),
            'style' => sanitize_text_field($_POST['style'] ?? 'natural'),
            'max_attempts' => intval($_POST['max_attempts'] ?? 3),
            'retry_delay' => intval($_POST['retry_delay'] ?? 5),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'enable_seo_optimization' => isset($_POST['enable_seo_optimization']),
            'auto_alt_text' => isset($_POST['auto_alt_text']),
            'auto_description' => isset($_POST['auto_description'])
        ];
        
        update_option('abp_image_generator_settings', $new_settings);
        $this->settings = $new_settings;
        
        $this->log('info', 'Settings updated');
    }
    
    /**
     * Подключение админских ресурсов
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'abp-image-generator') === false) {
            return;
        }
        
        wp_enqueue_style('abp-image-generator-admin', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], self::VERSION);
        wp_enqueue_script('abp-image-generator-admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery'], self::VERSION, true);
        
        wp_localize_script('abp-image-generator-admin', 'ABPImageGenerator', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'strings' => [
                'generating' => 'Генерируем изображение...',
                'success' => 'Изображение успешно создано',
                'error' => 'Ошибка при создании изображения'
            ]
        ]);
    }
    
    /**
     * Создание директорий
     */
    private function ensure_directories() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/abp-image-generator';
        
        $directories = [
            $base_dir,
            $base_dir . '/logs',
            $base_dir . '/temp'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
    }
    
    /**
     * Создание директории для логов
     */
    private function ensure_log_directory() {
        $this->ensure_directories();
    }
    
    /**
     * Создание таблицы для логов генерации
     */
    private function create_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_image_generations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            post_id int(11) NOT NULL,
            attachment_id int(11) NULL,
            prompt text NOT NULL,
            status varchar(20) NOT NULL,
            error_message text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Сохранение лога генерации
     */
    private function save_generation_log($post_id, $attachment_id, $prompt, $status, $error_message = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_image_generations';
        
        $wpdb->insert(
            $table_name,
            [
                'post_id' => $post_id,
                'attachment_id' => $attachment_id,
                'prompt' => $prompt,
                'status' => $status,
                'error_message' => $error_message
            ],
            [
                '%d', '%d', '%s', '%s', '%s'
            ]
        );
    }
    
    /**
     * Логирование
     */
    private function log($level, $message) {
        $log_levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
        $current_level = $log_levels[$this->settings['log_level']] ?? 1;
        
        if ($log_levels[$level] < $current_level) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        $log_file = $this->log_file . 'abp-image-generator-' . date('Y-m-d') . '.log';
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Также логируем в WordPress debug.log если включен
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("ABP Image Generator [{$level}]: {$message}");
        }
    }
    
    /**
     * AJAX обработчик для получения постов без изображений
     */
    public function ajax_get_posts_without_images() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 50,
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);
        
        $posts_data = [];
        foreach ($posts as $post) {
            $posts_data[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'date' => date('d.m.Y', strtotime($post->post_date)),
                'author' => get_the_author_meta('display_name', $post->post_author),
                'excerpt' => wp_trim_words($post->post_excerpt ?: $post->post_content, 20)
            ];
        }
        
        wp_send_json_success(['posts' => $posts_data]);
    }
    
    /**
     * AJAX обработчик для проверки SEO поста
     */
    public function ajax_check_post_seo() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }
        
        $seo_result = $this->verify_image_seo($post_id);
        
        wp_send_json_success($seo_result);
    }
    
    /**
     * AJAX обработчик для тестирования OpenAI API
     */
    public function ajax_test_openai_api() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        try {
            $test_prompt = "Simple test image: a small blue circle on white background";
            
            $request_data = [
                'model' => 'dall-e-2',
                'prompt' => $test_prompt,
                'n' => 1,
                'size' => '256x256'
            ];
            
            $response = wp_remote_post(self::OPENAI_API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::OPENAI_API_KEY,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($request_data),
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                wp_send_json_error(['message' => 'API request failed: ' . $response->get_error_message()]);
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['error'])) {
                wp_send_json_error(['message' => 'API error: ' . $data['error']['message']]);
            }
            
            if (isset($data['data'][0]['url'])) {
                wp_send_json_success(['message' => 'API работает корректно', 'test_url' => $data['data'][0]['url']]);
            }
            
            wp_send_json_error(['message' => 'Unexpected API response format']);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Exception: ' . $e->getMessage()]);
        }
    }
    
    /**
     * AJAX обработчик для получения логов
     */
    public function ajax_get_logs() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $log_file = $this->log_file . 'abp-image-generator-' . date('Y-m-d') . '.log';
        
        $logs = [];
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            $log_lines = array_reverse(array_slice(explode("\n", $log_content), -50)); // Последние 50 строк
            
            foreach ($log_lines as $line) {
                if (empty(trim($line))) continue;
                
                if (preg_match('/^\[([^\]]+)\] \[([^\]]+)\] (.+)$/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level' => $matches[2],
                        'message' => $matches[3]
                    ];
                }
            }
        }
        
        wp_send_json_success(['logs' => $logs]);
    }
    
    /**
     * AJAX обработчик для проверки интеграции с блогом
     */
    public function ajax_check_blog_integration() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $integration = [
            'alphabet_blog_panel' => class_exists('ABP_V2_Plugin'),
            'yoast_integration' => class_exists('YoastAlphabetIntegration'),
            'ai_categorization' => class_exists('ABP_AI_Categorization'),
            'quality_monitor' => class_exists('ABP_Article_Quality_Monitor')
        ];
        
        wp_send_json_success($integration);
    }
    
    /**
     * Интеграция с системой блога
     */
    public function process_post_for_image($post_id, $post_data) {
        if (isset($post_data['generate_image']) && $post_data['generate_image']) {
            $this->generate_image_for_post($post_id);
        }
    }
    
    /**
     * Страница всех статей
     */
    public function all_posts_page() {
        include plugin_dir_path(__FILE__) . 'templates/all-posts.php';
    }
    
    /**
     * AJAX обработчик регенерации изображения для одной статьи
     */
    public function ajax_regenerate_single_image() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error('Неверный ID поста');
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'post') {
            wp_send_json_error('Пост не найден');
        }
        
        try {
            // Удаляем старое изображение если есть
            $old_thumbnail_id = get_post_thumbnail_id($post_id);
            if ($old_thumbnail_id) {
                // Удаляем attachment
                wp_delete_attachment($old_thumbnail_id, true);
                $this->log('info', "Deleted old image for post {$post_id}");
            }
            
            // Генерируем новое изображение
            $result = $this->generate_image_for_post($post_id);
            
                if ($result) {
                    // Получаем информацию о новом изображении
                    $new_thumbnail_id = get_post_thumbnail_id($post_id);
                    $image_url = wp_get_attachment_image_url($new_thumbnail_id, 'full');
                    $thumbnail_url = wp_get_attachment_image_url($new_thumbnail_id, 'thumbnail');
                    
                    // Выполняем диагностику
                    $diagnostics = $this->perform_image_diagnostics($post_id, $new_thumbnail_id);
                    
                    $this->log('info', "Successfully regenerated image for post {$post_id}");
                    
                    wp_send_json_success([
                        'message' => 'Изображение успешно регенерировано',
                        'post_id' => $post_id,
                        'image_url' => $image_url,
                        'thumbnail_url' => $thumbnail_url,
                        'attachment_id' => $new_thumbnail_id,
                        'diagnostics' => $diagnostics
                    ]);
            } else {
                wp_send_json_error('Ошибка генерации изображения');
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Error regenerating image: ' . $e->getMessage());
            wp_send_json_error('Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Выполнение диагностики изображения
     */
    public function perform_image_diagnostics($post_id, $attachment_id) {
        $diagnostics = [];
        
        // 1. Проверка медиатеки
        $attachment = get_post($attachment_id);
        $diagnostics['media_library'] = [
            'status' => $attachment ? 'success' : 'error',
            'message' => $attachment ? 'Изображение найдено в медиатеке' : 'Изображение не найдено в медиатеке',
            'file_url' => $attachment ? wp_get_attachment_url($attachment_id) : null
        ];
        
        // 2. Проверка статьи
        $featured_image_id = get_post_thumbnail_id($post_id);
        $diagnostics['article'] = [
            'status' => ($featured_image_id == $attachment_id) ? 'success' : 'error',
            'message' => ($featured_image_id == $attachment_id) ? 'Изображение установлено как главное в статье' : 'Изображение не установлено в статье',
            'image_url' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'full') : null
        ];
        
        // 3. Проверка миниатюры
        $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        $diagnostics['thumbnail'] = [
            'status' => !empty($thumbnail_url) ? 'success' : 'error',
            'message' => !empty($thumbnail_url) ? 'Миниатюра создана успешно' : 'Миниатюра не создана',
            'thumbnail_url' => $thumbnail_url
        ];
        
        // 4. Проверка файловой системы
        $upload_dir = wp_upload_dir();
        $file_path = get_attached_file($attachment_id);
        $file_path = str_replace('//', '/', $file_path); // Исправляем двойные слеши
        
        // Дополнительная проверка - если файл не найден, пробуем альтернативные пути
        if (!file_exists($file_path)) {
            $attachment_url = wp_get_attachment_url($attachment_id);
            if ($attachment_url) {
                $parsed_url = parse_url($attachment_url);
                $alternative_path = ABSPATH . ltrim($parsed_url['path'], '/');
                if (file_exists($alternative_path)) {
                    $file_path = $alternative_path;
                }
            }
        }
        
        $diagnostics['file_system'] = [
            'status' => file_exists($file_path) ? 'success' : 'error',
            'message' => file_exists($file_path) ? 'Файл существует на сервере' : 'Файл не найден на сервере',
            'file_path' => $file_path
        ];
        
        return $diagnostics;
    }

    /**
     * Рендеринг статуса диагностики для отображения в таблице
     */
    public function render_diagnostic_status($diagnostic) {
        $status_class = $diagnostic['status'] === 'success' ? 'abp-diagnostic-success' : 'abp-diagnostic-error';
        $icon = $diagnostic['status'] === 'success' ? 'dashicons-yes' : 'dashicons-no';
        $color = $diagnostic['status'] === 'success' ? '#28a745' : '#dc3545';
        
        return sprintf(
            '<div class="abp-diagnostic-status %s" title="%s">
                <span class="dashicons %s" style="color: %s;"></span>
            </div>',
            $status_class,
            esc_attr($diagnostic['message']),
            $icon,
            $color
        );
    }

    /**
     * Получение списка всех статей с информацией об изображениях
     */
    public function get_all_posts_with_images($page = 1, $per_page = 20, $search = '') {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        if (!empty($search)) {
            // Проверяем, является ли поиск числом (ID поста)
            if (is_numeric($search)) {
                $args['p'] = intval($search);
            } else {
                $args['s'] = $search;
            }
        }
        
        $posts = get_posts($args);
        $posts_data = [];
        
        foreach ($posts as $post) {
            $thumbnail_id = get_post_thumbnail_id($post->ID);
            $has_image = !empty($thumbnail_id);
            
            // Получаем диагностику для статьи
            $diagnostics = $has_image ? $this->perform_image_diagnostics($post->ID, $thumbnail_id) : null;
            
            $posts_data[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'date' => $post->post_date,
                'url' => get_permalink($post->ID),
                'edit_url' => get_edit_post_link($post->ID),
                'has_image' => $has_image,
                'thumbnail_id' => $thumbnail_id,
                'thumbnail_url' => $has_image ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '',
                'image_url' => $has_image ? wp_get_attachment_image_url($thumbnail_id, 'full') : '',
                'first_letter' => get_post_meta($post->ID, 'abp_first_letter', true),
                'ai_category' => get_post_meta($post->ID, 'abp_ai_category', true),
                'diagnostics' => $diagnostics
            ];
        }
        
        // Получаем общее количество постов
        $total_args = $args;
        $total_args['posts_per_page'] = -1;
        $total_args['fields'] = 'ids';
        $total_posts = count(get_posts($total_args));
        
        // Если поиск по ID, то общее количество всегда 1 или 0
        if (!empty($search) && is_numeric($search)) {
            $total_posts = count($posts);
        }
        
        return [
            'posts' => $posts_data,
            'total' => $total_posts,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total_posts / $per_page)
        ];
    }
}

// Инициализация плагина
new ABP_Image_Generator();
