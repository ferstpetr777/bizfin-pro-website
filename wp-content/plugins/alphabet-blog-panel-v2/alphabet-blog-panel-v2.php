<?php
/**
 * Plugin Name: Alphabet Blog Panel v2
 * Description: Простой и надежный алфавитный рубрикатор блога с AJAX навигацией
 * Version: 2.0.0
 * Author: BizFin Pro Team
 * Text Domain: abp-v2
 */

if (!defined('ABSPATH')) exit;

class ABP_V2_Plugin {
    const META_KEY = 'abp_first_letter';
    const NONCE = 'abp_v2_nonce';
    const SLUG = 'blog';
    const QVAR = 'abp_letter';
    const VERSION = '2.0.0';

    public function __construct() {
        // Хуки WordPress
        add_action('save_post', [$this, 'save_first_letter'], 20, 2);
        add_shortcode('abp_v2_alphabet', [$this, 'shortcode_output']);
        add_shortcode('abp_v2_alphabet_only', [$this, 'shortcode_alphabet_only']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_abp_v2_fetch_posts', [$this, 'ajax_fetch_posts']);
        add_action('wp_ajax_nopriv_abp_v2_fetch_posts', [$this, 'ajax_fetch_posts']);
        add_action('wp_ajax_abp_v2_search', [$this, 'ajax_search']);
        add_action('wp_ajax_nopriv_abp_v2_search', [$this, 'ajax_search']);
        
        // Rewrite rules
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'register_query_var']);
        add_filter('template_include', [$this, 'intercept_template']);
        
        // SEO
        add_filter('pre_get_document_title', [$this, 'seo_title']);
        add_action('wp_head', [$this, 'seo_meta']);
        
        // Подключаем Gutenberg блок
        require_once __DIR__ . '/blocks/alphabet-block.php';
        
        // Добавляем алфавитную панель на страницы статей после шапки сайта (отключено в пользу блока)
        // add_action('wp_head', [$this, 'add_alphabet_panel_to_single_post'], 999);
        
        // Активация
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        
        // Создание страницы
        add_action('admin_init', [$this, 'maybe_create_page']);
    }

    public static function activate() {
        (new self())->add_rewrite_rules();
        flush_rewrite_rules();
        self::create_blog_page();
        self::process_existing_posts();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_blog_page() {
        $page_exists = get_page_by_path(self::SLUG . '2');
        if (!$page_exists) {
            wp_insert_post([
                'post_title' => 'Блог 2',
                'post_content' => '[abp_v2_alphabet]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => self::SLUG . '2',
                'post_author' => 1,
            ]);
        }
    }

    private static function process_existing_posts() {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        foreach ($posts as $post_id) {
            $instance = new self();
            $instance->save_first_letter($post_id, get_post($post_id));
        }
    }

    public function maybe_create_page() {
        if (get_option('abp_v2_page_created')) return;
        $this->create_blog_page();
        update_option('abp_v2_page_created', true);
    }

    public function save_first_letter($post_id, $post) {
        if ($post->post_type !== 'post') return;
        if (wp_is_post_revision($post_id)) return;

        $title = get_the_title($post_id);
        if (!$title) return;

        $first = $this->mb_first_letter($title);
        if (!$first) return;

        update_post_meta($post_id, self::META_KEY, $first);
    }

    private function mb_first_letter(string $str): ?string {
        $str = trim($str);
        if ($str === '') return null;
        
        $str = preg_replace('/^[\s"\':«»\(\)\[\]\-]+/u', '', $str);
        if ($str === '') return null;
        
        $first = mb_substr($str, 0, 1, 'UTF-8');
        return mb_strtoupper($first, 'UTF-8');
    }

    public function enqueue_assets() {
        // Загружаем ресурсы на страницах блога и отдельных статей
        if (!is_singular() && !is_page()) return;

        wp_register_style('abp-v2-css', plugins_url('assets/css/abp-v2.css', __FILE__), [], self::VERSION);
        wp_register_script('abp-v2-js', plugins_url('assets/js/abp-v2.js', __FILE__), ['wp-url'], self::VERSION, true);

        wp_localize_script('abp-v2-js', 'ABP_V2', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE),
            'slug' => self::SLUG,
        ]);
    }

    public function shortcode_output($atts) {
        wp_enqueue_style('abp-v2-css');
        wp_enqueue_script('abp-v2-js');

        $active_map = $this->letters_availability();

        ob_start(); ?>
        <div id="abp-v2-root" class="abp-v2-root">
            <div class="abp-v2-header">
                <h1 class="abp-v2-title">Блог</h1>
                <p class="abp-v2-subtitle">Найдите статьи по алфавиту</p>
            </div>

            <div class="abp-v2-alphabet">
                <?php foreach (self::letters() as $letter):
                    $count = $active_map[$letter] ?? 0;
                    $disabled = $count === 0 ? 'data-disabled="true"' : '';
                ?>
                    <button class="abp-v2-letter" data-letter="<?php echo esc_attr($letter); ?>" <?php echo $disabled; ?>>
                        <?php echo esc_html($letter); ?>
                        <?php if ($count > 0): ?><span class="abp-v2-count"><?php echo (int)$count; ?></span><?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="abp-v2-content">
                <div class="abp-v2-loader" style="display: none;">
                    <div class="abp-v2-spinner"></div>
                    <span>Загрузка...</span>
                </div>
                <div id="abp-v2-list" class="abp-v2-list"></div>
                <div class="abp-v2-pagination" style="display: none;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Шорткод только для алфавитной панели (для страниц статей)
     */
    public function shortcode_alphabet_only($atts) {
        wp_enqueue_style('abp-v2-css');
        wp_enqueue_script('abp-v2-js');

        $active_map = $this->letters_availability();

        ob_start(); ?>
        <div class="abp-v2-alphabet-only">
            <?php foreach (self::letters() as $letter):
                $count = $active_map[$letter] ?? 0;
                $disabled = $count === 0 ? 'data-disabled="true"' : '';
            ?>
                <button class="abp-v2-letter" data-letter="<?php echo esc_attr($letter); ?>" <?php echo $disabled; ?>>
                    <?php echo esc_html($letter); ?>
                    <?php if ($count > 0): ?><span class="abp-v2-count"><?php echo (int)$count; ?></span><?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function letters(): array {
        $ru = ['А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'];
        $en = range('A','Z');
        return array_merge($ru, $en);
    }

    private function letters_availability(): array {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.meta_value AS letter, COUNT(*) AS qty
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND p.post_status = 'publish' AND p.post_type = 'post'
             GROUP BY pm.meta_value", self::META_KEY
        ), ARRAY_A);

        $map = [];
        if ($results) {
            foreach ($results as $row) {
                $map[$row['letter']] = (int)$row['qty'];
            }
        }
        
        foreach (self::letters() as $L) {
            if (!isset($map[$L])) $map[$L] = 0;
        }
        return $map;
    }

    public function ajax_fetch_posts() {
        check_ajax_referer(self::NONCE, 'nonce');
        
        $letter = sanitize_text_field($_POST['letter'] ?? '');
        $page = max(1, intval($_POST['page'] ?? 1));
        
        if (!$letter) {
            wp_send_json_error(['message' => 'No letter'], 400);
        }

        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_key' => self::META_KEY,
            'meta_value' => $letter,
            'orderby' => 'title',
            'order' => 'ASC',
            'paged' => $page,
            'posts_per_page' => 12,
        ]);

        ob_start();
        if ($q->have_posts()) {
            echo '<div class="abp-v2-results">Найдено: ' . $q->found_posts . ' статей</div>';
            echo '<div class="abp-v2-items">';
            while ($q->have_posts()) {
                $q->the_post();
                echo '<div class="abp-item">';
                
                // Добавляем featured image
                if (has_post_thumbnail()) {
                    echo '<div class="abp-thumbnail">';
                    echo '<a href="' . get_permalink() . '">';
                    echo get_the_post_thumbnail(get_the_ID(), 'medium');
                    echo '</a>';
                    echo '</div>';
                }
                
                echo '<div class="abp-content">';
                echo '<a class="abp-link" href="' . get_permalink() . '">' . get_the_title() . '</a>';
                if (has_excerpt()) {
                    echo '<div class="abp-excerpt">' . wp_trim_words(get_the_excerpt(), 20) . '</div>';
                }
                echo '<div class="abp-date">' . get_the_date() . '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="abp-v2-empty">Нет статей на эту букву</div>';
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        $pagination = [
            'current' => $page,
            'total' => (int)$q->max_num_pages,
            'has_prev' => $page > 1,
            'has_next' => $page < $q->max_num_pages,
        ];

        wp_send_json_success([
            'html' => $html,
            'pagination' => $pagination,
        ]);
    }

    public function add_rewrite_rules() {
        add_rewrite_rule('^' . self::SLUG . '2/([^/]+)/?$', 'index.php?' . self::QVAR . '=$matches[1]', 'top');
        add_rewrite_rule('^' . self::SLUG . '2/([^/]+)/page/([0-9]+)/?$', 'index.php?' . self::QVAR . '=$matches[1]&paged=$matches[2]', 'top');
    }

    public function register_query_var($vars) {
        $vars[] = self::QVAR;
        return $vars;
    }

    public function intercept_template($template) {
        $letter = get_query_var(self::QVAR);
        
        // Проверяем, является ли это главной страницей блога
        if (is_page('blog2') && !$letter) {
            $tpl = plugin_dir_path(__FILE__) . 'templates/abp-v2-main-page.php';
            if (file_exists($tpl)) return $tpl;
        }
        
        // Проверяем, является ли это буквенным архивом
        if ($letter) {
            $tpl = plugin_dir_path(__FILE__) . 'templates/abp-v2-archive.php';
            if (file_exists($tpl)) return $tpl;
        }
        
        return $template;
    }

    public function seo_title($title) {
        $letter = get_query_var(self::QVAR);
        if ($letter) {
            $L = mb_strtoupper(urldecode($letter), 'UTF-8');
            return 'Блог — буква «' . $L . '» | BizFin Pro';
        }
        return $title;
    }

    public function seo_meta() {
        $letter = get_query_var(self::QVAR);
        if ($letter) {
            $L = esc_html(mb_strtoupper(urldecode($letter), 'UTF-8'));
            echo '<meta name="description" content="Статьи блога на букву ' . $L . '. Алфавитный рубрикатор блога."/>' . "\n";
        }
    }

    /**
     * Добавляет алфавитную панель на страницы статей
     */
    public function add_alphabet_panel_to_single_post() {
        if (!is_single() || get_post_type() !== 'post') return;

        // Подключаем необходимые ресурсы
        wp_enqueue_style('abp-v2-css');
        wp_enqueue_script('abp-v2-js');
        
        // Локализуем скрипт
        wp_localize_script('abp-v2-js', 'ABP_V2', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE),
            'slug' => self::SLUG,
        ]);

        // Выводим JavaScript для добавления алфавитной панели после шапки
        // Создаем HTML с заголовком, поиском и алфавитом для страниц статей
        $alphabet_html = '
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
        <div class="abp-v2-main-alphabet">
            ' . do_shortcode('[abp_v2_alphabet_only]') . '
        </div>';
        $alphabet_html_escaped = json_encode($alphabet_html);
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Ждем загрузки шапки сайта
                const header = document.querySelector('header');
                if (header) {
                    // Создаем алфавитную панель
                    const alphabetPanel = document.createElement('div');
                    alphabetPanel.className = 'abp-v2-single-post-alphabet';
                    alphabetPanel.innerHTML = <?php echo $alphabet_html_escaped; ?>;
                    
                    // Вставляем панель после шапки
                    header.parentNode.insertBefore(alphabetPanel, header.nextSibling);
                    console.log('Alphabet panel added after header');
                    
                    // Добавляем обработчики событий для букв
                    const letters = alphabetPanel.querySelectorAll('.abp-v2-letter');
                    letters.forEach(letter => {
                        letter.addEventListener('click', function(e) {
                            e.preventDefault();
                            const letterChar = this.getAttribute('data-letter');
                            console.log('Letter clicked:', letterChar);
                            
                            // Переходим в блог
                            if (typeof window.ABP_V2 !== 'undefined') {
                                window.location.href = '/' + window.ABP_V2.slug + '2/' + encodeURIComponent(letterChar) + '/';
                            }
                        });
                    });
                    console.log('Click handlers added to', letters.length, 'letters');
                    
                    // Инициализируем поиск для страниц статей
                    const searchInput = alphabetPanel.querySelector('#abp-v2-search-input');
                    const searchBtn = alphabetPanel.querySelector('#abp-v2-search-btn');
                    
                    if (searchInput && searchBtn) {
                        searchBtn.addEventListener('click', function() {
                            const query = searchInput.value.trim();
                            if (query) {
                                // Переходим в блог с поисковым запросом
                                if (typeof window.ABP_V2 !== 'undefined') {
                                    window.location.href = '/' + window.ABP_V2.slug + '2/?search=' + encodeURIComponent(query);
                                }
                            }
                        });
                        
                        searchInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                const query = searchInput.value.trim();
                                if (query) {
                                    // Переходим в блог с поисковым запросом
                                    if (typeof window.ABP_V2 !== 'undefined') {
                                        window.location.href = '/' + window.ABP_V2.slug + '2/?search=' + encodeURIComponent(query);
                                    }
                                }
                            }
                        });
                    }
                } else {
                    // Если шапка не найдена, вставляем в начало body
                    const body = document.body;
                    const alphabetPanel = document.createElement('div');
                    alphabetPanel.className = 'abp-v2-single-post-alphabet';
                    alphabetPanel.innerHTML = <?php echo $alphabet_html_escaped; ?>;
                    
                    // Вставляем в начало body
                    body.insertBefore(alphabetPanel, body.firstChild);
                    console.log('Alphabet panel added to body');
                    
                    // Добавляем обработчики событий для букв
                    const letters = alphabetPanel.querySelectorAll('.abp-v2-letter');
                    letters.forEach(letter => {
                        letter.addEventListener('click', function(e) {
                            e.preventDefault();
                            const letterChar = this.getAttribute('data-letter');
                            console.log('Letter clicked:', letterChar);
                            
                            // Переходим в блог
                            if (typeof window.ABP_V2 !== 'undefined') {
                                window.location.href = '/' + window.ABP_V2.slug + '2/' + encodeURIComponent(letterChar) + '/';
                            }
                        });
                    });
                    console.log('Click handlers added to', letters.length, 'letters');
                    
                    // Инициализируем поиск для страниц статей
                    const searchInput = alphabetPanel.querySelector('#abp-v2-search-input');
                    const searchBtn = alphabetPanel.querySelector('#abp-v2-search-btn');
                    
                    if (searchInput && searchBtn) {
                        searchBtn.addEventListener('click', function() {
                            const query = searchInput.value.trim();
                            if (query) {
                                // Переходим в блог с поисковым запросом
                                if (typeof window.ABP_V2 !== 'undefined') {
                                    window.location.href = '/' + window.ABP_V2.slug + '2/?search=' + encodeURIComponent(query);
                                }
                            }
                        });
                        
                        searchInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                const query = searchInput.value.trim();
                                if (query) {
                                    // Переходим в блог с поисковым запросом
                                    if (typeof window.ABP_V2 !== 'undefined') {
                                        window.location.href = '/' + window.ABP_V2.slug + '2/?search=' + encodeURIComponent(query);
                                    }
                                }
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Error adding alphabet panel:', error);
            }
        });
        </script>
        <?php
    }
    
    /**
     * AJAX обработчик для поиска по статьям
     */
    public function ajax_search() {
        check_ajax_referer(self::NONCE, 'nonce');
        
        $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
        
        if (empty($query)) {
            wp_send_json_error(['message' => 'Пустой поисковый запрос'], 400);
        }
        
        // Выполняем поиск по заголовкам и содержимому
        $search_query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            's' => $query,
            'posts_per_page' => 20,
            'orderby' => 'relevance',
            'meta_query' => [
                [
                    'key' => self::META_KEY,
                    'compare' => 'EXISTS'
                ]
            ]
        ]);
        
        $posts = [];
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $posts[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 20, '...'),
                    'date' => get_the_date('c'),
                    'date_formatted' => get_the_date()
                ];
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success([
            'query' => $query,
            'count' => count($posts),
            'posts' => $posts
        ]);
    }
}

new ABP_V2_Plugin();
