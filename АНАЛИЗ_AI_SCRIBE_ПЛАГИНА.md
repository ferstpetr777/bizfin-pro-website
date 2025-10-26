# 🔍 ДЕТАЛЬНЫЙ АНАЛИЗ ПЛАГИНА AI-SCRIBE

## 📋 ОБЩАЯ ИНФОРМАЦИЯ

**Плагин:** AI Writer: ChatGPT SEO Content Creator  
**Версия:** 2.6.1  
**Автор:** Opace Digital Agency  
**Расположение:** `/wp-content/plugins/ai-scribe-the-chatgpt-powered-seo-content-creation-wizard/`  
**Дата анализа:** 11 октября 2025  

---

## 🎯 КЛЮЧЕВЫЕ ФУНКЦИИ ДЛЯ SEO

### 1️⃣ **Функция `send_post_page()` - Сохранение SEO данных**

**Расположение:** `article_builder.php`, строки 1606-1750

#### 📝 **Логика сохранения:**

```php
// 1. Получение мета-данных из POST запроса
$metaData = array_map("sanitize_text_field", $_POST["metaData"] ?? []);

// 2. Создание поста
$my_post = [
    "post_type" => "post",
    "post_title" => $post_title,
    "post_content" => $articleValue,
    "post_status" => "draft",
    "post_name" => $truncated_slug,
];
$insertPost = wp_insert_post($my_post);

// 3. Сохранение SEO данных в зависимости от активного плагина
if ($insertPost > 0) {
    $keywordStr = implode(", ", $keywordData);
    
    if (defined("WPSEO_FILE")) {
        // Yoast SEO
        update_post_meta($insertPost, "_yoast_wpseo_title", $metaData[0]);
        update_post_meta($insertPost, "_yoast_wpseo_metadesc", $metaData[1]);
        update_post_meta($insertPost, "_yoast_wpseo_focuskw", $keywordStr);
    }
}
```

#### 🔑 **Ключевые моменты:**
- `$metaData[0]` = SEO Title (Meta Title)
- `$metaData[1]` = Meta Description
- `$keywordStr` = Focus Keyword (через запятую)
- Проверка активного SEO плагина через `defined()`

### 2️⃣ **Функция `suggest_content()` - Генерация контента**

**Расположение:** `article_builder.php`, строки 2643-4000+

#### 📝 **Обработка мета-данных:**

```php
// Специальная обработка для seo-meta-data
elseif ($actionInput == "seo-meta-data") {
    $combinedContent = explode("\n\n", $combinedContent);
}

// Очистка мета-данных
if ($actionInput == "seo-meta-data") {
    $textValue = str_replace("\n\n", "<br/>", $textValue);
    $textValue = ltrim($textValue, "<br/>");
    $textValue = trim(str_replace("Meta Title:", "", $textValue));
    $textValue = trim(str_replace("Meta Description:", "", $textValue));
}
```

### 3️⃣ **JavaScript функция `allCheckElements()` - Сбор данных**

**Расположение:** `assets/js/create_template.js`, строки 1646-1663

#### 📝 **Сбор мета-данных:**

```javascript
var metadataCheckObj = [];
jQuery('.generate_seo-meta-data .get_checked:checked').each(function(i) {
    metadataCheckObj[i] = jQuery(this).val();
    metadataCheckObj[i] = metadataCheckObj[i]?.replace(
        metadataCheckObj[i]?.match(/(\d+)./g), ''
    ).trim();
});

var allcheckArray = {
    title: titleCheckObj,
    heading: headingCheckObj,
    keyword: keywordCheckObj,
    intro: introCheckObj,
    tagline: taglineCheckObj,
    conclusion: conclusionCheckObj,
    qna: qnaCheckObj,
    metadata: metadataCheckObj,  // ← Ключевое поле
};
```

### 4️⃣ **AJAX отправка данных**

**Расположение:** `assets/js/create_template.js`, строки 1680-1697

#### 📝 **Отправка на сервер:**

```javascript
jQuery.ajax({
    type: "post",
    url: linkaction,
    dataType: 'json',
    data: {
        action: 'al_scribe_send_post_page',
        security: ai_scribe.nonce,
        titleData: getAllCheckElement.title,
        headingData: getAllCheckElement.heading,
        keywordData: getAllCheckElement.keyword,
        introData: getAllCheckElement.intro,
        taglineData: getAllCheckElement.tagline,
        articleVal: editor_content,
        conclusionData: getAllCheckElement.conclusion,
        qnaData: getAllCheckElement.qna,
        metaData: getAllCheckElement.metadata,  // ← Отправка мета-данных
        contentData: checkObj,
    },
    success: function(response) {
        alert("Post saved successfully!");
    }
});
```

---

## 🎨 ПРОМПТЫ ДЛЯ ГЕНЕРАЦИИ МЕТА-ДАННЫХ

### 📝 **Промпт для SEO мета-данных:**

**Расположение:** `article_builder.php`, строки 1260-1261

```php
"meta_prompts" => 'Create a single SEO friendly meta title and meta description. Based this on the "[Title]" article title and the [Selected Keywords]. Create the meta data in the [Language] language. Follow SEO best practices and make the meta data catchy to attract clicks. Do not add any additional markup such as ***'
```

### 🔄 **Обработка ответа AI:**

1. **Разделение по двойным переносам строк:** `explode("\n\n", $combinedContent)`
2. **Очистка от префиксов:** Удаление "Meta Title:" и "Meta Description:"
3. **Форматирование:** Замена переносов на `<br/>`
4. **Сохранение в массив:** `$metaData[0]` и `$metaData[1]`

---

## 🗄️ СТРУКТУРА БАЗЫ ДАННЫХ

### 📊 **Yoast SEO поля:**

| Поле | Значение | Описание |
|------|----------|----------|
| `_yoast_wpseo_title` | `$metaData[0]` | SEO заголовок |
| `_yoast_wpseo_metadesc` | `$metaData[1]` | Meta описание |
| `_yoast_wpseo_focuskw` | `$keywordStr` | Ключевое слово |

### 📊 **Другие SEO плагины:**

#### **Rank Math:**
- `rank_math_title` → `$metaData[0]`
- `rank_math_description` → `$metaData[1]`
- `rank_math_focus_keyword` → `$keywordStr`

#### **All in One SEO:**
- `_aioseop_title` → `$metaData[0]`
- `_aioseop_description` → `$metaData[1]`
- Focus keyword не поддерживается

#### **SEOPress:**
- `_seopress_titles_title` → `$metaData[0]`
- `_seopress_titles_desc` → `$metaData[1]`
- `_seopress_analysis_target_kw` → `$keywordStr`

---

## 🔧 ТЕХНИЧЕСКАЯ РЕАЛИЗАЦИЯ

### 1️⃣ **Проверка активного SEO плагина:**

```php
if (defined("WPSEO_FILE")) {
    // Yoast SEO активен
} elseif (defined("AIOSEOP_VERSION")) {
    // All in One SEO активен
} elseif (defined("RANK_MATH_FILE")) {
    // Rank Math активен
} elseif (defined("SEOPRESS_VERSION")) {
    // SEOPress активен
}
```

### 2️⃣ **Безопасность:**

- **Nonce проверка:** `check_ajax_referer("ai_scribe_nonce", "security", false)`
- **Санитизация:** `sanitize_text_field()` для всех входных данных
- **Basic Auth:** Альтернативная аутентификация для администраторов

### 3️⃣ **Обработка ошибок:**

```php
if (!$nonce_valid) {
    wp_send_json_error([
        "msg" => "Security nonce is missing or invalid.",
        "nonce_expired" => true,
        "debug" => $nonce_debug,
    ]);
    return;
}
```

---

## 📱 FRONTEND ИНТЕГРАЦИЯ

### 🎨 **HTML структура:**

```html
<div class="generate_seo-meta-data">
    <input type="checkbox" class="get_checked" value="Generated meta title">
    <input type="checkbox" class="get_checked" value="Generated meta description">
</div>
```

### ⚡ **JavaScript обработка:**

1. **Сбор данных:** `allCheckElements()` собирает все отмеченные элементы
2. **AJAX отправка:** Отправка через `al_scribe_send_post_page`
3. **Обратная связь:** Alert "Post saved successfully!"

---

## 🎯 КЛЮЧЕВЫЕ ВЫВОДЫ ДЛЯ РАЗРАБОТКИ

### ✅ **Что работает хорошо:**

1. **Универсальность:** Поддержка всех основных SEO плагинов
2. **Безопасность:** Многоуровневая проверка nonce
3. **Гибкость:** Автоматическое определение активного SEO плагина
4. **AI интеграция:** Использование промптов для генерации мета-данных

### 🔧 **Техническая логика:**

1. **Генерация через AI:** Промпт → AI ответ → Парсинг → Массив `$metaData`
2. **Сохранение:** `update_post_meta()` для каждого SEO плагина
3. **Структура данных:** `$metaData[0]` = title, `$metaData[1]` = description

### 📋 **Для нашего скрипта нужно:**

1. **Использовать `update_post_meta()`** для сохранения SEO данных
2. **Проверять активный SEO плагин** через `defined()`
3. **Использовать правильные поля** для каждого плагина
4. **Санитизировать данные** перед сохранением
5. **Генерировать мета-данные** через AI или из существующих данных

---

## 🚀 РЕКОМЕНДАЦИИ ДЛЯ РЕАЛИЗАЦИИ

### 1️⃣ **Структура функции сохранения:**

```php
function save_seo_meta($post_id, $meta_title, $meta_description, $focus_keyword) {
    // Проверка активного SEO плагина
    if (defined("WPSEO_FILE")) {
        update_post_meta($post_id, "_yoast_wpseo_title", $meta_title);
        update_post_meta($post_id, "_yoast_wpseo_metadesc", $meta_description);
        update_post_meta($post_id, "_yoast_wpseo_focuskw", $focus_keyword);
    }
    // ... другие плагины
}
```

### 2️⃣ **Генерация мета-данных:**

```php
function generate_seo_meta($title, $keywords, $content) {
    // Использовать AI или готовые данные
    $meta_title = generate_meta_title($title, $keywords);
    $meta_description = generate_meta_description($content, $keywords);
    
    return [$meta_title, $meta_description];
}
```

### 3️⃣ **Интеграция с существующими статьями:**

```php
function update_article_seo($post_id, $seo_data) {
    $meta_data = generate_seo_meta(
        get_the_title($post_id),
        $seo_data['keywords'],
        get_post_field('post_content', $post_id)
    );
    
    save_seo_meta($post_id, $meta_data[0], $meta_data[1], $seo_data['keywords']);
}
```

---

## ✅ ИТОГОВЫЙ АНАЛИЗ

Плагин AI-Scribe демонстрирует **профессиональный подход** к работе с SEO данными:

1. **Универсальность** - поддержка всех популярных SEO плагинов
2. **Безопасность** - многоуровневая проверка и санитизация
3. **AI интеграция** - использование промптов для генерации мета-данных
4. **Гибкость** - автоматическое определение активного плагина

**Для нашего проекта** этот анализ дает четкое понимание того, как правильно сохранять SEO данные в WordPress и интегрироваться с различными SEO плагинами.

---

*Анализ проведен: 11 октября 2025*  
*Автор: AI Assistant*  
*Статус: Комплексный анализ завершен*

