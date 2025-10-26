# Технический стек BizFin Pro

## 🏗️ Общая архитектура

### Системная архитектура:
```
┌─────────────────────────────────────────────────────────────┐
│                    Пользовательский интерфейс                │
├─────────────────────────────────────────────────────────────┤
│  WordPress Frontend (Astra Theme + Elementor)              │
├─────────────────────────────────────────────────────────────┤
│  WordPress Core (6.8.3) + Plugins (29 + 16 MU)            │
├─────────────────────────────────────────────────────────────┤
│  PHP 8.0+ + MySQL 5.7+ + Apache/Nginx                     │
├─────────────────────────────────────────────────────────────┤
│  Серверная инфраструктура (Linux)                          │
└─────────────────────────────────────────────────────────────┘
```

## 🖥️ Серверная инфраструктура

### Операционная система:
- **OS:** Linux (Ubuntu/Debian)
- **Kernel:** 5.15.0-1032-realtime
- **Architecture:** x86_64

### Веб-сервер:
- **Apache/Nginx:** Версия 2.4+
- **PHP:** 8.0+ (рекомендуется 8.1+)
- **MySQL:** 5.7+ (рекомендуется 8.0+)

### Конфигурация PHP:
```ini
# Основные настройки
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000

# Расширения
extension=mysqli
extension=pdo_mysql
extension=curl
extension=gd
extension=mbstring
extension=openssl
extension=zip
extension=imagick
```

## 🗄️ База данных

### MySQL конфигурация:
```sql
-- Основные таблицы WordPress
wp_posts              -- Посты и страницы
wp_postmeta           -- Мета-данные постов
wp_users              -- Пользователи
wp_usermeta           -- Мета-данные пользователей
wp_options            -- Настройки сайта
wp_comments           -- Комментарии
wp_commentmeta        -- Мета-данные комментариев

-- Таблицы плагинов
wp_yoast_indexable    -- Yoast SEO индексы
wp_yoast_seo_links    -- Yoast SEO ссылки
wp_yoast_seo_meta     -- Yoast SEO мета-данные
wp_elementor_posts    -- Elementor данные
wp_elementor_css      -- Elementor стили
wp_wpforms_entries    -- WPForms записи
wp_wpforms_fields     -- WPForms поля
```

### Оптимизация БД:
```sql
-- Индексы для производительности
CREATE INDEX idx_posts_status ON wp_posts(post_status);
CREATE INDEX idx_posts_type ON wp_posts(post_type);
CREATE INDEX idx_postmeta_key ON wp_postmeta(meta_key);
CREATE INDEX idx_options_name ON wp_options(option_name);
```

## 🔌 API интеграции

### OpenAI API:
```php
// Конфигурация OpenAI
define('OPENAI_API_KEY', 'sk-proj-...');
define('OPENAI_API_URL', 'https://api.openai.com/v1/');
define('OPENAI_MODEL', 'gpt-4o');
define('OPENAI_MAX_TOKENS', 4000);
```

### Голландский прокси:
```php
// Конфигурация прокси
define('DUTCH_PROXY_HOST', '89.110.80.198');
define('DUTCH_PROXY_PORT', '8889');
define('DUTCH_PROXY_URL', 'http://89.110.80.198:8889');
```

### Яндекс.Метрика:
```javascript
// Конфигурация метрики
window.ym = window.ym || function(){(ym.a=ym.a||[]).push(arguments)};
ym(COUNTER_ID, 'init', {
    clickmap:true,
    trackLinks:true,
    accurateTrackBounce:true,
    webvisor:true
});
```

## 🎨 Фронтенд технологии

### CSS Framework:
- **Bootstrap:** 5.x (через Elementor)
- **Custom CSS:** Кастомные стили
- **Responsive Design:** Мобильная адаптация

### JavaScript:
- **jQuery:** 3.x (WordPress встроенный)
- **Elementor JS:** Конструктор страниц
- **Custom JS:** Кастомные скрипты
- **AJAX:** Асинхронные запросы

### Шрифты:
- **Google Fonts:** Основные шрифты
- **Tenor Sans:** Кастомный шрифт
- **Web Fonts:** Оптимизированные шрифты

## 🔧 WordPress конфигурация

### wp-config.php:
```php
// Основные настройки
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);

// Безопасность
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', false);
define('FORCE_SSL_ADMIN', true);

// Производительность
define('WP_CACHE', true);
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('ENFORCE_GZIP', true);

// База данных
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
```

### .htaccess оптимизация:
```apache
# Сжатие
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Кэширование
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Безопасность
<Files wp-config.php>
    order allow,deny
    deny from all
</Files>

<Files .htaccess>
    order allow,deny
    deny from all
</Files>
```

## 🚀 Производительность

### Кэширование:
```php
// Объектное кэширование
define('WP_CACHE_KEY_SALT', 'bizfin-pro-');
define('WP_CACHE_GROUP', 'bizfin-pro');

// Кэширование запросов
define('WP_QUERY_CACHE', true);
define('WP_OBJECT_CACHE', true);
```

### Оптимизация изображений:
```php
// WebP поддержка
define('WEBP_SUPPORT', true);
define('IMAGE_COMPRESSION_QUALITY', 85);

// Ленивая загрузка
define('LAZY_LOAD_IMAGES', true);
define('LAZY_LOAD_THRESHOLD', 200);
```

### CDN интеграция:
```php
// CDN настройки
define('CDN_URL', 'https://cdn.bizfin-pro.ru/');
define('CDN_ENABLED', true);
define('CDN_EXCLUDE_EXTENSIONS', 'php,html,htm');
```

## 🔒 Безопасность

### SSL/TLS:
```apache
# Принудительный HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Заголовки безопасности:
```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'"
```

### Ограничения доступа:
```apache
# Защита wp-admin
<Files wp-login.php>
    AuthType Basic
    AuthName "Restricted Access"
    AuthUserFile /path/to/.htpasswd
    Require valid-user
</Files>
```

## 📊 Мониторинг

### Логирование:
```php
// Настройки логирования
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_LOG_FILE', '/var/log/wordpress/debug.log');
define('ERROR_LOG_FILE', '/var/log/wordpress/error.log');
```

### Мониторинг производительности:
```php
// Query Monitor
define('QM_ENABLE', true);
define('QM_DB_QUERIES', true);
define('QM_DB_QUERIES_CALLERS', true);

// Server Monitor
define('SERVER_MONITOR_ENABLED', true);
define('SERVER_MONITOR_INTERVAL', 300);
```

### Алерты:
```php
// Proxy Monitor
define('PROXY_MONITOR_ENABLED', true);
define('PROXY_ALERT_THRESHOLD', 3);
define('PROXY_ALERT_EMAIL', 'admin@bizfin-pro.ru');
```

## 🔄 Резервное копирование

### Автоматические бэкапы:
```bash
#!/bin/bash
# Скрипт резервного копирования
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/bizfin-pro"
SITE_DIR="/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru"

# Бэкап файлов
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $SITE_DIR

# Бэкап базы данных
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Очистка старых бэкапов (старше 30 дней)
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
```

### Восстановление:
```bash
#!/bin/bash
# Скрипт восстановления
BACKUP_DATE=$1
BACKUP_DIR="/backups/bizfin-pro"
SITE_DIR="/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru"

# Восстановление файлов
tar -xzf $BACKUP_DIR/files_$BACKUP_DATE.tar.gz -C /

# Восстановление базы данных
mysql -u $DB_USER -p$DB_PASS $DB_NAME < $BACKUP_DIR/db_$BACKUP_DATE.sql
```

## 📈 Масштабирование

### Горизонтальное масштабирование:
- **Load Balancer:** Nginx/HAProxy
- **Multiple Servers:** Несколько серверов приложения
- **Database Replication:** Репликация MySQL
- **CDN:** Распределенная доставка контента

### Вертикальное масштабирование:
- **CPU:** Увеличение процессорной мощности
- **RAM:** Увеличение оперативной памяти
- **Storage:** SSD диски для БД
- **Network:** Увеличение пропускной способности

## 🔧 Развертывание

### CI/CD Pipeline:
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          rsync -avz --delete . user@server:/var/www/bizfin-pro.ru/
          ssh user@server "cd /var/www/bizfin-pro.ru && wp cache flush"
```

### Docker контейнеризация:
```dockerfile
# Dockerfile
FROM wordpress:6.8.3-php8.1-apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
```

---

**Версия документа:** 1.0  
**Дата создания:** 26 октября 2025  
**Для проекта:** BizFin Pro Website
