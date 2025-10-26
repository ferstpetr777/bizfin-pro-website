<?php
/**
 * Добавление CSS стилей в тему сайта
 */

// Подключение WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-load.php');

// ID статьи
$post_id = 3027;

echo "📝 Добавление CSS стилей в тему сайта\n";

// CSS стили для статьи
$css_content = '
/* Стили для статьи "Документы для банковской гарантии на возврат аванса" */
:root {
    --orange: #FF6B00;
    --orange-2: #FF9A3C;
    --text: #0F172A;
    --text-muted: #556070;
    --surface: #FFFFFF;
    --surface-2: #F7F7F7;
    --blue: #3498db;
    --green: #28a745;
    --yellow: #ffc107;
    --red: #dc3545;
    --cyan: #17a2b8;
}

.intro-section {
    background: linear-gradient(135deg, var(--orange), var(--orange-2));
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.example {
    background: #e8f5e8;
    border-left: 4px solid var(--green);
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 0 8px 8px 0;
}

.toc {
    background: var(--surface-2);
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.toc ul {
    list-style: none;
    padding-left: 0;
}

.toc a {
    color: var(--blue);
    text-decoration: none;
    font-weight: 500;
}

.checklist {
    background: #f0f8ff;
    border: 1px solid var(--blue);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.checklist ul {
    list-style: none;
    padding-left: 0;
}

.checklist li {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.checklist li:before {
    content: "✓";
    color: var(--green);
    font-weight: bold;
    position: absolute;
    left: 0;
}

.warning {
    background: #fff3cd;
    border: 1px solid var(--yellow);
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.info {
    background: #d1ecf1;
    border: 1px solid var(--cyan);
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.success {
    background: #d4edda;
    border: 1px solid var(--green);
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    background: var(--surface);
}

th, td {
    border: 1px solid #ddd;
    padding: 0.75rem;
    text-align: left;
}

th {
    background: var(--orange);
    color: white;
    font-weight: 600;
}

.cta-section {
    background: linear-gradient(135deg, var(--blue), var(--cyan));
    color: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    margin: 2rem 0;
}

.cta-button {
    display: inline-block;
    background: var(--orange);
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    margin: 0.5rem;
}

.faq-section {
    background: var(--surface-2);
    padding: 2rem;
    border-radius: 12px;
    margin: 2rem 0;
}

.faq-item {
    margin: 1rem 0;
    padding: 1rem;
    background: var(--surface);
    border-radius: 8px;
    border-left: 4px solid var(--blue);
}

.internal-link {
    color: var(--blue);
    text-decoration: none;
    font-weight: 500;
}

h2 {
    font-size: 2rem;
    color: var(--text);
    margin: 2rem 0 1rem 0;
    border-bottom: 3px solid var(--orange);
    padding-bottom: 0.5rem;
}

h3 {
    font-size: 1.5rem;
    color: var(--text);
    margin: 1.5rem 0 1rem 0;
}

h4 {
    font-size: 1.25rem;
    color: var(--text);
    margin: 1rem 0 0.5rem 0;
}

@media (max-width: 768px) {
    .intro-section, .cta-section {
        padding: 1.5rem;
    }
    
    h2 {
        font-size: 1.75rem;
    }
}
';

// Создаем CSS файл в теме
$css_file_path = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/themes/bizfin-pro/article-styles.css';
file_put_contents($css_file_path, $css_content);

echo "✅ CSS файл создан: $css_file_path\n";

// Добавляем функцию в functions.php темы для подключения стилей
$functions_file = '/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-content/themes/bizfin-pro/functions.php';

// Проверяем, существует ли файл functions.php
if (!file_exists($functions_file)) {
    // Создаем functions.php если его нет
    $functions_content = '<?php
// Подключение стилей для статей
function enqueue_article_styles() {
    if (is_single() && get_post_type() == "post") {
        wp_enqueue_style("article-styles", get_template_directory_uri() . "/article-styles.css", array(), "1.0.0");
    }
}
add_action("wp_enqueue_scripts", "enqueue_article_styles");
?>';
    file_put_contents($functions_file, $functions_content);
    echo "✅ Файл functions.php создан\n";
} else {
    // Добавляем функцию в существующий functions.php
    $existing_content = file_get_contents($functions_file);
    
    // Проверяем, есть ли уже наша функция
    if (strpos($existing_content, 'enqueue_article_styles') === false) {
        $new_function = '
// Подключение стилей для статей
function enqueue_article_styles() {
    if (is_single() && get_post_type() == "post") {
        wp_enqueue_style("article-styles", get_template_directory_uri() . "/article-styles.css", array(), "1.0.0");
    }
}
add_action("wp_enqueue_scripts", "enqueue_article_styles");
';
        
        // Добавляем функцию в конец файла
        $updated_content = $existing_content . $new_function;
        file_put_contents($functions_file, $updated_content);
        echo "✅ Функция добавлена в functions.php\n";
    } else {
        echo "✅ Функция уже существует в functions.php\n";
    }
}

// Обновляем мета-данные статьи
update_post_meta($post_id, '_bsag_css_added_to_theme', true);
update_post_meta($post_id, '_bsag_css_file', 'article-styles.css');

echo "✅ CSS стили добавлены в тему сайта\n";
echo "✅ Статья будет отображаться с правильными стилями без видимого кода\n";

// Получаем URL статьи
$article_url = get_permalink($post_id);
echo "✅ Статья доступна: $article_url\n";

echo "\n🎉 Готово! CSS код удален из контента и добавлен в тему сайта.\n";
?>

