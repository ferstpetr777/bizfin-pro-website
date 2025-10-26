<?php
/**
 * Plugin Name: BizFin SEO Article Generator
 * Description: Универсальный плагин для генерации SEO-оптимизированных статей по банковским гарантиям с интеграцией ИИ-агента
 * Version: 1.0.0
 * Author: BizFin Pro Team
 * Text Domain: bizfin-seo-article-generator
 */

if (!defined('ABSPATH')) exit;

class BizFin_SEO_Article_Generator {
    
    const PLUGIN_VERSION = '1.0.0';
    const PLUGIN_SLUG = 'bizfin-seo-article-generator';
    const NONCE_ACTION = 'bsag_ajax_nonce';
    
    private static $instance = null;
    
    // Матрица SEO-параметров для банковских гарантий (на основе ALwrity архитектуры)
    private $seo_matrix = [
        'keywords' => [
            'что такое банковская гарантия' => [
                'intent' => 'informational',
                'structure' => 'educational',
                'target_audience' => 'beginners',
                'word_count' => 2000,
                'cta_type' => 'calculator',
                'persona_context' => [
                    'archetype' => 'Expert Educator',
                    'core_belief' => 'Знания должны быть доступными',
                    'default_tone' => 'Профессиональный, но понятный',
                    'sentence_length' => 18,
                    'go_to_words' => ['гарантия', 'банк', 'документы', 'процесс', 'требования'],
                    'avoid_words' => ['сложно', 'трудно', 'проблематично']
                ],
                'quality_gates' => [
                    'content_uniqueness' => true,
                    'factual_accuracy' => 0.9,
                    'professional_tone' => 0.85,
                    'industry_relevance' => 0.95,
                    'seo_optimization' => 0.9
                ],
                'prompt_chaining' => [
                    'phase_1' => 'data_analysis',
                    'phase_2' => 'structure_generation', 
                    'phase_3' => 'content_creation',
                    'phase_4' => 'optimization_validation'
                ]
            ],
            'как работает банковская гарантия' => [
                'intent' => 'informational',
                'structure' => 'educational',
                'target_audience' => 'professionals',
                'word_count' => 2500,
                'cta_type' => 'calculator',
                'persona_context' => [
                    'archetype' => 'Expert Educator',
                    'core_belief' => 'Знания должны быть доступными',
                    'default_tone' => 'Профессиональный, но понятный',
                    'sentence_length' => 18,
                    'go_to_words' => ['процесс', 'этапы', 'сроки', 'документы', 'требования'],
                    'avoid_words' => ['сложно', 'трудно', 'проблематично']
                ],
                'global_tone_style' => [
                    'target_audience' => 'закупщики, юристы, специалисты по тендерам',
                    'tone_formula' => [
                        'opening_value' => 'Понимание механизма банковской гарантии поможет вам оптимизировать процесс получения и использования',
                        'simple_explanation' => 'Это пошаговый процесс от подачи заявки до исполнения обязательств',
                        'practical_minimum' => 'Знание этапов поможет избежать ошибок и ускорить получение гарантии',
                        'practical_example' => 'Рассмотрим реальный кейс: компания получила гарантию за 5 дней вместо стандартных 10',
                        'expert_terms' => 'Скоринг (оценка рисков), принципал (заявитель), бенефициар (получатель), гарант (банк)',
                        'reader_address' => 'ваш процесс, ваши документы, для вашего бизнеса',
                        'friendly_professional' => 'Профессиональное объяснение без излишней сложности',
                        'smooth_transition' => 'Теперь детально разберём каждый этап процесса'
                    ],
                    'language_guidelines' => [
                        'readability' => 'Чёткая структура, логические переходы между этапами',
                        'confidence_note' => 'Уверенность в знании процесса и его нюансов',
                        'focus_on_reader' => 'Фокус на практическом применении знаний',
                        'active_voice' => 'Активные конструкции, чёткие инструкции',
                        'concrete_examples' => 'Конкретные сроки, документы, требования',
                        'emotional_tone' => 'Показ важности понимания процесса для успеха',
                        'avoid_clichés' => 'Избегаем абстрактных формулировок, фокус на конкретике'
                    ]
                ],
                'introduction_template' => [
                    'structure' => [
                        'seo_title' => 'Как работает банковская гарантия: пошаговый процесс для специалистов',
                        'brief_description' => 'Банковская гарантия — это многоэтапный процесс от подачи заявки до исполнения обязательств. Понимание механизма поможет оптимизировать сроки и избежать ошибок.',
                        'content_announcement' => 'Разберём все этапы: от подготовки документов до исполнения гарантии, сроки, требования банков и точки возможных сбоев.',
                        'table_of_contents' => 'Содержание: этапы процесса, сроки на каждом этапе, требования банков, точки отказов, факторы ускорения, практические рекомендации',
                        'main_definition' => 'Процесс банковской гарантии включает подачу заявки, скоринг банка, оформление договора, выдачу гарантии и её регистрацию в реестре.',
                        'life_example' => 'Например, строительная компания подаёт заявку на гарантию исполнения контракта. Банк анализирует финансовое состояние за 3 дня, оформляет договор за 2 дня, выдаёт гарантию и регистрирует в реестре закупок.',
                        'legal_context' => 'Процесс регламентируется Гражданским кодексом РФ, банковским законодательством и требованиями реестра банковских гарантий.',
                        'logical_transition' => 'Начнём с первого этапа — подготовки и подачи заявки в банк.'
                    ],
                    'editorial_rules' => [
                        'tone' => 'Эксперт-практик, знающий все нюансы процесса',
                        'volume' => '450–500 слов для детального введения',
                        'text_structure' => 'Чёткая структура по этапам, временные рамки, требования',
                        'seo_tools' => 'Ключевое слово в H1, LSI-синонимы процесса',
                        'html_markup' => '<section class="intro">, <div class="timeline">, <nav class="toc">',
                        'formatting' => 'Выделение этапов, сроков, требований',
                        'visual_blocks' => 'Временная шкала процесса, чек-лист документов'
                    ]
                ],
                'quality_gates' => [
                    'content_uniqueness' => true,
                    'factual_accuracy' => 0.9,
                    'professional_tone' => 0.85,
                    'industry_relevance' => 0.95,
                    'seo_optimization' => 0.9
                ],
                'prompt_chaining' => [
                    'phase_1' => 'data_analysis',
                    'phase_2' => 'structure_generation', 
                    'phase_3' => 'content_creation',
                    'phase_4' => 'optimization_validation'
                ]
            ],
            'виды банковских гарантий' => [
                'intent' => 'informational',
                'structure' => 'educational',
                'target_audience' => 'project_managers',
                'word_count' => 2500,
                'cta_type' => 'form',
        'persona_context' => [
            'archetype' => 'Expert Consultant',
            'core_belief' => 'Правильный выбор гарантии определяет успех проекта',
            'default_tone' => 'Профессиональный, экспертный',
            'sentence_length' => 20,
            'go_to_words' => ['виды', 'гарантии', 'условия', 'риски', 'выбор'],
            'avoid_words' => ['сложно', 'трудно', 'проблематично']
        ],
        'global_tone_style' => [
            'target_audience' => 'руководители проектов, бизнес-люди',
            'tone_formula' => [
                'opening_value' => '[Тема] помогает вам … / … упрощает … и пригодится …',
                'simple_explanation' => 'Опиши, что это такое одним-двумя предложениями, простым языком',
                'practical_minimum' => 'Добавь короткий минимум — зачем нужна эта тема/инструмент (выгода, проблема, решение)',
                'practical_example' => 'Включи практический пример или ситуацию, относящуюся к читателю-аудитории',
                'expert_terms' => 'Используй экспертные термины, но сразу давай пояснение на простом языке',
                'reader_address' => 'Обратись к читателю: «вам», «ваш бизнес», «для вашего проекта»',
                'friendly_professional' => 'Сохрани дружелюбный, неформальный оттенок, но без излишней фамильярности',
                'smooth_transition' => 'Заверши переходом к следующей части, мотивируя читать дальше'
            ],
            'language_guidelines' => [
                'readability' => 'Легко читаемый, дружелюбный, чуть разговорный, но без юмора до шуток',
                'confidence_note' => 'Лёгкая нотка уверенности',
                'focus_on_reader' => 'Акцент на «мы» или «мы рассказываем», но в пассиве — при этом фокус на «вы»',
                'active_voice' => 'Активный залог, прямое обращение',
                'concrete_examples' => 'Не перегружай абзакциями — сразу переходи к делу',
                'emotional_tone' => 'Показать, что тема важна, что есть выгода, что можно избежать боли',
                'avoid_clichés' => 'Избегай канцелярита, длинных сводных предложений, многословия'
            ]
        ],
        'introduction_template' => [
            'structure' => [
                'seo_title' => '[Ключевое слово]: [польза, цель, решение или вопрос читателя]',
                'brief_description' => 'Краткое вводное описание темы (1 абзац, 2–3 предложения)',
                'content_announcement' => 'Анонс содержания (структурное обещание)',
                'table_of_contents' => 'Содержание / мини-оглавление (опционально)',
                'main_definition' => 'Основное определение темы (базовая суть)',
                'life_example' => 'Пример из жизни или практическая ситуация',
                'legal_context' => 'Уточнение или правовой контекст (по необходимости)',
                'logical_transition' => 'Логическая развязка — переход к основному разделу'
            ],
            'editorial_rules' => [
                'tone' => 'профессиональный, но дружелюбный — «объясняем, а не читаем лекцию»',
                'volume' => '300–500 слов (оптимально для SEO и удержания внимания)',
                'text_structure' => 'короткие абзацы (2–3 предложения), подзаголовки каждые 3–5 абзацев',
                'seo_tools' => 'ключевое слово в заголовке <h1> и в первом абзаце; LSI-синонимы — в теле',
                'html_markup' => 'оборачивать ввод в <section class="intro">, подзаголовки — в <h2>, примеры — в <blockquote> или <div class="example">',
                'formatting' => 'выделять ключевые слова и роли (Принципал, Бенефициар, Гарант) полужирным',
                'visual_blocks' => 'добавлять «Содержание» в виде <ul> или <details> для UX и SEO'
            ]
        ],
                'quality_gates' => [
                    'content_uniqueness' => true,
                    'factual_accuracy' => 0.95,
                    'professional_tone' => 0.9,
                    'industry_relevance' => 0.95,
                    'seo_optimization' => 0.9
                ],
                'prompt_chaining' => [
                    'phase_1' => 'data_analysis',
                    'phase_2' => 'structure_generation', 
                    'phase_3' => 'content_creation',
                    'phase_4' => 'optimization_validation'
                ]
            ],
            'банковская гарантия для тендеров' => [
                'intent' => 'commercial',
                'structure' => 'service',
                'target_audience' => 'contractors',
                'word_count' => 2500,
                'cta_type' => 'form'
            ],
            'стоимость банковской гарантии' => [
                'intent' => 'commercial',
                'structure' => 'pricing',
                'target_audience' => 'business_owners',
                'word_count' => 1800,
                'cta_type' => 'calculator'
            ],
            'как получить банковскую гарантию' => [
                'intent' => 'informational',
                'structure' => 'how_to',
                'target_audience' => 'beginners',
                'word_count' => 2200,
                'cta_type' => 'guide'
            ],
            'банковская гарантия по 44 фз' => [
                'intent' => 'informational',
                'structure' => 'legal',
                'target_audience' => 'specialists',
                'word_count' => 2800,
                'cta_type' => 'consultation'
            ],
            'банковская гарантия по 223 фз' => [
                'intent' => 'informational',
                'structure' => 'legal',
                'target_audience' => 'specialists',
                'word_count' => 2600,
                'cta_type' => 'consultation'
            ],
            'отзыв банковской гарантии' => [
                'intent' => 'informational',
                'structure' => 'process',
                'target_audience' => 'contractors',
                'word_count' => 2000,
                'cta_type' => 'form'
            ],
            'безотзывная банковская гарантия' => [
                'intent' => 'informational',
                'structure' => 'comparison',
                'target_audience' => 'business_owners',
                'word_count' => 2100,
                'cta_type' => 'calculator'
            ]
        ],
        
        'article_structures' => [
            'educational' => [
                'h1' => 'Определение и основы',
                'h2_sections' => [
                    'Что такое банковская гарантия простыми словами',
                    'Участники процесса и их роли',
                    'Как работает механизм гарантии',
                    'Когда без гарантии не обойтись',
                    'Что проверяет банк перед выдачей',
                    'Стоимость и факторы ценообразования',
                    'Ошибки новичков и как их избежать',
                    'Практический пример: простой контракт',
                    'Итоги и чек-лист'
                ]
            ],
            'service' => [
                'h1' => 'Услуга и решение',
                'h2_sections' => [
                    'Что включает услуга',
                    'Преимущества нашего подхода',
                    'Этапы получения гарантии',
                    'Документы и требования',
                    'Сроки и стоимость',
                    'Отличия от конкурентов',
                    'Кейсы и результаты',
                    'Как начать сотрудничество'
                ]
            ],
            'pricing' => [
                'h1' => 'Стоимость и расчеты',
                'h2_sections' => [
                    'Факторы влияющие на стоимость',
                    'Расчет стоимости онлайн',
                    'Сравнение тарифов банков',
                    'Как сэкономить на гарантии',
                    'Скрытые комиссии и доплаты',
                    'Стоимость по регионам',
                    'Специальные предложения',
                    'Калькулятор стоимости'
                ]
            ],
            'how_to' => [
                'h1' => 'Пошаговая инструкция',
                'h2_sections' => [
                    'Подготовка к получению гарантии',
                    'Выбор банка и тарифа',
                    'Сбор необходимых документов',
                    'Подача заявления в банк',
                    'Рассмотрение заявки банком',
                    'Подписание договора',
                    'Получение гарантии',
                    'Контроль исполнения обязательств'
                ]
            ],
            'legal' => [
                'h1' => 'Правовые аспекты',
                'h2_sections' => [
                    'Нормативная база',
                    'Требования законодательства',
                    'Изменения в 2024-2025 году',
                    'Судебная практика',
                    'Частые нарушения и штрафы',
                    'Права и обязанности сторон',
                    'Спорные ситуации',
                    'Консультация юриста'
                ]
            ],
            'process' => [
                'h1' => 'Процедуры и процессы',
                'h2_sections' => [
                    'Общий алгоритм действий',
                    'Временные рамки процесса',
                    'Ответственные лица',
                    'Документооборот',
                    'Контрольные точки',
                    'Возможные проблемы',
                    'Альтернативные решения',
                    'Обратная связь и поддержка'
                ]
            ],
            'comparison' => [
                'h1' => 'Сравнение и выбор',
                'h2_sections' => [
                    'Виды банковских гарантий',
                    'Сравнительная таблица',
                    'Плюсы и минусы каждого типа',
                    'Критерии выбора',
                    'Рекомендации экспертов',
                    'Типичные ошибки при выборе',
                    'Практические примеры',
                    'Консультация по выбору'
                ]
            ]
        ],
        
        'seo_requirements' => [
            'title_length' => 60,
            'meta_description_length' => 160,
            'h1_count' => 1,
            'h2_count' => [8, 12],
            'word_count_min' => 1500,
            'word_count_max' => 3000,
            'keyword_density' => [1, 3],
            'internal_links' => [3, 7],
            'external_links' => [1, 3],
            'cta_blocks' => [2, 3],
            'images_min' => 3,
            'images_with_alt' => true,
            'schema_markup' => true,
            'breadcrumbs' => true,
            'faq_section' => true
        ],
        
        // БЕЗУСЛОВНЫЕ ПРАВИЛА ПО УМОЛЧАНИЮ (обязательны для всех статей)
        'mandatory_intro_blocks' => [
            'simple_definition' => [
                'required' => true,
                'template' => '[Термин] — это [простое объяснение сути]. [Дополнительное пояснение о комиссии/условиях].',
                'example' => 'Банковская гарантия — это обещание банка выплатить деньги третьему лицу, если клиент не выполнит взятое обязательство. За выдачу гарантии клиент платит банку комиссию.'
            ],
            'sympathetic_example' => [
                'required' => true,
                'template' => 'Например, [Имя] хочет [действие/покупку]. На это нужно [сумма], но денег пока нет — [объяснение ситуации]. Поэтому [Имя] нужна [потребность]. Чтобы [условие], [Имя] получает у банка гарантию. По условиям гарантии банк [действие банка], если [условие неисполнения].',
                'example' => 'Например, Юлия хочет купить две конвекционные печи для своей булочной. На это нужно 1,2 млн рублей, но денег пока нет — владелица рассчитывает накопить их спустя полгода работы пекарни. Поэтому Юлии нужна отсрочка оплаты от поставщика. Чтобы он согласился продать печи, Юлия получает у банка гарантию. По условиям гарантии банк оплатит долг Юлии, если она не перечислит деньги за обе печи через полгода работы булочной.'
            ]
        ],
        
        'mandatory_toc_requirements' => [
            'clickable_links' => true,
            'anchor_links' => true,
            'smooth_scroll' => true,
            'template' => '<nav class="toc"><strong>Содержание:</strong><ul><li><a href="#anchor-id">Название раздела</a></li></ul></nav>',
            'anchor_format' => 'kebab-case', // definition, how-it-works, when-needed
            'validation' => 'Все ссылки в оглавлении должны вести на соответствующие разделы статьи'
        ],
        
        // БЕЗУСЛОВНЫЕ ПРАВИЛА ВНУТРЕННЕЙ ПЕРЕЛИНКОВКИ
        'mandatory_internal_linking' => [
            'enabled' => true,
            'registry_source' => 'articles_registry.json',
            'rules' => [
                'no_broken_links' => [
                    'required' => true,
                    'validation' => 'Все внутренние ссылки должны вести на существующие страницы/статьи'
                ],
                'contextual_relevance' => [
                    'required' => true,
                    'validation' => 'Ссылки должны быть тематически релевантными контексту'
                ],
                'minimum_internal_links' => [
                    'required' => true,
                    'count' => 3,
                    'validation' => 'Минимум 3 внутренние ссылки на существующие статьи/страницы'
                ],
                'maximum_internal_links' => [
                    'required' => true,
                    'count' => 7,
                    'validation' => 'Максимум 7 внутренних ссылок для читабельности'
                ]
            ],
            'link_types' => [
                'articles' => [
                    'priority' => 'high',
                    'description' => 'Ссылки на тематически связанные статьи блога'
                ],
                'services' => [
                    'priority' => 'high', 
                    'description' => 'Ссылки на сервисы и калькуляторы (например, калькулятор банковских гарантий)'
                ],
                'company_pages' => [
                    'priority' => 'medium',
                    'description' => 'Ссылки на страницы компании (контакты, услуги)'
                ]
            ],
            'auto_linking_keywords' => [
                'bank-guarantees' => [
                    'keywords' => ['банковская гарантия', 'гарантия', 'банк', 'финансы'],
                    'target_articles' => ['2830', '2803', '2520', '2067'],
                    'target_services' => ['14'], // калькулятор
                    'target_pages' => ['596', '128'] // услуги
                ],
                'banking' => [
                    'keywords' => ['кредит', 'вклад', 'карта', 'ипотека'],
                    'target_articles' => ['2438', '2488', '2497', '2434'],
                    'target_services' => [],
                    'target_pages' => []
                ],
                'business' => [
                    'keywords' => ['ИП', 'бизнес', 'предприниматель', 'финансы'],
                    'target_articles' => ['2455', '2425'],
                    'target_services' => [],
                    'target_pages' => []
                ]
            ]
        ],
        
        // БЕЗУСЛОВНЫЕ ПРАВИЛА РАЗМЕЩЕНИЯ ИЗОБРАЖЕНИЙ
        'mandatory_image_placement' => [
            'enabled' => true,
            'placement_rules' => [
                'position' => 'after_toc_before_content',
                'description' => 'Изображение размещается после блока оглавления, перед основным текстом статьи',
                'required' => true,
                'validation' => 'Каждая статья должна содержать изображение в указанной позиции'
            ],
            'image_requirements' => [
                'featured_image' => [
                    'required' => true,
                    'source' => 'abp_image_generator',
                    'description' => 'Изображение генерируется через ABP Image Generator и устанавливается как featured image',
                    'preserve_featured' => true,
                    'note' => 'Featured image НЕ удаляется, а дублируется в контент'
                ],
                'content_image' => [
                    'required' => true,
                    'position' => 'after_toc',
                    'description' => 'Изображение вставляется в контент после оглавления как копия featured image',
                    'source' => 'featured_image_copy'
                ],
                'alt_text' => [
                    'required' => true,
                    'template' => 'Изображение для статьи: [название статьи]',
                    'description' => 'Обязательный alt-текст для изображения'
                ],
                'styling' => [
                    'aspect_ratio' => '16:9',
                    'border_radius' => '12px',
                    'box_shadow' => '0 4px 20px rgba(0, 0, 0, 0.1)',
                    'border' => '1px solid rgba(255, 255, 255, 0.8)',
                    'ios_style' => true,
                    'description' => 'iOS-стиль с белой рамкой, тенью и скругленными углами'
                ]
            ],
            'integration_flow' => [
                'step_1' => 'Статья публикуется → ABP Image Generator автоматически создаёт featured image',
                'step_2' => 'BizFin Integration Manager планирует отложенную интеграцию (10 сек)',
                'step_3' => 'Через тайминг проверяется наличие featured image',
                'step_4' => 'Featured image остается как главное изображение статьи',
                'step_5' => 'Копия featured image вставляется в контент после оглавления',
                'step_6' => 'Добавляется alt-текст и описание',
                'step_7' => 'Применяются iOS-стили (16:9, рамка, тень)',
                'step_8' => 'Результат: 1 featured image + 1 контентное изображение (без дублирования)'
            ],
            'abp_integration' => [
                'preserve_featured_image' => true,
                'no_conflicts' => true,
                'description' => 'Полная совместимость с ABP Image Generator без конфликтов',
                'timing_settings' => [
                    'initial_delay' => 10, // секунд после публикации
                    'retry_delay' => 5,    // секунд между попытками
                    'max_retries' => 6,    // максимум попыток (30 секунд общий таймаут)
                    'description' => 'Настройки тайминга для ожидания создания featured image'
                ]
            ]
        ],
        // СИСТЕМА GUTENBERG БЛОКОВ ДЛЯ ТОЧЕЧНЫХ ИЗМЕНЕНИЙ
        'gutenberg_block_system' => [
            'enabled' => true,
            'block_types' => [
                'intro_section' => [
                    'block_name' => 'bizfin/intro-section',
                    'attributes' => [
                        'simpleDefinition' => 'string',
                        'sympatheticExample' => 'string',
                        'tocContent' => 'string'
                    ],
                    'description' => 'Блок вводной секции с определением, примером и оглавлением'
                ],
                'article_image' => [
                    'block_name' => 'bizfin/article-image',
                    'attributes' => [
                        'imageUrl' => 'string',
                        'altText' => 'string',
                        'caption' => 'string',
                        'position' => 'string'
                    ],
                    'description' => 'Блок изображения с iOS-стилями'
                ],
                'content_section' => [
                    'block_name' => 'bizfin/content-section',
                    'attributes' => [
                        'sectionId' => 'string',
                        'sectionTitle' => 'string',
                        'sectionContent' => 'string',
                        'hasExample' => 'boolean'
                    ],
                    'description' => 'Блок контентной секции с возможностью добавления примеров'
                ]
            ],
            'update_strategy' => [
                'method' => 'block_based',
                'description' => 'Обновление только конкретных блоков вместо полной перезаписи',
                'ajax_endpoints' => [
                    'update_block' => 'bsag_update_block',
                    'remove_duplicates' => 'bsag_remove_duplicate_images'
                ]
            ],
            'duplicate_prevention' => [
                'enabled' => true,
                'auto_cleanup' => true,
                'description' => 'Автоматическое удаление дублирующихся изображений'
            ]
        ],
        // БЕЗУСЛОВНЫЕ ПРАВИЛА АДАПТИВНОЙ ВЕРСТКИ
        'mandatory_responsive_design' => [
            'enabled' => true,
            'requirements' => [
                'mobile_first' => [
                    'required' => true,
                    'description' => 'Верстка должна быть адаптивной с подходом mobile-first',
                    'validation' => 'Статья корректно отображается на всех устройствах'
                ],
                'responsive_breakpoints' => [
                    'required' => true,
                    'breakpoints' => [
                        'mobile' => 'max-width: 768px',
                        'tablet' => 'max-width: 1024px',
                        'desktop' => 'min-width: 1025px'
                    ],
                    'description' => 'Обязательные точки останова для адаптивности'
                ],
                'flexible_layout' => [
                    'required' => true,
                    'description' => 'Использование flexbox/grid для гибкой компоновки',
                    'validation' => 'Контент адаптируется под ширину экрана'
                ],
                'readable_typography' => [
                    'required' => true,
                    'description' => 'Читаемая типографика на всех устройствах',
                    'font_sizes' => [
                        'mobile' => '16px base, 1.4rem headings',
                        'tablet' => '18px base, 1.6rem headings',
                        'desktop' => '20px base, 2rem+ headings'
                    ]
                ],
                'reasonable_width_content' => [
                    'required' => true,
                    'description' => 'Контент должен иметь разумную ширину с отступами для читаемости',
                    'css_override' => [
                        'container_max_width' => '1200px',
                        'content_max_width' => '100%',
                        'theme_override' => 'Установка разумной максимальной ширины с центрированием'
                    ],
                    'validation' => 'Статья отображается с разумными отступами и читаемой шириной'
                ],
                'normalized_headings' => [
                    'required' => true,
                    'description' => 'Заголовки должны соответствовать стилям темы',
                    'heading_sizes' => [
                        'h1' => '2.5rem (desktop), 2rem (mobile)',
                        'h2' => '2rem (desktop), 1.75rem (mobile)',
                        'h3' => '1.5rem',
                        'h4' => '1.25rem'
                    ],
                    'validation' => 'Заголовки имеют разумные размеры, соответствующие стилям темы'
                ],
                'touch_friendly' => [
                    'required' => true,
                    'description' => 'Элементы управления удобны для касания',
                    'min_touch_target' => '44px',
                    'validation' => 'Кнопки и ссылки достаточно большие для касания'
                ]
            ],
            'css_requirements' => [
                'viewport_meta' => '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
                'responsive_images' => 'img { max-width: 100%; height: auto; }',
                'flexible_containers' => '.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }',
                'media_queries' => '@media (max-width: 768px) { /* mobile styles */ }',
                'description' => 'Обязательные CSS правила для адаптивности'
            ],
            'validation_rules' => [
                'no_horizontal_scroll' => 'На мобильных устройствах не должно быть горизонтальной прокрутки',
                'readable_text' => 'Текст должен быть читаемым без масштабирования',
                'accessible_navigation' => 'Навигация должна быть доступна на всех устройствах',
                'fast_loading' => 'Страница должна быстро загружаться на мобильных устройствах'
            ]
        ],
        
        // MANDATORY LENGTH RULES
        'mandatory_length' => [
            'enabled' => true,
            'min_words' => 2500,
            'hard_gate' => true,
            'description' => 'Гарантированный минимум 2500 слов для всех статей - безусловное правило',
            'by_sections' => [
                'intro' => [
                    'min_words' => 350,
                    'max_words' => 500,
                    'description' => 'Вводная секция с определением и примером'
                ],
                'h2_sections' => [
                    'min_words_per_section' => 300,
                    'max_words_per_section' => 450,
                    'auto_multiply' => true,
                    'description' => 'Каждая H2 секция должна содержать минимум 300 слов'
                ],
                'faq' => [
                    'min_words' => 400,
                    'max_words' => 600,
                    'description' => 'FAQ секция с вопросами и ответами'
                ],
                'cta' => [
                    'min_words' => 80,
                    'max_words' => 150,
                    'description' => 'Call-to-action блок'
                ],
                'safety_buffer' => [
                    'percentage' => 5,
                    'description' => 'Буфер 5% на усадку после чистки HTML'
                ]
            ],
            'expansion_settings' => [
                'max_expansion_passes' => 3,
                'expansion_strategies' => [
                    'add_examples' => 'Добавление практических примеров и кейсов',
                    'add_details' => 'Расширение объяснений и детализация процессов',
                    'add_subheadings' => 'Добавление подзаголовков с дополнительным контентом',
                    'add_lists' => 'Создание списков и чек-листов',
                    'add_step_by_step' => 'Пошаговые инструкции и алгоритмы'
                ],
                'quality_control' => [
                    'prevent_water' => true,
                    'max_water_percentage' => 15,
                    'diversity_check' => true,
                    'description' => 'Контроль качества при расширении - избегание "воды"'
                ]
            ],
            'validation_rules' => [
                'count_method' => 'clean_html_strip',
                'exclude_elements' => ['toc', 'navigation', 'captions', 'figures', 'scripts', 'styles'],
                'encoding' => 'utf8_mb_split',
                'description' => 'Метод подсчета чистых слов без HTML и служебных элементов'
            ],
            'automation' => [
                'auto_expansion' => true,
                'background_expansion' => true,
                'cron_expansion' => true,
                'description' => 'Автоматическое доращивание при недоборе слов'
            ],
            'metadata' => [
                'store_word_count' => true,
                'store_expansion_attempts' => true,
                'store_needs_expansion' => true,
                'meta_keys' => [
                    '_bsag_min_words',
                    '_bsag_word_count', 
                    '_bsag_expansion_attempts',
                    '_bsag_needs_expansion'
                ]
            ]
        ]
    ];
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_bsag_generate_article', [$this, 'ajax_generate_article']);
        add_action('wp_ajax_bsag_get_keyword_data', [$this, 'ajax_get_keyword_data']);
        add_action('wp_ajax_bsag_test_generate_article', [$this, 'ajax_test_generate_article']);
        add_action('wp_ajax_bsag_generate_with_modules', [$this, 'ajax_generate_with_modules']);
        add_action('wp_ajax_bsag_publish_article', [$this, 'ajax_publish_article']);
        add_action('wp_ajax_bsag_e2e_test_bank_guarantee_process', [$this, 'ajax_run_e2e_test']);
        add_action('wp_ajax_bsag_update_articles_registry', [$this, 'ajax_update_articles_registry']);
        add_action('wp_ajax_bsag_fix_image_placement', [$this, 'ajax_fix_image_placement']);
        add_action('wp_ajax_bsag_update_block', [$this, 'ajax_update_block']);
        add_action('wp_ajax_bsag_remove_duplicate_images', [$this, 'ajax_remove_duplicate_images']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Обработчик автоматического доращивания статей
        add_action('bsag_auto_expand_article', [$this, 'handle_auto_expand_article'], 10, 1);
        
        // Интеграции теперь обрабатываются через централизованный менеджер
        // Все хуки save_post и bsag_article_generated управляются через Integration Manager
        
        // Подключение дополнительных модулей
        $this->load_additional_modules();
        
        // Подключение загрузчика стилей для статей
        require_once plugin_dir_path(__FILE__) . 'includes/article-styles-loader.php';
        
        // Инициализация генератора тона и стиля
        require_once plugin_dir_path(__FILE__) . 'includes/tone-style-generator.php';
        
        // Инициализация менеджера внутренней перелинковки
        require_once plugin_dir_path(__FILE__) . 'includes/internal-linking-manager.php';
        $this->internal_linking_manager = new BizFin_Internal_Linking_Manager($this);
        
        // Инициализация менеджера размещения изображений
        require_once plugin_dir_path(__FILE__) . 'includes/image-placement-manager.php';
        $this->image_placement_manager = new BizFin_Image_Placement_Manager($this);
        
        // Инициализация менеджера Gutenberg блоков
        require_once plugin_dir_path(__FILE__) . 'includes/gutenberg-block-manager.php';
        $this->gutenberg_block_manager = new BizFin_Gutenberg_Block_Manager($this);
        
        // Инициализация Integration Manager с ссылкой на основной плагин
        require_once plugin_dir_path(__FILE__) . 'includes/integration-manager.php';
        $this->integration_manager = BizFin_Integration_Manager::get_instance($this);
        
        $this->tone_style_generator = new BizFin_Tone_Style_Generator($this);
        
        // Регистрация активации/деактивации
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function init() {
        // Инициализация плагина
        load_plugin_textdomain(self::PLUGIN_SLUG, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Загрузка дополнительных модулей
     */
    private function load_additional_modules() {
        $plugin_dir = plugin_dir_path(__FILE__);
        
        // Загружаем модули если они существуют
        $modules = [
            'includes/integration-manager.php',
            'includes/quality-system.php',
            'includes/prompt-chaining-system.php',
            'includes/ai-agent-integration.php',
            'includes/plugin-integrations.php',
            'includes/dynamic-modules-system.php',
            'includes/modules-interface.php',
            'includes/gutenberg-block-manager.php',
            'test-article-what-is-bank-guarantee.php',
            'e2e-test-bank-guarantee-process.php'
        ];
        
        foreach ($modules as $module) {
            $module_path = $plugin_dir . $module;
            if (file_exists($module_path)) {
                require_once $module_path;
            }
        }
    }
    
    public function activate() {
        // Создание таблиц для хранения данных
        $this->create_tables();
        
        // Создание страницы генератора статей
        $this->create_generator_page();
        
        // Флеш правила перезаписи
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Таблица для хранения сгенерированных статей
        $articles_table = $wpdb->prefix . 'bsag_articles';
        $articles_sql = "CREATE TABLE $articles_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            keyword varchar(255) NOT NULL,
            article_content longtext NOT NULL,
            seo_meta text,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            ai_agent_response text,
            status varchar(20) DEFAULT 'draft',
            quality_score decimal(3,2) DEFAULT 0.00,
            word_count int(11) DEFAULT 0,
            keyword_density decimal(5,2) DEFAULT 0.00,
            PRIMARY KEY (id),
            KEY keyword (keyword),
            KEY generated_at (generated_at),
            KEY status (status),
            KEY quality_score (quality_score)
        ) $charset_collate;";
        
        // Таблица для сессий цепочек промптов
        $sessions_table = $wpdb->prefix . 'bsag_prompt_sessions';
        $sessions_sql = "CREATE TABLE $sessions_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(36) NOT NULL,
            keyword varchar(255) NOT NULL,
            keyword_data text,
            status varchar(20) DEFAULT 'active',
            phase_1_result longtext,
            phase_2_result longtext,
            phase_3_result longtext,
            phase_4_result longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY keyword (keyword),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Таблица для анализа качества
        $quality_table = $wpdb->prefix . 'bsag_quality_analysis';
        $quality_sql = "CREATE TABLE $quality_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            keyword varchar(255) NOT NULL,
            overall_score decimal(3,2) DEFAULT 0.00,
            factual_accuracy decimal(3,2) DEFAULT 0.00,
            professional_tone decimal(3,2) DEFAULT 0.00,
            industry_relevance decimal(3,2) DEFAULT 0.00,
            seo_optimization decimal(3,2) DEFAULT 0.00,
            content_uniqueness decimal(3,2) DEFAULT 0.00,
            readability_score decimal(3,2) DEFAULT 0.00,
            keyword_density decimal(5,2) DEFAULT 0.00,
            recommendations text,
            analysis_timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY keyword (keyword),
            KEY overall_score (overall_score),
            KEY analysis_timestamp (analysis_timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($articles_sql);
        dbDelta($sessions_sql);
        dbDelta($quality_sql);
    }
    
    private function create_generator_page() {
        $page_exists = get_page_by_path('seo-article-generator');
        if (!$page_exists) {
            $page_id = wp_insert_post([
                'post_title'   => 'SEO Генератор Статей',
                'post_content' => '[bsag_generator_interface]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => 'seo-article-generator',
                'post_author'  => 1,
            ]);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('bsag_generator_page_id', $page_id);
            }
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'SEO Генератор Статей',
            'SEO Статьи',
            'manage_options',
            self::PLUGIN_SLUG,
            [$this, 'admin_page'],
            'dashicons-edit-page',
            30
        );
        
        add_submenu_page(
            self::PLUGIN_SLUG,
            'Генератор',
            'Генератор',
            'manage_options',
            self::PLUGIN_SLUG,
            [$this, 'admin_page']
        );
        
        add_submenu_page(
            self::PLUGIN_SLUG,
            'Статистика',
            'Статистика',
            'manage_options',
            self::PLUGIN_SLUG . '-stats',
            [$this, 'stats_page']
        );
        
        add_submenu_page(
            self::PLUGIN_SLUG,
            'Настройки',
            'Настройки',
            'manage_options',
            self::PLUGIN_SLUG . '-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            self::PLUGIN_SLUG,
            'E2E Тестирование',
            'E2E Тесты',
            'manage_options',
            self::PLUGIN_SLUG . '-e2e-tests',
            [$this, 'e2e_tests_page']
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>BizFin SEO Генератор Статей</h1>
            
            <div class="bsag-generator-container">
                <div class="bsag-left-panel">
                    <h2>Матрица ключевых слов</h2>
                    <div class="bsag-keywords-list">
                        <?php foreach ($this->seo_matrix['keywords'] as $keyword => $data): ?>
                            <div class="bsag-keyword-item" data-keyword="<?php echo esc_attr($keyword); ?>">
                                <h3><?php echo esc_html($keyword); ?></h3>
                                <div class="bsag-keyword-meta">
                                    <span class="bsag-intent"><?php echo esc_html($data['intent']); ?></span>
                                    <span class="bsag-structure"><?php echo esc_html($data['structure']); ?></span>
                                    <span class="bsag-audience"><?php echo esc_html($data['target_audience']); ?></span>
                                </div>
                                <button class="bsag-generate-btn" data-keyword="<?php echo esc_attr($keyword); ?>">
                                    Генерировать статью
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="bsag-right-panel">
                    <h2>Интерфейс ИИ-агента</h2>
                    <div class="bsag-ai-interface">
                        <div class="bsag-current-keyword">
                            <strong>Выбранное ключевое слово:</strong>
                            <span id="bsag-selected-keyword">Не выбрано</span>
                        </div>
                        
                        <div class="bsag-ai-conversation">
                            <div id="bsag-conversation-log"></div>
                        </div>
                        
                        <div class="bsag-ai-controls">
                            <button id="bsag-start-generation" class="button button-primary" disabled>
                                Начать генерацию
                            </button>
                            <button id="bsag-stop-generation" class="button" style="display: none;">
                                Остановить
                            </button>
                        </div>
                        
                        <div class="bsag-generation-progress" style="display: none;">
                            <div class="bsag-progress-bar">
                                <div class="bsag-progress-fill"></div>
                            </div>
                            <div class="bsag-progress-text">Генерация статьи...</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bsag-article-preview" style="display: none;">
                <h2>Предварительный просмотр статьи</h2>
                <div id="bsag-article-content"></div>
                <div class="bsag-article-actions">
                    <button id="bsag-publish-article" class="button button-primary">
                        Опубликовать статью
                    </button>
                    <button id="bsag-save-draft" class="button">
                        Сохранить как черновик
                    </button>
                    <button id="bsag-edit-article" class="button">
                        Редактировать
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function stats_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_articles';
        
        // Получаем статистику
        $total_articles = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $published_articles = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'published'");
        $draft_articles = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'draft'");
        
        // Топ ключевых слов
        $top_keywords = $wpdb->get_results("
            SELECT keyword, COUNT(*) as count 
            FROM $table_name 
            GROUP BY keyword 
            ORDER BY count DESC 
            LIMIT 10
        ");
        
        ?>
        <div class="wrap">
            <h1>Статистика генерации статей</h1>
            
            <div class="bsag-stats-grid">
                <div class="bsag-stat-card">
                    <h3>Всего статей</h3>
                    <div class="bsag-stat-number"><?php echo $total_articles; ?></div>
                </div>
                
                <div class="bsag-stat-card">
                    <h3>Опубликовано</h3>
                    <div class="bsag-stat-number"><?php echo $published_articles; ?></div>
                </div>
                
                <div class="bsag-stat-card">
                    <h3>Черновики</h3>
                    <div class="bsag-stat-number"><?php echo $draft_articles; ?></div>
                </div>
            </div>
            
            <div class="bsag-top-keywords">
                <h2>Топ ключевых слов</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Ключевое слово</th>
                            <th>Количество статей</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_keywords as $keyword): ?>
                            <tr>
                                <td><?php echo esc_html($keyword->keyword); ?></td>
                                <td><?php echo $keyword->count; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Настройки SEO Генератора</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('bsag_settings');
                do_settings_sections('bsag_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Интеграция с Yoast SEO</th>
                        <td>
                            <input type="checkbox" name="bsag_yoast_integration" value="1" 
                                   <?php checked(get_option('bsag_yoast_integration', 1)); ?> />
                            <p class="description">Автоматически оптимизировать статьи под критерии Yoast SEO</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Автопубликация</th>
                        <td>
                            <input type="checkbox" name="bsag_auto_publish" value="1" 
                                   <?php checked(get_option('bsag_auto_publish', 0)); ?> />
                            <p class="description">Автоматически публиковать сгенерированные статьи</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Категория по умолчанию</th>
                        <td>
                            <?php
                            wp_dropdown_categories([
                                'name' => 'bsag_default_category',
                                'selected' => get_option('bsag_default_category', 1),
                                'show_option_none' => 'Выберите категорию'
                            ]);
                            ?>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, self::PLUGIN_SLUG) !== false) {
            wp_enqueue_script(
                'bsag-admin-script',
                plugin_dir_url(__FILE__) . 'assets/js/admin.js',
                ['jquery'],
                self::PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'bsag-admin-style',
                plugin_dir_url(__FILE__) . 'assets/css/admin.css',
                [],
                self::PLUGIN_VERSION
            );
            
            wp_localize_script('bsag-admin-script', 'bsagAjax', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(self::NONCE_ACTION),
                'seoMatrix' => $this->seo_matrix
            ]);
        }
        
        // Подключаем стили и скрипты для Gutenberg блоков
        wp_enqueue_style(
            'bsag-ios-image-styles',
            plugin_dir_url(__FILE__) . 'assets/css/ios-image-styles.css',
            [],
            self::PLUGIN_VERSION
        );
        
        // Подключаем стили для разумной ширины контента
        wp_enqueue_style(
            'bsag-reasonable-width-content',
            plugin_dir_url(__FILE__) . 'assets/css/reasonable-width-content.css',
            [],
            self::PLUGIN_VERSION
        );
        
        // Header JavaScript removed - let theme handle header naturally
        
        wp_enqueue_script(
            'bsag-gutenberg-block-manager',
            plugin_dir_url(__FILE__) . 'assets/js/gutenberg-block-manager.js',
            ['jquery', 'wp-blocks', 'wp-element', 'wp-editor'],
            self::PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('bsag-gutenberg-block-manager', 'bsag_ajax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION)
        ]);
    }
    
    public function ajax_get_keyword_data() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        
        if (!isset($this->seo_matrix['keywords'][$keyword])) {
            wp_send_json_error('Ключевое слово не найдено');
        }
        
        $data = $this->seo_matrix['keywords'][$keyword];
        $structure = $this->seo_matrix['article_structures'][$data['structure']];
        
        wp_send_json_success([
            'keyword_data' => $data,
            'article_structure' => $structure,
            'seo_requirements' => $this->seo_matrix['seo_requirements']
        ]);
    }
    
    public function ajax_generate_article() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        
        if (!isset($this->seo_matrix['keywords'][$keyword])) {
            wp_send_json_error('Ключевое слово не найдено');
        }
        
        // Начинаем процесс генерации статьи
        $this->start_article_generation($keyword);
        
        wp_send_json_success(['message' => 'Генерация статьи начата']);
    }
    
    /**
     * AJAX обработчик тестовой генерации статьи
     */
    public function ajax_test_generate_article() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        // Генерируем тестовую статью "Что такое банковская гарантия"
        $keyword = 'что такое банковская гарантия';
        
        if (!isset($this->seo_matrix['keywords'][$keyword])) {
            wp_send_json_error('Тестовое ключевое слово не найдено в матрице');
        }
        
        // Получаем данные ключевого слова
        $keyword_data = $this->seo_matrix['keywords'][$keyword];
        
        // Генерируем статью
        $article_result = $this->generate_test_article_content($keyword, $keyword_data);
        
        // Запускаем анализ качества
        if (class_exists('BizFin_Quality_System')) {
            $quality_system = new BizFin_Quality_System();
            $quality_analysis = $quality_system->run_quality_analysis(0, $article_result);
            $article_result['quality_analysis'] = $quality_analysis;
        }
        
        wp_send_json_success($article_result);
    }
    
    private function start_article_generation($keyword) {
        // Здесь будет интеграция с ИИ-агентом
        // Пока что возвращаем структуру статьи
        
        $keyword_data = $this->seo_matrix['keywords'][$keyword];
        $structure = $this->seo_matrix['article_structures'][$keyword_data['structure']];
        
        // Генерируем базовую структуру статьи
        $article_content = $this->generate_article_structure($keyword, $keyword_data, $structure);
        
        // Сохраняем в базу данных
        $this->save_generated_article($keyword, $article_content);
    }
    
    private function generate_article_structure($keyword, $keyword_data, $structure) {
        $content = "<h1>{$structure['h1']}</h1>\n\n";
        
        foreach ($structure['h2_sections'] as $section) {
            $content .= "<h2>{$section}</h2>\n";
            $content .= "<p>[Контент для раздела: {$section}]</p>\n\n";
        }
        
        return $content;
    }
    
    private function save_generated_article($keyword, $content) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bsag_articles';
        
        $wpdb->insert(
            $table_name,
            [
                'keyword' => $keyword,
                'article_content' => $content,
                'status' => 'draft',
                'generated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s']
        );
    }
    
    /**
     * Генерация тестового контента статьи
     */
    private function generate_test_article_content($keyword, $keyword_data) {
        // Создаем базовую структуру статьи
        $article_content = $this->generate_article_structure($keyword, $keyword_data);
        
        // Создаем мета-данные
        $meta_data = [
            'title' => 'Что такое банковская гарантия: полное руководство 2025 | BizFin Pro',
            'meta_description' => 'Узнайте, что такое банковская гарантия простыми словами. Полное руководство для начинающих: участники процесса, механизм работы, стоимость, документы. Экспертные советы и практические примеры.',
            'og_title' => 'Что такое банковская гарантия: полное руководство',
            'og_description' => 'Простое объяснение банковской гарантии для начинающих. Участники, механизм работы, стоимость и практические советы от экспертов.',
            'focus_keyword' => $keyword
        ];
        
        // Создаем внутренние ссылки
        $internal_links = [
            ['text' => 'калькулятор банковских гарантий', 'url' => '/calculator/', 'position' => 'after_intro'],
            ['text' => 'стоимость банковских гарантий', 'url' => '/cost/', 'position' => 'middle'],
            ['text' => 'документы для получения гарантии', 'url' => '/documents/', 'position' => 'middle'],
            ['text' => 'банковские гарантии для тендеров', 'url' => '/tenders/', 'position' => 'conclusion']
        ];
        
        // Создаем CTA блоки
        $cta_blocks = [
            ['type' => 'calculator', 'text' => 'Рассчитать стоимость банковской гарантии', 'position' => 'after_intro'],
            ['type' => 'consultation', 'text' => 'Получить консультацию эксперта', 'position' => 'middle'],
            ['type' => 'form', 'text' => 'Оставить заявку на получение гарантии', 'position' => 'conclusion']
        ];
        
        // Создаем FAQ секцию
        $faq_section = [
            ['question' => 'Что такое банковская гарантия простыми словами?', 'answer' => 'Банковская гарантия — это письменное обязательство банка выплатить определенную сумму денег, если принципал не выполнит свои обязательства перед бенефициаром.'],
            ['question' => 'Кто может получить банковскую гарантию?', 'answer' => 'Банковскую гарантию может получить любое юридическое лицо или индивидуальный предприниматель, соответствующий требованиям банка.'],
            ['question' => 'Сколько стоит банковская гарантия?', 'answer' => 'Стоимость банковской гарантии составляет от 1% до 5% от суммы гарантии в год, в зависимости от банка, типа гарантии и финансового состояния заявителя.'],
            ['question' => 'Сколько времени занимает получение банковской гарантии?', 'answer' => 'Срок получения банковской гарантии составляет от 1 до 10 рабочих дней, в зависимости от банка и сложности заявки.'],
            ['question' => 'Какие документы нужны для получения банковской гарантии?', 'answer' => 'Основные документы: заявление, учредительные документы, финансовая отчетность, документы по тендеру или контракту, справки о состоянии расчетов.']
        ];
        
        return [
            'keyword' => $keyword,
            'content' => $article_content,
            'meta_data' => $meta_data,
            'internal_links' => $internal_links,
            'cta_blocks' => $cta_blocks,
            'faq_section' => $faq_section,
            'word_count' => str_word_count(strip_tags($article_content)),
            'keyword_density' => $this->calculate_keyword_density($article_content, $keyword),
            'generation_timestamp' => current_time('mysql'),
            'status' => 'test_generated'
        ];
    }
    
    /**
     * Расчет плотности ключевых слов
     */
    private function calculate_keyword_density($content, $keyword) {
        $content_lower = mb_strtolower(strip_tags($content), 'UTF-8');
        $keyword_lower = mb_strtolower($keyword, 'UTF-8');
        
        $total_words = str_word_count($content_lower);
        $keyword_count = substr_count($content_lower, $keyword_lower);
        
        return ($keyword_count / max($total_words, 1)) * 100;
    }
    
    // Методы интеграций перенесены в Integration Manager для предотвращения конфликтов
    
    /**
     * AJAX обработчик для e2e теста
     */
    public function ajax_run_e2e_test() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        // Проверяем наличие e2e теста
        if (class_exists('BizFin_E2E_Test_Bank_Guarantee_Process')) {
            $e2e_test = new BizFin_E2E_Test_Bank_Guarantee_Process();
            $e2e_test->ajax_run_e2e_test();
        } else {
            wp_send_json_error('E2E test class not available');
        }
    }
    
    /**
     * AJAX обработчик генерации статьи с модулями
     */
    public function ajax_generate_with_modules() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword']);
        $user_instruction = sanitize_textarea_field($_POST['user_instruction']);
        $table_of_contents = $_POST['table_of_contents'];
        $modules = $_POST['modules'];
        
        // Проверяем наличие системы динамических модулей
        if (!class_exists('BizFin_Dynamic_Modules_System')) {
            wp_send_json_error('Система динамических модулей не загружена');
        }
        
        $dynamic_modules = new BizFin_Dynamic_Modules_System();
        $result = $dynamic_modules->generate_article_with_modules($keyword, $user_instruction, $table_of_contents, $modules);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX обработчик публикации статьи
     */
    public function ajax_publish_article() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Статья не найдена');
        }
        
        // БЕЗУСЛОВНОЕ ПРАВИЛО: Валидация длины статьи перед публикацией
        $length_validation = $this->validate_article_length_before_publish($post_id, $post);
        
        if (!$length_validation['meets_requirement']) {
            // Если недобор и включен hard_gate - блокируем публикацию
            $mandatory_length = $this->seo_matrix['mandatory_length'] ?? [];
            if (!empty($mandatory_length['hard_gate']) && $mandatory_length['hard_gate']) {
                wp_send_json_error([
                    'error_type' => 'length_validation_failed',
                    'message' => "КРИТИЧЕСКИЙ НЕДОБОР: Статья содержит {$length_validation['word_count']} слов, требуется минимум {$length_validation['min_required']} слов",
                    'word_count' => $length_validation['word_count'],
                    'min_required' => $length_validation['min_required'],
                    'deficit' => $length_validation['deficit'],
                    'percentage' => $length_validation['percentage'],
                    'action_required' => 'auto_expansion_needed'
                ]);
            } else {
                // Если hard_gate отключен - публикуем, но помечаем для доращивания
                $this->schedule_article_expansion($post_id, $length_validation);
            }
        }
        
        // Обновляем статус на опубликованный
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);
        
        // Сохраняем метаданные о длине
        update_post_meta($post_id, '_bsag_word_count', $length_validation['word_count']);
        update_post_meta($post_id, '_bsag_min_words', $length_validation['min_required']);
        update_post_meta($post_id, '_bsag_length_validation', $length_validation);
        
        wp_send_json_success([
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'status' => 'published',
            'word_count' => $length_validation['word_count'],
            'length_validation' => $length_validation
        ]);
    }
    
    /**
     * AJAX обработчик для обновления реестра статей
     */
    public function ajax_update_articles_registry() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав для обновления реестра');
        }
        
        try {
            $updated_registry = $this->internal_linking_manager->update_registry_from_wp();
            
            wp_send_json_success([
                'message' => 'Реестр статей успешно обновлён',
                'articles_count' => count($updated_registry['articles']),
                'pages_count' => count($updated_registry['pages']),
                'registry' => $updated_registry
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Ошибка при обновлении реестра: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX обработчик для исправления размещения изображений
     */
    public function ajax_fix_image_placement() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Недостаточно прав для исправления размещения изображений');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $bulk_mode = $_POST['bulk_mode'] ?? false;
        
        try {
            if ($bulk_mode) {
                // Массовое исправление
                $fixed_count = $this->image_placement_manager->bulk_fix_image_placement();
                
                wp_send_json_success([
                    'message' => "Исправлено размещение изображений для {$fixed_count} статей",
                    'fixed_count' => $fixed_count
                ]);
            } else {
                // Исправление для конкретной статьи
                if (!$post_id) {
                    wp_send_json_error('Не указан ID статьи');
                }
                
                $fixed = $this->image_placement_manager->auto_fix_image_placement($post_id);
                
                if ($fixed) {
                    update_post_meta($post_id, '_bsag_image_placement_fixed', true);
                    
                    wp_send_json_success([
                        'message' => 'Размещение изображения исправлено',
                        'post_id' => $post_id
                    ]);
                } else {
                    wp_send_json_error('Не удалось исправить размещение изображения');
                }
            }
        } catch (Exception $e) {
            wp_send_json_error('Ошибка при исправлении размещения изображений: ' . $e->getMessage());
        }
    }
    
    /**
     * Страница E2E тестирования
     */
    public function e2e_tests_page() {
        ?>
        <div class="wrap">
            <h1>E2E Тестирование BizFin SEO Генератора</h1>
            
            <div class="bsag-e2e-tests-container">
                <div class="bsag-test-card">
                    <h2>Тест: "Как работает банковская гарантия"</h2>
                    <p><strong>Описание:</strong> Полный e2e тест генерации статьи с заданными параметрами</p>
                    
                    <div class="bsag-test-params">
                        <h3>Параметры теста:</h3>
                        <ul>
                            <li><strong>Ключевое слово:</strong> "Как работает банковская гарантия"</li>
                            <li><strong>Тип:</strong> Информационный (закупщики/юристы)</li>
                            <li><strong>Отстройка:</strong> BPMN‑диаграмма процесса с таймингом</li>
                            <li><strong>Модули:</strong> timeline, document_checklist</li>
                            <li><strong>CTA:</strong> "Ускорим выдачу — предварительный скоринг бесплатно"</li>
                        </ul>
                    </div>
                    
                    <div class="bsag-test-actions">
                        <button id="bsag-run-e2e-test" class="button button-primary button-large">
                            Запустить E2E Тест
                        </button>
                        <button id="bsag-view-test-results" class="button button-secondary" style="display: none;">
                            Просмотреть результаты
                        </button>
                    </div>
                    
                    <div id="bsag-test-progress" class="bsag-test-progress" style="display: none;">
                        <h3>Прогресс теста:</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                        <div id="bsag-test-log" class="test-log"></div>
                    </div>
                    
                    <div id="bsag-test-results" class="bsag-test-results" style="display: none;">
                        <h3>Результаты теста:</h3>
                        <div id="bsag-results-content"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .bsag-e2e-tests-container {
            max-width: 1200px;
            margin: 20px 0;
        }
        
        .bsag-test-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .bsag-test-params {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .bsag-test-params ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .bsag-test-actions {
            margin: 20px 0;
        }
        
        .bsag-test-progress {
            margin: 20px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            transition: width 0.3s ease;
        }
        
        .test-log {
            background: #f8f8f8;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
        }
        
        .bsag-test-results {
            background: #f0f8ff;
            border: 1px solid #0073aa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .test-success {
            color: #00aa00;
        }
        
        .test-error {
            color: #aa0000;
        }
        
        .test-warning {
            color: #ff6600;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#bsag-run-e2e-test').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Выполняется тест...');
                
                $('#bsag-test-progress').show();
                $('#bsag-test-log').empty();
                $('#bsag-test-results').hide();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bsag_e2e_test_bank_guarantee_process',
                        nonce: '<?php echo wp_create_nonce("bsag_ajax_nonce"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayTestResults(response.data);
                        } else {
                            displayTestError(response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        displayTestError('AJAX Error: ' + error);
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Запустить E2E Тест');
                    }
                });
            });
            
            function displayTestResults(data) {
                var logHtml = '';
                if (data.test_log) {
                    data.test_log.forEach(function(log) {
                        var className = '';
                        if (log.includes('✓')) className = 'test-success';
                        else if (log.includes('❌')) className = 'test-error';
                        else if (log.includes('⚠')) className = 'test-warning';
                        
                        logHtml += '<div class="' + className + '">' + log + '</div>';
                    });
                }
                
                $('#bsag-test-log').html(logHtml);
                $('.progress-fill').css('width', '100%');
                
                var resultsHtml = '<div class="test-success">';
                resultsHtml += '<h4>✅ Тест выполнен успешно!</h4>';
                resultsHtml += '<p><strong>ID статьи:</strong> ' + data.post_id + '</p>';
                resultsHtml += '<p><strong>URL статьи:</strong> <a href="' + data.article_url + '" target="_blank">' + data.article_url + '</a></p>';
                resultsHtml += '<p><strong>Количество слов:</strong> ' + data.word_count + '</p>';
                
                if (data.integrations_status) {
                    resultsHtml += '<h5>Статус интеграций:</h5><ul>';
                    for (var key in data.integrations_status) {
                        var status = data.integrations_status[key] ? '✅' : '❌';
                        resultsHtml += '<li>' + key + ': ' + status + '</li>';
                    }
                    resultsHtml += '</ul>';
                }
                
                if (data.modules_status) {
                    resultsHtml += '<h5>Статус модулей:</h5><ul>';
                    for (var key in data.modules_status) {
                        var status = data.modules_status[key] ? '✅' : '❌';
                        resultsHtml += '<li>' + key + ': ' + status + '</li>';
                    }
                    resultsHtml += '</ul>';
                }
                
                resultsHtml += '</div>';
                
                $('#bsag-results-content').html(resultsHtml);
                $('#bsag-test-results').show();
                $('#bsag-view-test-results').show();
            }
            
            function displayTestError(error) {
                $('#bsag-test-log').html('<div class="test-error">❌ Тест не выполнен: ' + error + '</div>');
                $('#bsag-test-results').html('<div class="test-error">❌ Ошибка выполнения теста: ' + error + '</div>').show();
            }
        });
        </script>
        <?php
    }
    
    /**
     * Получить SEO матрицу
     */
    public function get_seo_matrix() {
        return $this->seo_matrix;
    }
    
    /**
     * AJAX обработчик для обновления конкретного блока
     */
    public function ajax_update_block() {
        if (!isset($this->gutenberg_block_manager)) {
            wp_send_json_error('Gutenberg Block Manager не инициализирован');
        }
        
        $this->gutenberg_block_manager->ajax_update_block();
    }
    
    /**
     * AJAX обработчик для удаления дублирующихся изображений
     */
    public function ajax_remove_duplicate_images() {
        if (!isset($this->gutenberg_block_manager)) {
            wp_send_json_error('Gutenberg Block Manager не инициализирован');
        }
        
        $this->gutenberg_block_manager->ajax_remove_duplicate_images();
    }
    
    /**
     * Получить генератор тона и стиля
     */
    public function get_tone_style_generator() {
        return $this->tone_style_generator;
    }
    
    /**
     * Получить экземпляр плагина (Singleton)
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Валидация длины статьи перед публикацией
     */
    private function validate_article_length_before_publish($post_id, $post) {
        $content = $post->post_content;
        $word_count = $this->count_words_in_content($content);
        
        // Получаем минимальное количество слов из матрицы
        $mandatory_length = $this->seo_matrix['mandatory_length'] ?? [];
        $min_words = $mandatory_length['min_words'] ?? 2500;
        
        return [
            'word_count' => $word_count,
            'min_required' => $min_words,
            'meets_requirement' => $word_count >= $min_words,
            'deficit' => max(0, $min_words - $word_count),
            'percentage' => round(($word_count / $min_words) * 100, 1)
        ];
    }
    
    /**
     * Планирование доращивания статьи
     */
    private function schedule_article_expansion($post_id, $length_validation) {
        // Помечаем статью для доращивания
        update_post_meta($post_id, '_bsag_needs_expansion', true);
        update_post_meta($post_id, '_bsag_expansion_attempts', 0);
        update_post_meta($post_id, '_bsag_length_validation', $length_validation);
        
        // Планируем отложенное доращивание через 30 секунд
        wp_schedule_single_event(time() + 30, 'bsag_auto_expand_article', [$post_id]);
        
        error_log("BizFin: Article {$post_id} scheduled for auto-expansion. Word count: {$length_validation['word_count']}, deficit: {$length_validation['deficit']}");
    }
    
    /**
     * Подсчет слов в контенте (аналогично другим системам)
     */
    private function count_words_in_content($content) {
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
     * БЕЗУСЛОВНОЕ ПРАВИЛО: Обработчик автоматического доращивания статей
     */
    public function handle_auto_expand_article($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            error_log("BizFin: Auto-expansion failed - post {$post_id} not found");
            return;
        }
        
        // Проверяем, нужно ли доращивание
        $needs_expansion = get_post_meta($post_id, '_bsag_needs_expansion', true);
        if (!$needs_expansion) {
            error_log("BizFin: Auto-expansion skipped - post {$post_id} doesn't need expansion");
            return;
        }
        
        // Проверяем количество попыток
        $attempts = get_post_meta($post_id, '_bsag_expansion_attempts', true) ?: 0;
        $max_attempts = 3;
        
        if ($attempts >= $max_attempts) {
            error_log("BizFin: Auto-expansion failed - max attempts reached for post {$post_id}");
            update_post_meta($post_id, '_bsag_needs_expansion', false);
            return;
        }
        
        // Получаем данные ключевого слова
        $keyword = get_post_meta($post_id, '_bsag_keyword', true);
        if (!$keyword) {
            error_log("BizFin: Auto-expansion failed - no keyword found for post {$post_id}");
            return;
        }
        
        $keyword_data = $this->seo_matrix['keywords'][$keyword] ?? [];
        if (empty($keyword_data)) {
            error_log("BizFin: Auto-expansion failed - keyword data not found for '{$keyword}'");
            return;
        }
        
        // Выполняем доращивание через Tone Style Generator
        if (isset($this->tone_style_generator)) {
            $expanded_content = $this->tone_style_generator->auto_expand_content($post->post_content, $keyword, $keyword_data);
            
            if ($expanded_content !== $post->post_content) {
                // Обновляем контент статьи
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $expanded_content
                ]);
                
                // Обновляем счетчик попыток
                update_post_meta($post_id, '_bsag_expansion_attempts', $attempts + 1);
                
                // Проверяем, достигли ли нужной длины
                $new_word_count = $this->count_words_in_content($expanded_content);
                $min_words = $this->seo_matrix['mandatory_length']['min_words'] ?? 2500;
                
                if ($new_word_count >= $min_words) {
                    // Достигли нужной длины - убираем флаг доращивания
                    update_post_meta($post_id, '_bsag_needs_expansion', false);
                    error_log("BizFin: Auto-expansion completed for post {$post_id}. Final word count: {$new_word_count}");
                } else {
                    // Еще не достигли - планируем следующую попытку
                    $deficit = $min_words - $new_word_count;
                    wp_schedule_single_event(time() + 60, 'bsag_auto_expand_article', [$post_id]);
                    error_log("BizFin: Auto-expansion attempt " . ($attempts + 1) . " completed for post {$post_id}. Word count: {$new_word_count}, deficit: {$deficit}. Next attempt scheduled.");
                }
            } else {
                // Контент не изменился - увеличиваем счетчик попыток
                update_post_meta($post_id, '_bsag_expansion_attempts', $attempts + 1);
                error_log("BizFin: Auto-expansion attempt " . ($attempts + 1) . " - no content changes for post {$post_id}");
            }
        } else {
            error_log("BizFin: Auto-expansion failed - Tone Style Generator not available for post {$post_id}");
        }
    }
}

// Инициализация плагина
BizFin_SEO_Article_Generator::get_instance();
