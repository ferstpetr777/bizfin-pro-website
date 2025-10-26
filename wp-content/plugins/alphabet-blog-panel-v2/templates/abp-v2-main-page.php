<?php
/**
 * Шаблон для главной страницы блога v2
 * URL: /blog2/
 */
if (!defined('ABSPATH')) exit;

// Подключаем CSS и JS
wp_enqueue_style('abp-v2-css', plugins_url('assets/css/abp-v2.css', dirname(__FILE__)), [], '2.0.0');
wp_enqueue_script('abp-v2-js', plugins_url('assets/js/abp-v2.js', dirname(__FILE__)), ['wp-url'], '2.0.0', true);

// Локализация скрипта для главной страницы
wp_localize_script('abp-v2-js', 'ABP_V2', [
    'ajax' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce(ABP_V2_Plugin::NONCE),
    'slug' => ABP_V2_Plugin::SLUG,
]);

get_header();

// Загружаем статьи на букву "А" по умолчанию
$default_letter = 'А';
$q = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'meta_key' => ABP_V2_Plugin::META_KEY,
    'meta_value' => $default_letter,
    'orderby' => 'title',
    'order' => 'ASC',
    'posts_per_page' => 12,
]);
?>

<main class="abp-v2-main-page">
    <!-- Алфавитное меню с заголовком и поиском -->
    <div class="abp-v2-main-header">
        <div class="abp-v2-main-header-content">
            <div class="abp-v2-main-title">
                <h1>Блог</h1>
            </div>
            <div class="abp-v2-main-subtitle">
                <p>Найдите в алфавитном порядке</p>
            </div>
            <div class="abp-v2-main-search">
                <input type="text" id="abp-v2-search-input" placeholder="Поиск по ключевым словам..." />
                <button id="abp-v2-search-btn" type="button">🔍</button>
            </div>
        </div>
        
        <!-- Алфавитная панель -->
        <div class="abp-v2-main-alphabet">
            <?php echo do_shortcode('[abp_v2_alphabet_only]'); ?>
        </div>
    </div>

    <!-- Контентная область -->
    <div class="abp-v2-main-content">
        <div class="abp-v2-main-posts">
            <div class="abp-v2-main-posts-header">
                <h2>Статьи на букву «<?php echo esc_html($default_letter); ?>»</h2>
                <span class="abp-v2-main-posts-count">Найдено: <?php echo $q->found_posts; ?> статей</span>
            </div>
            
            <div class="abp-v2-main-posts-grid">
                <?php if ($q->have_posts()): ?>
                    <?php while ($q->have_posts()): $q->the_post(); ?>
                        <article class="abp-v2-main-post-card">
                            <?php 
                            // DEBUG: Проверка featured image
                            $post_id = get_the_ID();
                            $has_thumb = has_post_thumbnail();
                            $thumb_id = get_post_thumbnail_id();
                            $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
                            echo "<!-- DEBUG Post ID: $post_id, Has thumbnail: " . ($has_thumb ? 'YES' : 'NO') . ", Thumb ID: $thumb_id, URL: $thumb_url -->";
                            ?>
                            <?php if (has_post_thumbnail()): ?>
                                <div class="abp-v2-main-post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', ['loading' => 'lazy']); ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- DEBUG: No thumbnail for post <?php echo $post_id; ?> -->
                            <?php endif; ?>
                            <h3 class="abp-v2-main-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="abp-v2-main-post-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                            </div>
                            <div class="abp-v2-main-post-meta">
                                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="abp-v2-main-no-posts">
                        <p>На букву «<?php echo esc_html($default_letter); ?>» пока нет статей.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php 
wp_reset_postdata();
get_footer(); 
?>



