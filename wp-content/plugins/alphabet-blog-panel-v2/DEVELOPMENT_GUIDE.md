# 🚀 РУКОВОДСТВО ПО РАЗРАБОТКЕ СИСТЕМЫ БЛОГА

## 📋 БЫСТРЫЙ СТАРТ

### Требования:
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Yoast SEO (для полной функциональности)

### Установка:
1. Активировать плагин `Alphabet Blog Panel v2`
2. Остальные плагины активируются автоматически
3. Проверить создание страницы `/blog2/`

---

## 🔧 ОСНОВНЫЕ КОМАНДЫ

### Добавление новой функции в основной плагин:

```php
// В классе ABP_V2_Plugin
public function new_function() {
    // Логика функции
}

// Регистрация хука
add_action('wp_ajax_new_action', [$this, 'new_function']);
```

### Добавление нового шаблона:

```php
// В методе intercept_template()
if ($custom_condition) {
    $tpl = plugin_dir_path(__FILE__) . 'templates/new-template.php';
    if (file_exists($tpl)) return $tpl;
}
```

### Добавление нового мета-поля:

```php
// При сохранении поста
update_post_meta($post_id, 'new_meta_key', $value);

// При получении
$value = get_post_meta($post_id, 'new_meta_key', true);
```

---

## 🎨 КАСТОМИЗАЦИЯ ДИЗАЙНА

### CSS переменные:
```css
:root {
  --abp-primary: #FF6B00;      /* Основной цвет */
  --abp-secondary: #FF9A3C;    /* Вторичный цвет */
  --abp-text: #0F172A;         /* Текст */
  --abp-muted: #556070;        /* Приглушенный текст */
}
```

### Изменение стилей алфавитного меню:
```css
.abp-v2-main-header {
    /* Стили для заголовка меню */
}

.abp-v2-letter {
    /* Стили для кнопок букв */
}
```

---

## 🔌 РАСШИРЕНИЕ ФУНКЦИОНАЛЬНОСТИ

### Добавление нового AJAX обработчика:

```php
public function ajax_new_handler() {
    check_ajax_referer(self::NONCE, 'nonce');
    
    $param = sanitize_text_field($_POST['param'] ?? '');
    
    // Логика обработки
    
    wp_send_json_success(['data' => $result]);
}
```

### Добавление нового API endpoint:

```php
// В классе ABP_External_API
register_rest_route(self::API_NAMESPACE, '/new-endpoint', [
    'methods' => 'GET',
    'callback' => [$this, 'new_endpoint_handler'],
    'permission_callback' => '__return_true',
]);
```

---

## 🤖 ИНТЕГРАЦИЯ С AI

### Добавление новой категории:

```php
// В классе ABP_AI_Categorization
private function get_business_categories() {
    return [
        // ... существующие категории
        'Новая категория' => 'Описание новой категории',
    ];
}
```

### Кастомизация AI промпта:

```php
private function create_ai_prompt($title, $content) {
    return "Проанализируй статью и определи категорию:\n\n" .
           "Заголовок: {$title}\n\n" .
           "Содержание: {$content}\n\n" .
           "Категории: " . implode(', ', array_keys($this->get_business_categories()));
}
```

---

## 📊 МОНИТОРИНГ И ОТЛАДКА

### Включение отладки:

```php
// В wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Логирование в плагинах:

```php
error_log('ABP Debug: ' . $message);
```

### Проверка AJAX запросов:

```javascript
console.log('AJAX Response:', response);
```

---

## 🗄️ РАБОТА С БАЗОЙ ДАННЫХ

### Создание новой таблицы:

```php
global $wpdb;
$table_name = $wpdb->prefix . 'new_table';

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id int(11) NOT NULL AUTO_INCREMENT,
    post_id int(11) NOT NULL,
    data text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
```

### Выполнение кастомных запросов:

```php
global $wpdb;

$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}new_table WHERE post_id = %d",
    $post_id
));
```

---

## 🎯 SEO ОПТИМИЗАЦИЯ

### Добавление новых мета-тегов:

```php
public function add_custom_meta_tags() {
    echo '<meta name="custom-meta" content="' . esc_attr($value) . '">' . "\n";
}

add_action('wp_head', [$this, 'add_custom_meta_tags']);
```

### Интеграция с Yoast SEO:

```php
add_filter('wpseo_title', [$this, 'custom_title_filter']);
add_filter('wpseo_metadesc', [$this, 'custom_description_filter']);
```

---

## ⚡ ОПТИМИЗАЦИЯ ПРОИЗВОДИТЕЛЬНОСТИ

### Кеширование результатов:

```php
public function get_cached_data($key) {
    $cached = wp_cache_get($key, 'abp_cache');
    if ($cached !== false) {
        return $cached;
    }
    
    $data = $this->expensive_operation();
    wp_cache_set($key, $data, 'abp_cache', 3600); // 1 час
    
    return $data;
}
```

### Оптимизация SQL запросов:

```php
// Использование индексов
$posts = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
     WHERE pm.meta_key = %s 
     AND pm.meta_value = %s
     AND p.post_status = 'publish'
     ORDER BY p.post_title ASC",
    'abp_first_letter',
    $letter
));
```

---

## 🔒 БЕЗОПАСНОСТЬ

### Валидация входных данных:

```php
// Санитизация
$input = sanitize_text_field($_POST['input'] ?? '');

// Валидация
if (!preg_match('/^[А-ЯЁA-Z]$/u', $letter)) {
    wp_send_json_error(['message' => 'Invalid letter']);
}

// Экранирование вывода
echo esc_html($user_input);
echo esc_url($url);
echo esc_attr($attribute);
```

### Nonce защита:

```php
// Создание nonce
wp_create_nonce('action_name');

// Проверка nonce
check_ajax_referer('action_name', 'nonce');
```

---

## 📱 АДАПТИВНОСТЬ

### Медиа-запросы:

```css
/* Планшеты */
@media (max-width: 768px) {
    .abp-v2-main-posts-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Мобильные */
@media (max-width: 480px) {
    .abp-v2-main-posts-grid {
        grid-template-columns: 1fr;
    }
}
```

### Touch события:

```javascript
// Поддержка touch устройств
if ('ontouchstart' in window) {
    document.addEventListener('touchstart', handleTouch);
}
```

---

## 🧪 ТЕСТИРОВАНИЕ

### Unit тесты:

```php
class TestABPPlugin extends WP_UnitTestCase {
    public function test_save_first_letter() {
        $post_id = $this->factory->post->create([
            'post_title' => 'Акция банка'
        ]);
        
        $plugin = new ABP_V2_Plugin();
        $plugin->save_first_letter($post_id, get_post($post_id));
        
        $first_letter = get_post_meta($post_id, 'abp_first_letter', true);
        $this->assertEquals('А', $first_letter);
    }
}
```

### Функциональные тесты:

```javascript
// Тестирование AJAX запросов
describe('ABP AJAX Tests', function() {
    it('should load posts for letter А', function(done) {
        // Тест логика
        done();
    });
});
```

---

## 📚 ПОЛЕЗНЫЕ РЕСУРСЫ

### WordPress Codex:
- [WordPress Hooks](https://codex.wordpress.org/Plugin_API/Hooks)
- [AJAX в WordPress](https://codex.wordpress.org/AJAX_in_Plugins)
- [Custom Post Types](https://codex.wordpress.org/Post_Types)

### API документация:
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
- [OpenAI API](https://platform.openai.com/docs/api-reference)

### Инструменты разработки:
- [WordPress Debug Bar](https://wordpress.org/plugins/debug-bar/)
- [Query Monitor](https://wordpress.org/plugins/query-monitor/)

---

## 🚨 ЧАСТЫЕ ПРОБЛЕМЫ И РЕШЕНИЯ

### Проблема: AJAX не работает
**Решение:** Проверить nonce и правильность регистрации обработчика

### Проблема: Стили не применяются
**Решение:** Проверить правильность подключения CSS файлов

### Проблема: SEO не работает
**Решение:** Убедиться, что Yoast SEO активен

### Проблема: AI категоризация не работает
**Решение:** Проверить API ключ OpenAI и лимиты

---

**Версия руководства:** 1.0.0  
**Дата создания:** 19 октября 2025



