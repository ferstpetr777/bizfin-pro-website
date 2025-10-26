# Полный список атрибутов матрицы плагина BizFin SEO Article Generator

## 1. КЛЮЧЕВЫЕ СЛОВА (keywords)

### Структура каждого ключевого слова:
- **intent** - тип намерения (informational, commercial)
- **structure** - структура статьи (educational, service, pricing, how_to, legal, process, comparison)
- **target_audience** - целевая аудитория (beginners, professionals, project_managers, contractors, business_owners, specialists)
- **word_count** - количество слов
- **cta_type** - тип призыва к действию (calculator, form, guide, consultation)

### Персона и контекст (persona_context):
- **archetype** - архетип (Expert Educator, Expert Consultant)
- **core_belief** - основное убеждение
- **default_tone** - тон по умолчанию
- **sentence_length** - длина предложений
- **go_to_words** - ключевые слова для использования
- **avoid_words** - слова для избегания

### Глобальный тон и стиль (global_tone_style):
- **target_audience** - описание целевой аудитории
- **tone_formula** - формула тона:
  - opening_value - открывающая ценность
  - simple_explanation - простое объяснение
  - practical_minimum - практический минимум
  - practical_example - практический пример
  - expert_terms - экспертные термины
  - reader_address - обращение к читателю
  - friendly_professional - дружелюбный профессионализм
  - smooth_transition - плавный переход

### Языковые рекомендации (language_guidelines):
- **readability** - читаемость
- **confidence_note** - нотка уверенности
- **focus_on_reader** - фокус на читателе
- **active_voice** - активный залог
- **concrete_examples** - конкретные примеры
- **emotional_tone** - эмоциональный тон
- **avoid_clichés** - избегание клише

### Шаблон введения (introduction_template):
- **structure** - структура:
  - seo_title - SEO заголовок
  - brief_description - краткое описание
  - content_announcement - анонс содержания
  - table_of_contents - оглавление
  - main_definition - основное определение
  - life_example - жизненный пример
  - legal_context - правовой контекст
  - logical_transition - логический переход

### Редакционные правила (editorial_rules):
- **tone** - тон
- **volume** - объем
- **text_structure** - структура текста
- **seo_tools** - SEO инструменты
- **html_markup** - HTML разметка
- **formatting** - форматирование
- **visual_blocks** - визуальные блоки

### Ворота качества (quality_gates):
- **content_uniqueness** - уникальность контента
- **factual_accuracy** - фактическая точность
- **professional_tone** - профессиональный тон
- **industry_relevance** - релевантность отрасли
- **seo_optimization** - SEO оптимизация

### Цепочка промптов (prompt_chaining):
- **phase_1** - фаза 1 (data_analysis)
- **phase_2** - фаза 2 (structure_generation)
- **phase_3** - фаза 3 (content_creation)
- **phase_4** - фаза 4 (optimization_validation)

## 2. СТРУКТУРЫ СТАТЕЙ (article_structures)

### Типы структур:
- **educational** - образовательная
- **service** - сервисная
- **pricing** - ценовая
- **how_to** - пошаговая инструкция
- **legal** - правовая
- **process** - процессная
- **comparison** - сравнительная

### Для каждой структуры:
- **h1** - заголовок H1
- **h2_sections** - массив разделов H2

## 3. SEO ТРЕБОВАНИЯ (seo_requirements)

- **title_length** - длина заголовка (60 символов)
- **meta_description_length** - длина мета-описания (160 символов)
- **h1_count** - количество H1 (1)
- **h2_count** - количество H2 (8-12)
- **word_count_min** - минимум слов (1500)
- **word_count_max** - максимум слов (3000)
- **keyword_density** - плотность ключевых слов (1-3%)
- **internal_links** - внутренние ссылки (3-7)
- **external_links** - внешние ссылки (1-3)
- **cta_blocks** - блоки призыва к действию (2-3)
- **images_min** - минимум изображений (3)
- **images_with_alt** - изображения с alt-текстом (true)
- **schema_markup** - разметка схемы (true)
- **breadcrumbs** - хлебные крошки (true)
- **faq_section** - секция FAQ (true)

## 4. ОБЯЗАТЕЛЬНЫЕ БЛОКИ ВВЕДЕНИЯ (mandatory_intro_blocks)

### Простое определение (simple_definition):
- **required** - обязательно (true)
- **template** - шаблон
- **example** - пример

### Сочувствующий пример (sympathetic_example):
- **required** - обязательно (true)
- **template** - шаблон
- **example** - пример

## 5. ОБЯЗАТЕЛЬНЫЕ ТРЕБОВАНИЯ К ОГЛАВЛЕНИЮ (mandatory_toc_requirements)

- **clickable_links** - кликабельные ссылки (true)
- **anchor_links** - якорные ссылки (true)
- **smooth_scroll** - плавная прокрутка (true)
- **template** - шаблон HTML
- **anchor_format** - формат якорей (kebab-case)
- **validation** - правила валидации

## 6. ОБЯЗАТЕЛЬНАЯ ВНУТРЕННЯЯ ПЕРЕЛИНКОВКА (mandatory_internal_linking)

### Основные настройки:
- **enabled** - включено (true)
- **registry_source** - источник реестра (articles_registry.json)

### Правила (rules):
- **no_broken_links** - без битых ссылок
- **contextual_relevance** - контекстуальная релевантность
- **minimum_internal_links** - минимум внутренних ссылок (3)
- **maximum_internal_links** - максимум внутренних ссылок (7)

### Типы ссылок (link_types):
- **articles** - статьи (приоритет: high)
- **services** - сервисы (приоритет: high)
- **company_pages** - страницы компании (приоритет: medium)

### Автоматические ключевые слова (auto_linking_keywords):
- **bank-guarantees** - банковские гарантии
- **banking** - банковские услуги
- **business** - бизнес

## 7. ОБЯЗАТЕЛЬНОЕ РАЗМЕЩЕНИЕ ИЗОБРАЖЕНИЙ (mandatory_image_placement)

### Основные настройки:
- **enabled** - включено (true)

### Правила размещения (placement_rules):
- **position** - позиция (after_toc_before_content)
- **description** - описание
- **required** - обязательно (true)
- **validation** - валидация

### Требования к изображениям (image_requirements):
- **featured_image** - главное изображение
- **content_image** - изображение в контенте
- **alt_text** - alt-текст
- **styling** - стилизация

### Поток интеграции (integration_flow):
- **step_1** до **step_8** - 8 шагов интеграции

### Интеграция с ABP (abp_integration):
- **preserve_featured_image** - сохранение главного изображения
- **no_conflicts** - без конфликтов
- **timing_settings** - настройки тайминга

## 8. СИСТЕМА GUTENBERG БЛОКОВ (gutenberg_block_system)

### Основные настройки:
- **enabled** - включено (true)

### Типы блоков (block_types):
- **intro_section** - блок вводной секции
- **article_image** - блок изображения статьи
- **content_section** - блок контентной секции

### Стратегия обновления (update_strategy):
- **method** - метод (block_based)
- **ajax_endpoints** - AJAX эндпоинты

### Предотвращение дублирования (duplicate_prevention):
- **enabled** - включено (true)
- **auto_cleanup** - автоматическая очистка

## 9. ОБЯЗАТЕЛЬНЫЙ АДАПТИВНЫЙ ДИЗАЙН (mandatory_responsive_design)

### Основные настройки:
- **enabled** - включено (true)

### Требования (requirements):
- **mobile_first** - mobile-first подход
- **responsive_breakpoints** - адаптивные точки останова
- **flexible_layout** - гибкая компоновка
- **readable_typography** - читаемая типографика
- **reasonable_width_content** - разумная ширина контента
- **normalized_headings** - нормализованные заголовки
- **touch_friendly** - удобство касания

### CSS требования (css_requirements):
- **viewport_meta** - мета-тег viewport
- **responsive_images** - адаптивные изображения
- **flexible_containers** - гибкие контейнеры
- **media_queries** - медиа-запросы

### Правила валидации (validation_rules):
- **no_horizontal_scroll** - без горизонтальной прокрутки
- **readable_text** - читаемый текст
- **accessible_navigation** - доступная навигация
- **fast_loading** - быстрая загрузка

## 10. ОБЯЗАТЕЛЬНЫЕ ПРАВИЛА ДЛИНЫ (mandatory_length)

### Основные настройки:
- **enabled** - включено (true)
- **min_words** - минимум слов (2500)
- **hard_gate** - жесткий контроль (true)

### По разделам (by_sections):
- **intro** - введение (350-500 слов)
- **h2_sections** - разделы H2 (300-450 слов каждый)
- **faq** - FAQ (400-600 слов)
- **cta** - призыв к действию (80-150 слов)
- **safety_buffer** - буфер безопасности (5%)

### Настройки расширения (expansion_settings):
- **max_expansion_passes** - максимум проходов расширения (3)
- **expansion_strategies** - стратегии расширения
- **quality_control** - контроль качества

### Правила валидации (validation_rules):
- **count_method** - метод подсчета (clean_html_strip)
- **exclude_elements** - исключаемые элементы
- **encoding** - кодировка (utf8_mb_split)

### Автоматизация (automation):
- **auto_expansion** - автоматическое расширение
- **background_expansion** - фоновое расширение
- **cron_expansion** - расширение по расписанию

### Метаданные (metadata):
- **store_word_count** - сохранение количества слов
- **store_expansion_attempts** - сохранение попыток расширения
- **store_needs_expansion** - сохранение необходимости расширения
- **meta_keys** - ключи метаданных

---

## ИТОГО: 10 основных разделов матрицы

1. **keywords** - Ключевые слова и их параметры
2. **article_structures** - Структуры статей
3. **seo_requirements** - SEO требования
4. **mandatory_intro_blocks** - Обязательные блоки введения
5. **mandatory_toc_requirements** - Требования к оглавлению
6. **mandatory_internal_linking** - Внутренняя перелинковка
7. **mandatory_image_placement** - Размещение изображений
8. **gutenberg_block_system** - Система Gutenberg блоков
9. **mandatory_responsive_design** - Адаптивный дизайн
10. **mandatory_length** - Правила длины статей

Каждый раздел содержит детальные параметры, правила валидации, настройки автоматизации и интеграции с другими системами плагина.
