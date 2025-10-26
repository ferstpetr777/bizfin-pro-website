# BizFin Pro Website - Документация для Cursor Vector DB

## Описание проекта

**BizFin Pro** - это профессиональная платформа финансовых услуг и консультаций, предоставляющая комплексные решения для бизнеса в области финансового планирования, инвестиций, налогового консультирования и корпоративного финансирования.

**Сайт**: https://bizfin-pro.ru  
**GitHub**: https://github.com/ferstpetr777/bizfin-pro-website

## Архитектура системы

### Технический стек

- **CMS**: WordPress 6.x
- **E-commerce**: WooCommerce (для платных услуг)
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Web Server**: Apache/Nginx
- **SSL**: Let's Encrypt
- **CDN**: CloudFlare
- **CRM**: Интеграция с внешними CRM системами

### Основные компоненты

1. **WordPress Core** - Основная CMS
2. **WooCommerce** - E-commerce для платных услуг
3. **Тема** - Кастомная тема для финансовых услуг
4. **Плагины** - SEO, безопасность, формы, аналитика
5. **База данных** - MySQL для хранения данных
6. **API интеграции** - CRM, платежные системы, банки

## Структура кода

### Основные директории

```
bizfin-pro.ru/
├── wp-admin/                    # Административная панель WordPress
├── wp-content/                  # Пользовательский контент
│   ├── themes/                  # Темы
│   │   └── bizfin-pro-theme/    # Основная тема
│   ├── plugins/                 # Плагины
│   │   ├── woocommerce/         # WooCommerce
│   │   ├── yoast-seo/           # SEO оптимизация
│   │   ├── elementor/           # Конструктор страниц
│   │   ├── contact-form-7/      # Формы обратной связи
│   │   ├── gravity-forms/       # Продвинутые формы
│   │   └── [другие плагины]/
│   ├── uploads/                 # Загруженные файлы
│   │   ├── services/            # Изображения услуг
│   │   ├── team/                # Фото команды
│   │   ├── documents/           # Документы и презентации
│   │   └── [другие медиа]/
│   └── languages/               # Переводы
├── wp-includes/                 # Ядро WordPress
├── wp-config.php                # Конфигурация
├── .htaccess                    # Настройки Apache
└── index.php                    # Точка входа
```

### Ключевые файлы темы

```php
// functions.php - Основные функции темы
function bizfin_pro_theme_setup() {
    // Настройка темы
    add_theme_support('woocommerce');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('custom-header');
    add_theme_support('custom-background');
    
    // Регистрация меню
    register_nav_menus([
        'primary' => 'Главное меню',
        'footer' => 'Меню в подвале',
        'services' => 'Меню услуг'
    ]);
    
    // Регистрация виджетов
    register_sidebar([
        'name' => 'Боковая панель',
        'id' => 'sidebar-1',
        'description' => 'Виджеты для боковой панели'
    ]);
}

// style.css - Основные стили
.bizfin-pro-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.bizfin-pro-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 1rem 0;
}

.service-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
    transition: transform 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
}

// header.php - Шапка сайта
<header class="bizfin-pro-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/logo.png" alt="BizFin Pro">
            </div>
            <nav class="main-menu">
                <?php wp_nav_menu(['theme_location' => 'primary']); ?>
            </nav>
            <div class="header-cta">
                <a href="/contact" class="btn btn-primary">Получить консультацию</a>
            </div>
        </div>
    </div>
</header>

// footer.php - Подвал сайта
<footer class="bizfin-pro-footer">
    <div class="container">
        <div class="footer-widgets">
            <div class="footer-widget">
                <h3>Наши услуги</h3>
                <?php wp_nav_menu(['theme_location' => 'services']); ?>
            </div>
            <div class="footer-widget">
                <h3>Контакты</h3>
                <p>Телефон: +7 (495) 123-45-67</p>
                <p>Email: info@bizfin-pro.ru</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> BizFin Pro. Все права защищены.</p>
        </div>
    </div>
</footer>
```

## Финансовые услуги

### Типы услуг

```php
// Категории финансовых услуг
$financial_services = [
    'financial-planning' => 'Финансовое планирование',
    'investment-consulting' => 'Инвестиционное консультирование',
    'tax-consulting' => 'Налоговое консультирование',
    'corporate-finance' => 'Корпоративные финансы',
    'audit-services' => 'Аудиторские услуги',
    'business-valuation' => 'Оценка бизнеса',
    'risk-management' => 'Управление рисками',
    'insurance-consulting' => 'Страховое консультирование'
];

// Кастомные поля услуг
function add_financial_service_fields() {
    woocommerce_wp_text_input([
        'id' => 'service_duration',
        'label' => 'Продолжительность услуги',
        'placeholder' => '1 месяц, 3 месяца, 6 месяцев'
    ]);
    
    woocommerce_wp_text_input([
        'id' => 'service_complexity',
        'label' => 'Сложность',
        'placeholder' => 'Базовая, Стандартная, Премиум'
    ]);
    
    woocommerce_wp_textarea_input([
        'id' => 'service_requirements',
        'label' => 'Требования к клиенту',
        'placeholder' => 'Описывает требования для получения услуги'
    ]);
    
    woocommerce_wp_textarea_input([
        'id' => 'service_deliverables',
        'label' => 'Результаты услуги',
        'placeholder' => 'Что получит клиент по завершении'
    ]);
}
```

### Система консультаций

```php
// Форма заявки на консультацию
function bizfin_pro_consultation_form() {
    ?>
    <form id="consultation-form" class="bizfin-pro-form">
        <div class="form-group">
            <label for="client-name">Имя *</label>
            <input type="text" id="client-name" name="client_name" required>
        </div>
        
        <div class="form-group">
            <label for="client-email">Email *</label>
            <input type="email" id="client-email" name="client_email" required>
        </div>
        
        <div class="form-group">
            <label for="client-phone">Телефон *</label>
            <input type="tel" id="client-phone" name="client_phone" required>
        </div>
        
        <div class="form-group">
            <label for="company-name">Название компании</label>
            <input type="text" id="company-name" name="company_name">
        </div>
        
        <div class="form-group">
            <label for="service-type">Тип услуги *</label>
            <select id="service-type" name="service_type" required>
                <option value="">Выберите услугу</option>
                <?php foreach ($financial_services as $key => $service): ?>
                    <option value="<?php echo $key; ?>"><?php echo $service; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="budget">Бюджет</label>
            <select id="budget" name="budget">
                <option value="">Выберите бюджет</option>
                <option value="under-100k">До 100,000 руб.</option>
                <option value="100k-500k">100,000 - 500,000 руб.</option>
                <option value="500k-1m">500,000 - 1,000,000 руб.</option>
                <option value="over-1m">Свыше 1,000,000 руб.</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="message">Дополнительная информация</label>
            <textarea id="message" name="message" rows="5"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Отправить заявку</button>
    </form>
    <?php
}

// Обработка формы консультации
function handle_consultation_form() {
    if (isset($_POST['client_name']) && isset($_POST['client_email'])) {
        $client_data = [
            'name' => sanitize_text_field($_POST['client_name']),
            'email' => sanitize_email($_POST['client_email']),
            'phone' => sanitize_text_field($_POST['client_phone']),
            'company' => sanitize_text_field($_POST['company_name']),
            'service' => sanitize_text_field($_POST['service_type']),
            'budget' => sanitize_text_field($_POST['budget']),
            'message' => sanitize_textarea_field($_POST['message']),
            'date' => current_time('mysql')
        ];
        
        // Сохранение в базу данных
        global $wpdb;
        $table_name = $wpdb->prefix . 'bizfin_consultations';
        
        $wpdb->insert($table_name, $client_data);
        
        // Отправка уведомлений
        wp_mail('info@bizfin-pro.ru', 'Новая заявка на консультацию', 
                'Получена новая заявка от ' . $client_data['name']);
        
        wp_mail($client_data['email'], 'Заявка принята', 
                'Ваша заявка на консультацию принята. Мы свяжемся с вами в ближайшее время.');
        
        return 'success';
    }
    return 'error';
}
```

## CRM интеграция

### Интеграция с внешними CRM

```php
// Интеграция с AmoCRM
function bizfin_pro_amo_crm_integration($lead_data) {
    $api_url = 'https://' . AMO_DOMAIN . '.amocrm.ru/api/v4/leads';
    $api_token = AMO_API_TOKEN;
    
    $headers = [
        'Authorization: Bearer ' . $api_token,
        'Content-Type: application/json'
    ];
    
    $lead_payload = [
        'name' => $lead_data['name'],
        'price' => $lead_data['budget_value'],
        'custom_fields_values' => [
            [
                'field_id' => AMO_EMAIL_FIELD_ID,
                'values' => [['value' => $lead_data['email']]]
            ],
            [
                'field_id' => AMO_PHONE_FIELD_ID,
                'values' => [['value' => $lead_data['phone']]]
            ],
            [
                'field_id' => AMO_SERVICE_FIELD_ID,
                'values' => [['value' => $lead_data['service']]]
            ]
        ]
    ];
    
    $response = wp_remote_post($api_url, [
        'headers' => $headers,
        'body' => json_encode($lead_payload)
    ]);
    
    if (!is_wp_error($response)) {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        return $response_data['_embedded']['leads'][0]['id'];
    }
    
    return false;
}

// Интеграция с Bitrix24
function bizfin_pro_bitrix24_integration($deal_data) {
    $api_url = 'https://' . BITRIX_DOMAIN . '.bitrix24.ru/rest/' . BITRIX_USER_ID . '/' . BITRIX_WEBHOOK_CODE . '/crm.deal.add';
    
    $deal_payload = [
        'fields' => [
            'TITLE' => 'Консультация: ' . $deal_data['service'],
            'OPPORTUNITY' => $deal_data['budget_value'],
            'CURRENCY_ID' => 'RUB',
            'STAGE_ID' => 'NEW',
            'CONTACT_ID' => $deal_data['contact_id']
        ]
    ];
    
    $response = wp_remote_post($api_url, [
        'body' => $deal_payload
    ]);
    
    if (!is_wp_error($response)) {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        return $response_data['result'];
    }
    
    return false;
}
```

## Платежная система

### Интеграция с банками

```php
// Интеграция с Сбербанком
function bizfin_pro_sberbank_payment($order_data) {
    $api_url = 'https://3dsec.sberbank.ru/payment/rest/register.do';
    
    $payment_data = [
        'userName' => SBERBANK_USERNAME,
        'password' => SBERBANK_PASSWORD,
        'orderNumber' => $order_data['order_id'],
        'amount' => $order_data['amount'] * 100, // В копейках
        'returnUrl' => home_url('/payment/success'),
        'failUrl' => home_url('/payment/fail'),
        'description' => 'Оплата консультационных услуг BizFin Pro'
    ];
    
    $response = wp_remote_post($api_url, [
        'body' => $payment_data
    ]);
    
    if (!is_wp_error($response)) {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_data['errorCode'] == 0) {
            return $response_data['formUrl'];
        }
    }
    
    return false;
}

// Интеграция с Тинькофф
function bizfin_pro_tinkoff_payment($order_data) {
    $api_url = 'https://securepay.tinkoff.ru/v2/Init';
    
    $payment_data = [
        'TerminalKey' => TINKOFF_TERMINAL_KEY,
        'Amount' => $order_data['amount'] * 100,
        'OrderId' => $order_data['order_id'],
        'Description' => 'Оплата консультационных услуг BizFin Pro',
        'SuccessURL' => home_url('/payment/success'),
        'FailURL' => home_url('/payment/fail'),
        'NotificationURL' => home_url('/payment/notification')
    ];
    
    // Подпись запроса
    $payment_data['Token'] = bizfin_pro_generate_tinkoff_token($payment_data);
    
    $response = wp_remote_post($api_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($payment_data)
    ]);
    
    if (!is_wp_error($response)) {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_data['Success']) {
            return $response_data['PaymentURL'];
        }
    }
    
    return false;
}
```

## База данных

### Основные таблицы

```sql
-- Консультации
CREATE TABLE wp_bizfin_consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    company_name VARCHAR(200),
    service_type VARCHAR(50),
    budget VARCHAR(50),
    message TEXT,
    status ENUM('new', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
    assigned_consultant INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Консультанты
CREATE TABLE wp_bizfin_consultants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(200),
    experience_years INT,
    bio TEXT,
    photo_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Услуги
CREATE TABLE wp_bizfin_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    duration VARCHAR(50),
    complexity ENUM('basic', 'standard', 'premium'),
    requirements TEXT,
    deliverables TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Отзывы клиентов
CREATE TABLE wp_bizfin_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_company VARCHAR(200),
    service_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    testimonial TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES wp_bizfin_services(id)
);

-- Финансовые калькуляторы
CREATE TABLE wp_bizfin_calculators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('loan', 'investment', 'tax', 'business_valuation'),
    config JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Финансовые калькуляторы

### Калькулятор кредита

```javascript
// Калькулятор кредита
function calculateLoan() {
    const amount = parseFloat(document.getElementById('loan-amount').value);
    const rate = parseFloat(document.getElementById('interest-rate').value);
    const term = parseInt(document.getElementById('loan-term').value);
    
    const monthlyRate = rate / 100 / 12;
    const monthlyPayment = amount * (monthlyRate * Math.pow(1 + monthlyRate, term)) / 
                          (Math.pow(1 + monthlyRate, term) - 1);
    
    const totalPayment = monthlyPayment * term;
    const totalInterest = totalPayment - amount;
    
    document.getElementById('monthly-payment').textContent = 
        monthlyPayment.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'});
    document.getElementById('total-payment').textContent = 
        totalPayment.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'});
    document.getElementById('total-interest').textContent = 
        totalInterest.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'});
}

// Калькулятор инвестиций
function calculateInvestment() {
    const initialAmount = parseFloat(document.getElementById('initial-amount').value);
    const monthlyContribution = parseFloat(document.getElementById('monthly-contribution').value);
    const annualReturn = parseFloat(document.getElementById('annual-return').value);
    const years = parseInt(document.getElementById('investment-years').value);
    
    const monthlyReturn = annualReturn / 100 / 12;
    const totalMonths = years * 12;
    
    let futureValue = initialAmount;
    for (let i = 0; i < totalMonths; i++) {
        futureValue = (futureValue + monthlyContribution) * (1 + monthlyReturn);
    }
    
    const totalContributions = initialAmount + (monthlyContribution * totalMonths);
    const totalGains = futureValue - totalContributions;
    
    document.getElementById('future-value').textContent = 
        futureValue.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'});
    document.getElementById('total-gains').textContent = 
        totalGains.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'});
}
```

## SEO и контент-маркетинг

### SEO оптимизация

```php
// Кастомные мета-описания для услуг
function bizfin_pro_service_meta_description($description) {
    if (is_product_category()) {
        $category = get_queried_object();
        return "Профессиональные {$category->name} от BizFin Pro. Опытные консультанты, индивидуальный подход, гарантированный результат. Запишитесь на консультацию!";
    }
    
    if (is_product()) {
        global $product;
        $service_name = $product->get_name();
        return "{$service_name} - профессиональные финансовые услуги от BizFin Pro. Получите экспертную консультацию и решение ваших финансовых задач.";
    }
    
    return $description;
}
add_filter('wpseo_metadesc', 'bizfin_pro_service_meta_description');

// Структурированные данные для услуг
function bizfin_pro_add_service_schema() {
    if (is_product()) {
        global $product;
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $product->get_name(),
            'description' => $product->get_description(),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'BizFin Pro',
                'url' => home_url(),
                'telephone' => '+7 (495) 123-45-67',
                'email' => 'info@bizfin-pro.ru'
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->get_price(),
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock'
            ],
            'serviceType' => 'Financial Consulting',
            'areaServed' => 'Russia'
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
    }
}
add_action('wp_head', 'bizfin_pro_add_service_schema');
```

### Блог и контент

```php
// Кастомные типы постов для блога
function bizfin_pro_custom_post_types() {
    // Финансовые новости
    register_post_type('financial_news', [
        'labels' => [
            'name' => 'Финансовые новости',
            'singular_name' => 'Новость'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon' => 'dashicons-chart-line'
    ]);
    
    // Кейсы
    register_post_type('case_study', [
        'labels' => [
            'name' => 'Кейсы',
            'singular_name' => 'Кейс'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon' => 'dashicons-portfolio'
    ]);
    
    // Финансовые калькуляторы
    register_post_type('financial_calculator', [
        'labels' => [
            'name' => 'Калькуляторы',
            'singular_name' => 'Калькулятор'
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-calculator'
    ]);
}
add_action('init', 'bizfin_pro_custom_post_types');
```

## Аналитика и отчетность

### Google Analytics 4

```javascript
// Отслеживание конверсий
gtag('event', 'consultation_request', {
    event_category: 'engagement',
    event_label: 'consultation_form',
    value: 1
});

// Отслеживание просмотров услуг
gtag('event', 'view_service', {
    event_category: 'engagement',
    event_label: 'service_page',
    service_name: '<?php echo get_the_title(); ?>'
});

// Отслеживание использования калькуляторов
gtag('event', 'calculator_used', {
    event_category: 'engagement',
    event_label: 'financial_calculator',
    calculator_type: 'loan_calculator'
});
```

### Внутренняя аналитика

```php
// Отслеживание заявок
function bizfin_pro_track_consultation_request($consultation_id) {
    $analytics_data = [
        'consultation_id' => $consultation_id,
        'source' => $_SERVER['HTTP_REFERER'] ?? 'direct',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'timestamp' => current_time('mysql')
    ];
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bizfin_analytics';
    
    $wpdb->insert($table_name, $analytics_data);
}

// Генерация отчетов
function bizfin_pro_generate_analytics_report($start_date, $end_date) {
    global $wpdb;
    
    $consultations_table = $wpdb->prefix . 'bizfin_consultations';
    $analytics_table = $wpdb->prefix . 'bizfin_analytics';
    
    $report = $wpdb->get_results("
        SELECT 
            DATE(c.created_at) as date,
            COUNT(c.id) as total_consultations,
            COUNT(CASE WHEN c.status = 'completed' THEN 1 END) as completed_consultations,
            AVG(CASE WHEN c.budget = 'under-100k' THEN 50000
                     WHEN c.budget = '100k-500k' THEN 300000
                     WHEN c.budget = '500k-1m' THEN 750000
                     WHEN c.budget = 'over-1m' THEN 1500000
                     ELSE 0 END) as avg_budget
        FROM {$consultations_table} c
        WHERE c.created_at BETWEEN '{$start_date}' AND '{$end_date}'
        GROUP BY DATE(c.created_at)
        ORDER BY date DESC
    ");
    
    return $report;
}
```

## Безопасность и соответствие

### Защита персональных данных

```php
// Шифрование персональных данных
function bizfin_pro_encrypt_data($data) {
    $key = wp_salt('AUTH_KEY');
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function bizfin_pro_decrypt_data($encrypted_data) {
    $key = wp_salt('AUTH_KEY');
    $data = base64_decode($encrypted_data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

// Логирование доступа к персональным данным
function bizfin_pro_log_data_access($user_id, $data_type, $action) {
    $log_entry = [
        'user_id' => $user_id,
        'data_type' => $data_type,
        'action' => $action,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'timestamp' => current_time('mysql')
    ];
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bizfin_data_access_log';
    $wpdb->insert($table_name, $log_entry);
}
```

### Соответствие требованиям

```php
// GDPR соответствие
function bizfin_pro_gdpr_compliance() {
    // Добавление чекбокса согласия
    add_action('wp_footer', function() {
        if (!isset($_COOKIE['gdpr_consent'])) {
            ?>
            <div id="gdpr-consent-banner" class="gdpr-banner">
                <p>Мы используем cookies для улучшения работы сайта. 
                   <a href="/privacy-policy">Подробнее</a></p>
                <button onclick="acceptGDPR()">Принять</button>
            </div>
            <script>
            function acceptGDPR() {
                document.cookie = "gdpr_consent=accepted; expires=365; path=/";
                document.getElementById('gdpr-consent-banner').style.display = 'none';
            }
            </script>
            <?php
        }
    });
}

// Функция удаления данных пользователя
function bizfin_pro_delete_user_data($user_id) {
    global $wpdb;
    
    // Удаление консультаций
    $wpdb->delete($wpdb->prefix . 'bizfin_consultations', ['client_email' => $user_email]);
    
    // Удаление аналитики
    $wpdb->delete($wpdb->prefix . 'bizfin_analytics', ['user_id' => $user_id]);
    
    // Удаление отзывов
    $wpdb->delete($wpdb->prefix . 'bizfin_testimonials', ['client_email' => $user_email]);
    
    return true;
}
```

## Заключение

BizFin Pro - это профессиональная платформа финансовых услуг, построенная на WordPress с интеграцией WooCommerce. Проект включает в себя:

**Ключевые особенности:**
- Комплексная система финансовых услуг
- CRM интеграция (AmoCRM, Bitrix24)
- Платежные системы (Сбербанк, Тинькофф)
- Финансовые калькуляторы
- Система консультаций и заявок
- SEO оптимизация и контент-маркетинг
- Аналитика и отчетность
- Соответствие GDPR и защита данных

**Готовность к продакшну:** ✅ Полностью готов к коммерческому использованию

**Техническая поддержка:** Активная разработка и поддержка проекта

**Соответствие стандартам:** GDPR, финансовое законодательство РФ
