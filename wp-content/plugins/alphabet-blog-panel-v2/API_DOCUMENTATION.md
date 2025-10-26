# 🌐 API ДОКУМЕНТАЦИЯ СИСТЕМЫ БЛОГА

## 📋 ОБЗОР API

Система блога BizFin Pro предоставляет REST API для внешних приложений и интеграций. API построен на WordPress REST API и поддерживает CORS для кросс-доменных запросов.

**Базовый URL:** `/wp-json/abp/v1/`

---

## 🔑 АУТЕНТИФИКАЦИЯ

### Публичные endpoints:
Большинство endpoints не требуют аутентификации и доступны публично.

### Защищенные endpoints:
Для административных операций требуется WordPress nonce или API ключ.

---

## 📊 ENDPOINTS

### 1. 📈 Статистика блога

**GET** `/wp-json/abp/v1/stats`

Возвращает общую статистику блога.

#### Ответ:
```json
{
  "total_posts": 150,
  "total_letters": 25,
  "most_popular_letter": "Б",
  "posts_per_letter": {
    "А": 7,
    "Б": 12,
    "В": 2
  },
  "ai_categories": {
    "Банковские услуги": 45,
    "Кредиты и займы": 38
  }
}
```

---

### 2. 🔤 Список букв

**GET** `/wp-json/abp/v1/letters`

Возвращает список всех букв с количеством статей.

#### Ответ:
```json
{
  "letters": [
    {
      "letter": "А",
      "count": 7,
      "url": "/blog2/А/"
    },
    {
      "letter": "Б", 
      "count": 12,
      "url": "/blog2/Б/"
    }
  ]
}
```

---

### 3. 📝 Статьи по букве

**GET** `/wp-json/abp/v1/posts/{letter}`

Возвращает статьи для указанной буквы.

#### Параметры:
- `letter` (обязательный) - Буква для фильтрации
- `page` (опциональный) - Номер страницы (по умолчанию 1)
- `per_page` (опциональный) - Количество статей на страницу (по умолчанию 12)

#### Пример запроса:
```
GET /wp-json/abp/v1/posts/Б?page=1&per_page=10
```

#### Ответ:
```json
{
  "posts": [
    {
      "id": 123,
      "title": "Банковские гарантии",
      "excerpt": "Полное руководство по банковским гарантиям...",
      "url": "https://bizfin-pro.ru/bankovskie-garantii/",
      "date": "2025-10-19",
      "ai_category": "Банковские услуги",
      "first_letter": "Б"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 2,
    "total_posts": 12,
    "has_next": true,
    "has_prev": false
  }
}
```

---

### 4. 🔍 Поиск статей

**GET** `/wp-json/abp/v1/search`

Поиск статей по ключевым словам.

#### Параметры:
- `query` (обязательный) - Поисковый запрос
- `page` (опциональный) - Номер страницы
- `per_page` (опциональный) - Количество результатов

#### Пример запроса:
```
GET /wp-json/abp/v1/search?query=кредит&page=1
```

#### Ответ:
```json
{
  "posts": [
    {
      "id": 124,
      "title": "Автокредит: полное руководство",
      "excerpt": "Все об автокредитах...",
      "url": "https://bizfin-pro.ru/avtokredit/",
      "date": "2025-10-19",
      "ai_category": "Кредиты и займы",
      "relevance_score": 0.95
    }
  ],
  "query": "кредит",
  "total_results": 25,
  "pagination": {
    "current_page": 1,
    "total_pages": 3,
    "has_next": true
  }
}
```

---

### 5. 🏷️ AI Категории

**GET** `/wp-json/abp/v1/categories`

Возвращает список AI-категорий с количеством статей.

#### Ответ:
```json
{
  "categories": [
    {
      "name": "Банковские услуги",
      "slug": "bankovskie-uslugi",
      "count": 45,
      "description": "Статьи о банковских услугах и продуктах"
    },
    {
      "name": "Кредиты и займы",
      "slug": "kredity-i-zajmy",
      "count": 38,
      "description": "Информация о кредитах и займах"
    }
  ]
}
```

---

### 6. 📄 Детали статьи

**GET** `/wp-json/abp/v1/posts/{id}`

Возвращает подробную информацию о статье.

#### Параметры:
- `id` (обязательный) - ID статьи

#### Ответ:
```json
{
  "id": 123,
  "title": "Банковские гарантии",
  "content": "Полное содержание статьи...",
  "excerpt": "Краткое описание...",
  "url": "https://bizfin-pro.ru/bankovskie-garantii/",
  "date": "2025-10-19",
  "author": "BizFin Pro",
  "ai_category": "Банковские услуги",
  "first_letter": "Б",
  "seo_title": "Банковские гарантии - Полное руководство",
  "seo_description": "Все о банковских гарантиях...",
  "tags": ["гарантии", "банки", "бизнес"],
  "related_posts": [
    {
      "id": 124,
      "title": "Виды банковских гарантий",
      "url": "https://bizfin-pro.ru/vidy-garantij/"
    }
  ]
}
```

---

### 7. 🔄 Категоризация статьи

**POST** `/wp-json/abp/v1/categorize/{id}`

Запускает AI-категоризацию для указанной статьи.

#### Параметры:
- `id` (обязательный) - ID статьи

#### Ответ:
```json
{
  "success": true,
  "category": "Банковские услуги",
  "confidence": 0.92,
  "message": "Статья успешно категоризирована"
}
```

---

### 8. ⚡ Массовая категоризация

**POST** `/wp-json/abp/v1/categorize/bulk`

Запускает массовую категоризацию статей.

#### Тело запроса:
```json
{
  "post_ids": [123, 124, 125],
  "force_update": false
}
```

#### Ответ:
```json
{
  "success": true,
  "processed": 3,
  "results": [
    {
      "post_id": 123,
      "category": "Банковские услуги",
      "success": true
    }
  ]
}
```

---

## 🔧 КОДЫ ОШИБОК

### HTTP статус коды:
- `200` - Успешный запрос
- `400` - Неверные параметры
- `401` - Не авторизован
- `403` - Доступ запрещен
- `404` - Не найдено
- `500` - Внутренняя ошибка сервера

### Формат ошибки:
```json
{
  "code": "invalid_letter",
  "message": "Недопустимая буква",
  "data": {
    "status": 400
  }
}
```

---

## 📝 ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ

### JavaScript (Fetch API):

```javascript
// Получение статей по букве
async function getPostsByLetter(letter) {
  try {
    const response = await fetch(`/wp-json/abp/v1/posts/${letter}`);
    const data = await response.json();
    
    if (data.posts) {
      return data.posts;
    }
  } catch (error) {
    console.error('Ошибка загрузки статей:', error);
  }
}

// Поиск статей
async function searchPosts(query) {
  try {
    const response = await fetch(`/wp-json/abp/v1/search?query=${encodeURIComponent(query)}`);
    const data = await response.json();
    
    return data.posts || [];
  } catch (error) {
    console.error('Ошибка поиска:', error);
  }
}

// Получение статистики
async function getBlogStats() {
  try {
    const response = await fetch('/wp-json/abp/v1/stats');
    const data = await response.json();
    
    return data;
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error);
  }
}
```

### PHP (cURL):

```php
function getABPData($endpoint, $params = []) {
    $url = home_url("/wp-json/abp/v1/{$endpoint}");
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    return json_decode(wp_remote_retrieve_body($response), true);
}

// Использование
$posts = getABPData('posts/Б', ['page' => 1, 'per_page' => 10]);
$stats = getABPData('stats');
```

### Python (requests):

```python
import requests

def get_abp_data(endpoint, params=None):
    url = f"https://bizfin-pro.ru/wp-json/abp/v1/{endpoint}"
    
    try:
        response = requests.get(url, params=params)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Ошибка запроса: {e}")
        return None

# Использование
posts = get_abp_data('posts/Б', {'page': 1, 'per_page': 10})
stats = get_abp_data('stats')
```

---

## 🚀 ИНТЕГРАЦИЯ С ВНЕШНИМИ СИСТЕМАМИ

### Webhook поддержка:

```php
// Настройка webhook для уведомлений
add_action('abp_post_categorized', function($post_id, $category) {
    // Отправка уведомления во внешнюю систему
    wp_remote_post('https://external-system.com/webhook', [
        'body' => json_encode([
            'post_id' => $post_id,
            'category' => $category,
            'timestamp' => current_time('mysql')
        ]),
        'headers' => [
            'Content-Type' => 'application/json'
        ]
    ]);
}, 10, 2);
```

### CORS настройки:

```php
// Дополнительные CORS заголовки
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        return $value;
    });
});
```

---

## 📊 МОНИТОРИНГ И АНАЛИТИКА

### Логирование API запросов:

```php
// Логирование всех API запросов
add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    error_log("ABP API Request: " . $request->get_route() . " - " . $request->get_method());
    return $response;
}, 10, 3);
```

### Метрики производительности:

```php
// Отслеживание времени выполнения
add_action('rest_api_init', function() {
    add_filter('rest_request_after_callbacks', function($response, $handler, $request) {
        $start_time = microtime(true);
        // ... логика обработки
        $execution_time = microtime(true) - $start_time;
        error_log("ABP API Execution Time: {$execution_time}s");
        
        return $response;
    }, 10, 3);
});
```

---

## 🔒 БЕЗОПАСНОСТЬ

### Rate Limiting:

```php
// Ограничение количества запросов
add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'abp_rate_limit_' . md5($ip);
    
    $requests = get_transient($key) ?: 0;
    
    if ($requests > 100) { // 100 запросов в час
        return new WP_Error('rate_limit_exceeded', 'Превышен лимит запросов', ['status' => 429]);
    }
    
    set_transient($key, $requests + 1, HOUR_IN_SECONDS);
    
    return $response;
}, 10, 3);
```

### Валидация параметров:

```php
// Валидация параметров API
add_filter('rest_request_validation', function($errors, $request) {
    if ($request->get_route() === '/abp/v1/posts/(?P<letter>[a-zA-ZА-ЯЁ]+)') {
        $letter = $request->get_param('letter');
        if (!preg_match('/^[А-ЯЁA-Z]$/u', $letter)) {
            $errors->add('invalid_letter', 'Недопустимая буква');
        }
    }
    
    return $errors;
}, 10, 2);
```

---

## 📚 ДОПОЛНИТЕЛЬНЫЕ РЕСУРСЫ

### Postman коллекция:
Импортируйте коллекцию API endpoints в Postman для удобного тестирования.

### Swagger документация:
Доступна по адресу: `/wp-json/abp/v1/swagger.json`

### Webhook тестирование:
Используйте ngrok для локального тестирования webhook'ов.

---

**Версия API:** 1.0.0  
**Дата обновления:** 19 октября 2025  
**Поддержка:** BizFin Pro Development Team



