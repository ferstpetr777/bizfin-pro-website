# Полная логика работы плагина BizFin SEO Article Generator

## Архитектура плагина

### 🏗️ Основные компоненты

1. **Основной класс**: `BizFin_SEO_Article_Generator`
2. **Генератор тона и стиля**: `BizFin_Tone_Style_Generator`
3. **Система качества**: `BizFin_Quality_System`
4. **Цепочка промптов**: `BizFin_Prompt_Chaining_System`
5. **Динамические модули**: `BizFin_Dynamic_Modules_System`
6. **Менеджер интеграций**: `BizFin_Integration_Manager`
7. **Интеграции с плагинами**: `BizFin_Plugin_Integrations`

## 📋 SEO Матрица - Центральная логика

### Структура матрицы
```php
$seo_matrix = [
    'keywords' => [
        'что такое банковская гарантия' => [...],
        'как работает банковская гарантия' => [...],
        'виды банковских гарантий' => [...],
        // другие ключевые слова
    ],
    'article_structures' => [...],
    'seo_requirements' => [...]
];
```

### Критерии для каждого ключевого слова
```php
'ключевое слово' => [
    'intent' => 'informational|commercial|mixed',
    'structure' => 'educational|service|pricing|how_to|legal|comparison|process',
    'target_audience' => 'beginners|professionals|business_owners|contractors|project_managers',
    'word_count' => 2000-2800,
    'cta_type' => 'calculator|form|guide|consultation',
    
    // НОВОЕ: Персональный контекст
    'persona_context' => [
        'archetype' => 'Friendly Expert|Expert Educator|Expert Consultant',
        'core_belief' => 'Основное убеждение персоны',
        'default_tone' => 'Тон общения',
        'sentence_length' => 15-20,
        'go_to_words' => ['ключевые', 'слова'],
        'avoid_words' => ['избегаемые', 'слова']
    ],
    
    // НОВОЕ: Глобальный тон и стиль
    'global_tone_style' => [
        'target_audience' => 'конкретная ЦА',
        'tone_formula' => [
            'opening_value' => 'Формула открытия ценности',
            'simple_explanation' => 'Простое объяснение',
            'practical_minimum' => 'Практический минимум',
            'practical_example' => 'Практический пример',
            'expert_terms' => 'Экспертные термины',
            'reader_address' => 'Обращение к читателю',
            'friendly_professional' => 'Дружелюбный профессионализм',
            'smooth_transition' => 'Плавный переход'
        ],
        'language_guidelines' => [
            'readability' => 'Читаемость',
            'confidence_note' => 'Уверенность',
            'focus_on_reader' => 'Фокус на читателе',
            'active_voice' => 'Активный залог',
            'concrete_examples' => 'Конкретные примеры',
            'emotional_tone' => 'Эмоциональный тон',
            'avoid_clichés' => 'Избегание канцелярита'
        ]
    ],
    
    // НОВОЕ: Шаблон введения
    'introduction_template' => [
        'structure' => [
            'seo_title' => 'SEO заголовок',
            'brief_description' => 'Краткое описание',
            'content_announcement' => 'Анонс содержания',
            'table_of_contents' => 'Оглавление',
            'main_definition' => 'Основное определение',
            'life_example' => 'Пример из жизни',
            'legal_context' => 'Правовой контекст',
            'logical_transition' => 'Логический переход'
        ],
        'editorial_rules' => [
            'tone' => 'Тон введения',
            'volume' => 'Объём (300-500 слов)',
            'text_structure' => 'Структура текста',
            'seo_tools' => 'SEO инструменты',
            'html_markup' => 'HTML разметка',
            'formatting' => 'Форматирование',
            'visual_blocks' => 'Визуальные блоки'
        ]
    ],
    
    // Система качества
    'quality_gates' => [
        'content_uniqueness' => true,
        'factual_accuracy' => 0.95,
        'professional_tone' => 0.9,
        'industry_relevance' => 0.95,
        'seo_optimization' => 0.9
    ],
    
    // Цепочка промптов
    'prompt_chaining' => [
        'phase_1' => 'data_analysis',
        'phase_2' => 'structure_generation',
        'phase_3' => 'content_creation',
        'phase_4' => 'optimization_validation'
    ]
];
```

## 🔄 Полный цикл генерации статьи

### 1. Инициализация плагина
```php
// Singleton паттерн
BizFin_SEO_Article_Generator::get_instance();

// Загрузка всех модулей
$this->load_additional_modules();

// Инициализация генератора тона
$this->tone_style_generator = new BizFin_Tone_Style_Generator($this);
```

### 2. Получение параметров генерации
```php
// AJAX запрос с параметрами
$keyword = $_POST['keyword'];
$instructions = $_POST['instructions'];
$modules = $_POST['modules'];

// Получение данных из матрицы
$seo_matrix = $this->get_seo_matrix();
$keyword_data = $seo_matrix['keywords'][$keyword];
```

### 3. Генерация введения (НОВОЕ)
```php
// Применение шаблона введения
$introduction = $this->tone_style_generator->generate_introduction($keyword, $keyword_data);

// Результат:
<section class="intro">
  <h1>SEO заголовок с ключевым словом</h1>
  <p>Краткое описание темы</p>
  <p>Анонс содержания</p>
  <nav class="toc">
    <strong>Содержание:</strong>
    <ul>
      <li>Пункты оглавления</li>
    </ul>
  </nav>
  <h2>Основное определение</h2>
  <p>Определение темы</p>
  <div class="example">
    <p><strong>Пример:</strong> Практический пример</p>
  </div>
  <p>Правовой контекст</p>
  <p>Логический переход</p>
</section>
```

### 4. Цепочка промптов (4 фазы)
```php
$prompt_chaining = new BizFin_Prompt_Chaining_System();

// Фаза 1: Анализ данных
$analysis_result = $prompt_chaining->phase_1_data_analysis($keyword, $keyword_data);

// Фаза 2: Генерация структуры
$structure_result = $prompt_chaining->phase_2_structure_generation($analysis_result);

// Фаза 3: Создание контента
$content_result = $prompt_chaining->phase_3_content_creation($structure_result);

// Фаза 4: Оптимизация и валидация
$final_result = $prompt_chaining->phase_4_optimization_validation($content_result);
```

### 5. Применение тона и стиля (НОВОЕ)
```php
// Применение глобального тона
$content_with_tone = $this->tone_style_generator->apply_tone_formula(
    $content_result['content'], 
    $keyword_data['global_tone_style']
);

// Валидация соответствия тону
$tone_compliance = $this->tone_style_generator->validate_tone_compliance(
    $content_with_tone, 
    $keyword_data
);
```

### 6. Динамические модули
```php
$modules_system = new BizFin_Dynamic_Modules_System();

// Генерация модулей на основе инструкций
$modules_html = $modules_system->generate_modules($modules, $keyword_data);

// Доступные модули:
// - bsag_calculator (калькулятор)
// - bsag_diagram (диаграмма)
// - bsag_timeline (временная шкала)
// - bsag_checklist (чек-лист)
// - bsag_comparison_table (сравнительная таблица)
```

### 7. Система качества
```php
$quality_system = new BizFin_Quality_System();

// Проверка качества контента
$quality_score = $quality_system->assess_content_quality($final_content);

// Критерии качества:
// - Уникальность контента
// - Фактическая точность
// - Профессиональный тон
// - Релевантность отрасли
// - SEO оптимизация
```

### 8. Создание поста WordPress
```php
// Создание поста
$post_data = [
    'post_title' => $keyword_data['title'],
    'post_content' => $introduction . $content_with_tone . $modules_html,
    'post_status' => 'publish',
    'post_type' => 'post',
    'post_author' => 1
];

$post_id = wp_insert_post($post_data);

// Установка мета-полей
update_post_meta($post_id, '_bsag_generated_article', true);
update_post_meta($post_id, '_bsag_article_data', json_encode($keyword_data));
```

### 9. Интеграции через менеджер (Централизованное управление)
```php
// Запуск события генерации статьи
do_action('bsag_article_generated', $post_id, $keyword_data);

// Integration Manager обрабатывает все интеграции:
class BizFin_Integration_Manager {
    public function execute_integrations($post_id, $post) {
        // 1. SEO оптимизация (Yoast SEO)
        $this->execute_seo_optimization($post_id, $post);
        
        // 2. ABP Quality Check
        $this->execute_abp_quality_check($post_id, $post);
        
        // 3. Alphabet Blog Panel Integration
        $this->execute_alphabet_blog_integration($post_id, $post);
        
        // 4. ABP Image Generator
        $this->execute_abp_image_generation($post_id, $post);
        
        // 5. Финальная оптимизация
        $this->execute_final_optimization($post_id, $post);
    }
}
```

### 10. Интеграции с внешними плагинами

#### ABP Article Quality Monitor
```php
// Автоматическая проверка качества
$quality_monitor = new ABP_Article_Quality_Monitor();
$quality_monitor->check_post_quality($post_id, $post);

// Проверки:
// - AI категоризация
// - SEO мета-данные
// - Интеграция с алфавитной системой
```

#### ABP Image Generator
```php
// Автоматическая генерация изображений
$image_generator = new ABP_Image_Generator();
$image_generator->generate_image_for_post($post_id);

// Использует OpenAI DALL-E для генерации изображений
// Оптимизирует изображения для SEO
```

#### Alphabet Blog Panel
```php
// Интеграция с алфавитной системой
$first_letter = mb_strtoupper(mb_substr($post->post_title, 0, 1, 'UTF-8'), 'UTF-8');
update_post_meta($post_id, 'abp_first_letter', $first_letter);
```

#### Yoast SEO
```php
// SEO оптимизация
update_post_meta($post_id, '_yoast_wpseo_title', $keyword_data['title']);
update_post_meta($post_id, '_yoast_wpseo_metadesc', $keyword_data['meta_description']);
update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword_data['keyword']);
```

## 🎯 Ключевые особенности логики

### 1. Централизованное управление
- **Integration Manager** контролирует все интеграции
- Предотвращает конфликты между плагинами
- Обеспечивает правильную последовательность выполнения

### 2. Качество контента
- **4-фазная цепочка промптов** для глубокой проработки
- **Система качества** с 5 критериями
- **Валидация тона** для соответствия бренду

### 3. SEO оптимизация
- **Матрица SEO-параметров** для каждого ключевого слова
- **Автоматическая генерация** мета-данных
- **Структурированная разметка** Schema.org

### 4. Динамические модули
- **Интерактивные элементы** (калькуляторы, диаграммы)
- **Адаптивные модули** под тип контента
- **Пользовательские инструкции** для кастомизации

### 5. Тон и стиль (НОВОЕ)
- **Глобальный тон** для всех статей
- **Шаблон введения** для структурированного начала
- **Языковые правила** для читаемости и профессионализма

## 🔧 Техническая реализация

### Файловая структура
```
bizfin-seo-article-generator/
├── bizfin-seo-article-generator.php (основной файл)
├── includes/
│   ├── tone-style-generator.php (НОВОЕ)
│   ├── quality-system.php
│   ├── prompt-chaining-system.php
│   ├── dynamic-modules-system.php
│   ├── plugin-integrations.php
│   ├── integration-manager.php
│   └── ai-agent-integration.php
├── assets/
│   ├── css/
│   └── js/
└── test-*.php (тестовые файлы)
```

### AJAX endpoints
- `bsag_test_generate_article` - тестовая генерация
- `bsag_generate_with_modules` - генерация с модулями
- `bsag_publish_article` - публикация статьи
- `bsag_e2e_test_*` - end-to-end тесты

### WordPress хуки
- `save_post` - управляется через Integration Manager
- `bsag_article_generated` - событие генерации статьи
- `bsag_integrations_completed` - завершение интеграций

## 📊 Метрики и контроль качества

### Автоматические проверки
1. **Синтаксис PHP** - валидация кода
2. **Структура матрицы** - проверка данных
3. **Интеграции** - тестирование связей
4. **Качество контента** - оценка по критериям
5. **Соответствие тону** - валидация стиля

### Логирование
```php
error_log("BizFin Integration Manager: Starting integrations for post {$post_id}");
error_log("BizFin Integration Manager: All integrations completed for post {$post_id}");
```

## 🚀 Готовность к продакшену

✅ **Все компоненты протестированы**
✅ **Ошибки исправлены**
✅ **Интеграции работают**
✅ **Качество контента контролируется**
✅ **SEO оптимизация автоматизирована**

**Плагин готов к использованию в продакшене!**

