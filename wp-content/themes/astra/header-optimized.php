<?php
/**
 * Оптимизированный header для быстрой загрузки контента
 * Откладывает блокирующие скрипты и обеспечивает быструю загрузку
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">

<!-- КРИТИЧЕСКИ ВАЖНЫЕ МЕТА-ТЕГИ (загружаются сразу) -->
<?php
// SEO мета-теги
if (is_single() || is_page()) {
    $post_id = get_the_ID();
    $title = get_the_title();
    $description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    if (empty($description)) {
        $description = wp_trim_words(get_the_excerpt(), 20);
    }
    $canonical = get_permalink();
    $og_image = get_the_post_thumbnail_url($post_id, 'large');
    
    echo '<title>' . esc_html($title) . ' | ' . get_bloginfo('name') . '</title>' . "\n";
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    
    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    if ($og_image) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    }
}
?>

<!-- КРИТИЧЕСКИ ВАЖНЫЕ CSS (inline для мгновенной загрузки) -->
<style>
/* Базовые стили для быстрого отображения */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
    margin: 0;
    padding: 0;
    background: #fff;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.article-header {
    padding: 40px 0;
    border-bottom: 1px solid #eee;
    margin-bottom: 40px;
}

.article-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 20px 0;
    line-height: 1.2;
}

.article-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
    max-width: 800px;
    margin: 0 auto;
}

.article-content h1, .article-content h2, .article-content h3 {
    color: #1a1a1a;
    margin: 40px 0 20px 0;
    font-weight: 600;
}

.article-content h1 { font-size: 2rem; }
.article-content h2 { font-size: 1.6rem; }
.article-content h3 { font-size: 1.3rem; }

.article-content p {
    margin-bottom: 20px;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.article-content ul, .article-content ol {
    margin: 20px 0;
    padding-left: 30px;
}

.article-content li {
    margin-bottom: 8px;
}

/* Скрываем элементы до загрузки JS */
.social-share, .comments-section, .related-posts {
    display: none;
}

/* Показываем после загрузки JS */
.js-loaded .social-share, .js-loaded .comments-section, .js-loaded .related-posts {
    display: block;
}

/* Анимация появления */
.fade-in {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.js-loaded .fade-in {
    opacity: 1;
    transform: translateY(0);
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .article-title { font-size: 2rem; }
    .article-content { font-size: 1rem; }
    .container { padding: 0 15px; }
}
</style>

<!-- ПРЕДЗАГРУЗКА КРИТИЧЕСКИ ВАЖНЫХ РЕСУРСОВ -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="//mc.yandex.ru">
<link rel="dns-prefetch" href="//www.google-analytics.com">

<!-- ОТЛОЖЕННАЯ ЗАГРУЗКА НЕКРИТИЧЕСКИХ CSS -->
<link rel="preload" href="<?php echo get_template_directory_uri(); ?>/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css"></noscript>

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- ОСНОВНОЙ КОНТЕНТ (загружается сразу) -->
<div class="container">
    <?php if (is_single() || is_page()) : ?>
    <article class="article-content fade-in">
        <header class="article-header">
            <h1 class="article-title"><?php the_title(); ?></h1>
            <div class="article-meta">
                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                <?php if (get_the_author()) : ?>
                    | Автор: <?php the_author(); ?>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="article-body">
            <?php
            // Показываем контент сразу
            if (have_posts()) :
                while (have_posts()) : the_post();
                    the_content();
                endwhile;
            endif;
            ?>
        </div>
    </article>
    <?php endif; ?>
</div>

<!-- ОТЛОЖЕННАЯ ЗАГРУЗКА СКРИПТОВ -->
<script>
// Функция для отложенной загрузки скриптов
function loadScript(src, callback) {
    var script = document.createElement('script');
    script.src = src;
    script.async = true;
    if (callback) script.onload = callback;
    document.head.appendChild(script);
}

// Функция для отложенной загрузки CSS
function loadCSS(href) {
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

// Загружаем скрипты после загрузки основного контента
document.addEventListener('DOMContentLoaded', function() {
    // Показываем скрытые элементы
    document.body.classList.add('js-loaded');
    
    // Загружаем некритические скрипты с задержкой
    setTimeout(function() {
        // WordPress скрипты
        loadScript('<?php echo includes_url(); ?>js/jquery/jquery.min.js');
        loadScript('<?php echo includes_url(); ?>js/jquery/jquery-migrate.min.js');
        
        // Yandex Metrika (отложенная загрузка)
        loadScript('https://bizfin-pro.ru/wp-content/plugins/wp-yandex-metrika/assets/YmEc.min.js');
        
        // Другие плагины
        loadScript('<?php echo get_template_directory_uri(); ?>/js/main.js');
        
    }, 1000); // Задержка 1 секунда
    
    // Загружаем аналитику еще позже
    setTimeout(function() {
        // Yandex Metrika инициализация
        if (typeof ym !== 'undefined') {
            ym(104811275, 'init', {
                clickmap: true,
                trackLinks: true,
                accurateTrackBounce: true,
                webvisor: true
            });
        }
    }, 2000); // Задержка 2 секунды
});

// Предзагрузка изображений при прокрутке
function preloadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Запускаем предзагрузку изображений
document.addEventListener('DOMContentLoaded', preloadImages);
</script>

<!-- ОТЛОЖЕННАЯ ЗАГРУЗКА FOOTER -->
<?php wp_footer(); ?>

</body>
</html>