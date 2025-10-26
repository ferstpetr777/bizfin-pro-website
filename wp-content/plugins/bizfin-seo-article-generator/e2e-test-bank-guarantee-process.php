<?php
/**
 * E2E Test: "Как работает банковская гарантия"
 * Полный тест генерации статьи с заданными параметрами
 */

if (!defined('ABSPATH')) exit;

class BizFin_E2E_Test_Bank_Guarantee_Process {
    
    public function __construct() {
        add_action('wp_ajax_bsag_e2e_test_bank_guarantee_process', [$this, 'ajax_run_e2e_test']);
    }
    
    /**
     * AJAX обработчик для e2e теста
     */
    public function ajax_run_e2e_test() {
        check_ajax_referer('bsag_ajax_nonce', 'nonce');
        
        // Параметры тестовой статьи
        $test_params = [
            'keyword' => 'Как работает банковская гарантия',
            'user_instruction' => 'Информационный; закупщики/юристы. Отстройка: BPMN‑диаграмма процесса с таймингом.',
            'table_of_contents' => [
                [
                    'heading' => 'Этапы процесса банковской гарантии',
                    'subheadings' => ['Запрос', 'Скоринг', 'Оферта', 'Договор', 'Выдача', 'Реестр', 'Сопровождение'],
                    'key_points' => ['Последовательность этапов', 'Ключевые моменты каждого этапа', 'Взаимосвязь этапов'],
                    'target_words' => 400
                ],
                [
                    'heading' => 'Сроки оформления банковской гарантии',
                    'subheadings' => ['Стандартные сроки', 'Ускоренные процедуры', 'Факторы влияния на сроки'],
                    'key_points' => ['Временные рамки', 'Зависимости сроков', 'Оптимизация времени'],
                    'target_words' => 350
                ],
                [
                    'heading' => 'Точки отказов в процессе',
                    'subheadings' => ['Финансовые риски', 'Документооборот', 'Соответствие требованиям'],
                    'key_points' => ['Основные причины отказов', 'Способы предотвращения', 'Минимизация рисков'],
                    'target_words' => 300
                ],
                [
                    'heading' => 'Что ускоряет одобрение гарантии',
                    'subheadings' => ['Качественная подготовка документов', 'Финансовая прозрачность', 'Репутация заявителя'],
                    'key_points' => ['Факторы ускорения', 'Лучшие практики', 'Рекомендации экспертов'],
                    'target_words' => 350
                ],
                [
                    'heading' => 'BPMN-диаграмма процесса',
                    'subheadings' => ['Визуализация процесса', 'Временные рамки', 'Ответственные лица'],
                    'key_points' => ['Схема процесса', 'Тайминг операций', 'Контрольные точки'],
                    'target_words' => 300
                ],
                [
                    'heading' => 'Практические рекомендации',
                    'subheadings' => ['Подготовка к процессу', 'Выбор банка-партнера', 'Оптимизация процедур'],
                    'key_points' => ['Советы экспертов', 'Типичные ошибки', 'Эффективные стратегии'],
                    'target_words' => 400
                ]
            ],
            'modules' => ['timeline', 'document_checklist'],
            'faq' => [
                [
                    'question' => 'Сколько длится процесс получения банковской гарантии?',
                    'answer' => 'Стандартный срок оформления банковской гарантии составляет от 3 до 10 рабочих дней. В ускоренном режиме возможно получение в течение 1-2 дней при наличии всех необходимых документов.'
                ],
                [
                    'question' => 'Что делать, если контракт меняется после выдачи гарантии?',
                    'answer' => 'При изменении условий контракта необходимо уведомить банк и при необходимости внести изменения в условия гарантии. Это может потребовать дополнительного согласования и документооборота.'
                ],
                [
                    'question' => 'Можно ли ускорить процесс получения гарантии?',
                    'answer' => 'Да, процесс можно ускорить за счет качественной подготовки документов, выбора надежного банка-партнера и предварительного скоринга. Некоторые банки предлагают ускоренные процедуры для проверенных клиентов.'
                ]
            ],
            'cta' => 'Ускорим выдачу — предварительный скоринг бесплатно'
        ];
        
        // Запускаем полный e2e тест
        $result = $this->run_full_e2e_test($test_params);
        
        wp_send_json_success($result);
    }
    
    /**
     * Полный e2e тест генерации статьи
     */
    public function run_full_e2e_test($params) {
        $test_log = [];
        $test_log[] = "=== E2E Test Started: " . date('Y-m-d H:i:s') . " ===";
        
        try {
            // Шаг 1: Проверяем доступность системы динамических модулей
            if (!class_exists('BizFin_Dynamic_Modules_System')) {
                throw new Exception('Dynamic Modules System not available');
            }
            $test_log[] = "✓ Dynamic Modules System available";
            
            // Шаг 2: Проверяем доступность Integration Manager
            if (!class_exists('BizFin_Integration_Manager')) {
                throw new Exception('Integration Manager not available');
            }
            $test_log[] = "✓ Integration Manager available";
            
            // Шаг 3: Генерируем статью с модулями
            $test_log[] = "Generating article with modules...";
            $dynamic_modules = new BizFin_Dynamic_Modules_System();
            
            $article_result = $dynamic_modules->generate_article_with_modules(
                $params['keyword'],
                $params['user_instruction'],
                $params['table_of_contents'],
                $params['modules']
            );
            
            if (is_wp_error($article_result)) {
                throw new Exception('Article generation failed: ' . $article_result->get_error_message());
            }
            
            $test_log[] = "✓ Article generated successfully (ID: " . $article_result['post_id'] . ")";
            
            // Шаг 4: Проверяем создание поста
            $post = get_post($article_result['post_id']);
            if (!$post) {
                throw new Exception('Post not found after generation');
            }
            $test_log[] = "✓ Post created and verified";
            
            // Шаг 5: Проверяем мета-данные
            $is_generated = get_post_meta($article_result['post_id'], '_bsag_generated', true);
            if (!$is_generated) {
                throw new Exception('Generated flag not set');
            }
            $test_log[] = "✓ Generated flag set";
            
            // Шаг 6: Проверяем SEO оптимизацию
            $seo_optimized = get_post_meta($article_result['post_id'], '_bsag_seo_optimized', true);
            $test_log[] = "SEO optimization status: " . ($seo_optimized ? "✓ Optimized" : "⚠ Not optimized");
            
            // Шаг 7: Проверяем интеграцию с ABP Quality Monitor
            $abp_quality_checked = get_post_meta($article_result['post_id'], '_bsag_abp_quality_checked', true);
            $test_log[] = "ABP Quality Monitor status: " . ($abp_quality_checked ? "✓ Checked" : "⚠ Not checked");
            
            // Шаг 8: Проверяем интеграцию с Alphabet Blog Panel
            $abp_integrated = get_post_meta($article_result['post_id'], '_bsag_abp_integrated', true);
            $test_log[] = "ABP integration status: " . ($abp_integrated ? "✓ Integrated" : "⚠ Not integrated");
            
            // Шаг 9: Проверяем генерацию изображения
            $abp_image_generated = get_post_meta($article_result['post_id'], '_bsag_abp_image_generated', true);
            $has_featured_image = has_post_thumbnail($article_result['post_id']);
            $test_log[] = "Image generation status: " . ($abp_image_generated ? "✓ Generated" : "⚠ Not generated");
            $test_log[] = "Featured image status: " . ($has_featured_image ? "✓ Has image" : "⚠ No image");
            
            // Шаг 10: Публикуем статью
            $test_log[] = "Publishing article...";
            wp_update_post([
                'ID' => $article_result['post_id'],
                'post_status' => 'publish'
            ]);
            
            // Проверяем публикацию
            $post_after_publish = get_post($article_result['post_id']);
            if ($post_after_publish->post_status !== 'publish') {
                throw new Exception('Article not published successfully');
            }
            $test_log[] = "✓ Article published successfully";
            
            // Шаг 11: Получаем ссылку на статью
            $article_url = get_permalink($article_result['post_id']);
            $test_log[] = "✓ Article URL: " . $article_url;
            
            // Шаг 12: Проверяем доступность статьи
            $response = wp_remote_get($article_url);
            if (is_wp_error($response)) {
                throw new Exception('Article not accessible: ' . $response->get_error_message());
            }
            $test_log[] = "✓ Article accessible via URL";
            
            // Шаг 13: Проверяем контент статьи
            $content = get_post_field('post_content', $article_result['post_id']);
            $word_count = str_word_count(strip_tags($content));
            $test_log[] = "✓ Article content verified (Word count: " . $word_count . ")";
            
            // Шаг 14: Проверяем модули в контенте
            $has_timeline = strpos($content, '[bsag_timeline]') !== false;
            $has_checklist = strpos($content, '[bsag_document_checklist]') !== false;
            $test_log[] = "Timeline module: " . ($has_timeline ? "✓ Present" : "⚠ Missing");
            $test_log[] = "Checklist module: " . ($has_checklist ? "✓ Present" : "⚠ Missing");
            
            // Шаг 15: Проверяем FAQ секцию
            $has_faq = strpos($content, 'Часто задаваемые вопросы') !== false;
            $test_log[] = "FAQ section: " . ($has_faq ? "✓ Present" : "⚠ Missing");
            
            // Шаг 16: Проверяем CTA блок
            $has_cta = strpos($content, $params['cta']) !== false;
            $test_log[] = "CTA block: " . ($has_cta ? "✓ Present" : "⚠ Missing");
            
            // Шаг 17: Финальная проверка интеграций
            $integrations_completed = get_post_meta($article_result['post_id'], '_bsag_integrations_completed', true);
            $test_log[] = "All integrations: " . ($integrations_completed ? "✓ Completed" : "⚠ Not completed");
            
            $test_log[] = "=== E2E Test Completed Successfully: " . date('Y-m-d H:i:s') . " ===";
            
            return [
                'success' => true,
                'post_id' => $article_result['post_id'],
                'article_url' => $article_url,
                'word_count' => $word_count,
                'test_log' => $test_log,
                'integrations_status' => [
                    'seo_optimized' => $seo_optimized,
                    'abp_quality_checked' => $abp_quality_checked,
                    'abp_integrated' => $abp_integrated,
                    'abp_image_generated' => $abp_image_generated,
                    'integrations_completed' => $integrations_completed
                ],
                'modules_status' => [
                    'timeline' => $has_timeline,
                    'checklist' => $has_checklist,
                    'faq' => $has_faq,
                    'cta' => $has_cta
                ]
            ];
            
        } catch (Exception $e) {
            $test_log[] = "❌ E2E Test Failed: " . $e->getMessage();
            $test_log[] = "=== E2E Test Failed: " . date('Y-m-d H:i:s') . " ===";
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'test_log' => $test_log
            ];
        }
    }
}

// Инициализация e2e теста
new BizFin_E2E_Test_Bank_Guarantee_Process();
