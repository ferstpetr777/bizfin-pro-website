<?php
/**
 * Добавление стилей для статьи по 44-ФЗ
 */

// Подключаем WordPress
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

// CSS стили для статьи
$css_styles = '
<style>
:root {
    --orange: #FF6B00;
    --orange-2: #FF9A3C;
    --text: #0F172A;
    --text-muted: #556070;
    --surface: #FFFFFF;
    --surface-2: #F7F7F7;
}

.article-content .intro {
    background: var(--surface-2);
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid var(--orange);
}

.article-content .example {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #0073aa;
}

.article-content .toc {
    background: var(--surface-2);
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.article-content .toc ul {
    list-style: none;
    padding-left: 0;
}

.article-content .toc li {
    margin: 8px 0;
}

.article-content .toc a {
    color: var(--orange);
    text-decoration: none;
    font-weight: 500;
}

.article-content .toc a:hover {
    text-decoration: underline;
}

.article-content .article-image {
    width: 100%;
    max-width: 800px;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.8);
    margin: 20px 0;
    aspect-ratio: 16/9;
    object-fit: cover;
}

.article-content .checklist {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid #dee2e6;
}

.article-content .checklist h4 {
    color: var(--orange);
    margin-top: 0;
}

.article-content .checklist ul {
    margin: 0;
    padding-left: 20px;
}

.article-content .checklist li {
    margin: 8px 0;
}

.article-content .cta-block {
    background: linear-gradient(135deg, var(--orange), var(--orange-2));
    color: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    margin: 30px 0;
}

.article-content .cta-block h3 {
    color: white;
    margin-top: 0;
}

.article-content .cta-button {
    background: white;
    color: var(--orange);
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 5px 10px;
}

.article-content .faq {
    background: var(--surface-2);
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.article-content .faq h4 {
    color: var(--orange);
    margin-top: 0;
}

.article-content .internal-link {
    color: var(--orange);
    text-decoration: none;
    font-weight: 500;
}

.article-content .internal-link:hover {
    text-decoration: underline;
}

.article-content h2 {
    color: var(--text);
    font-size: 2rem;
    margin-top: 40px;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--orange);
    padding-bottom: 10px;
}

.article-content h3 {
    color: var(--text);
    font-size: 1.5rem;
    margin-top: 30px;
    margin-bottom: 15px;
}

.article-content h4 {
    color: var(--text);
    font-size: 1.25rem;
    margin-top: 25px;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .article-content .intro {
        padding: 20px;
    }
    
    .article-content h2 {
        font-size: 1.75rem;
    }
    
    .article-content .cta-button {
        display: block;
        margin: 10px 0;
    }
}
</style>
';

// Добавляем стили в head
add_action('wp_head', function() use ($css_styles) {
    if (is_single(2889)) { // ID статьи
        echo $css_styles;
    }
});

echo "✅ Стили для статьи по 44-ФЗ добавлены!\n";
echo "🎨 Стили будут применены к статье ID: 2889\n";
echo "🔗 URL статьи: https://bizfin-pro.ru/bankovskaya-garantiya-po-44-fz-polnoe-rukovodstvo-dlya-tenderschikov-2025/\n";
?>
