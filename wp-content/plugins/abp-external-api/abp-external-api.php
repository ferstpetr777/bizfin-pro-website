<?php
/**
 * Plugin Name: ABP External API
 * Description: REST API для внешних приложений алфавитного блога
 * Version: 1.0.0
 * Author: BizFin Pro Team
 */

if (!defined('ABSPATH')) exit;

class ABP_External_API {
    const API_NAMESPACE = 'abp/v1';
    const VERSION = '1.0.0';

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('init', [$this, 'add_cors_headers']);
    }

    /** Добавление CORS заголовков */
    public function add_cors_headers() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        }
    }

    /** Регистрация API маршрутов */
    public function register_routes() {
        // Получение списка букв с количеством постов
        register_rest_route(self::API_NAMESPACE, '/letters', [
            'methods' => 'GET',
            'callback' => [$this, 'get_letters'],
            'permission_callback' => '__return_true',
        ]);

        // Получение постов по букве
        register_rest_route(self::API_NAMESPACE, '/posts/(?P<letter>[a-zA-ZА-ЯЁ]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_posts_by_letter'],
            'permission_callback' => '__return_true',
            'args' => [
                'letter' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ],
                'search' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Получение конкретного поста
        register_rest_route(self::API_NAMESPACE, '/post/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_post'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Поиск постов
        register_rest_route(self::API_NAMESPACE, '/search', [
            'methods' => 'GET',
            'callback' => [$this, 'search_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'query' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Получение категорий AI
        register_rest_route(self::API_NAMESPACE, '/categories', [
            'methods' => 'GET',
            'callback' => [$this, 'get_categories'],
            'permission_callback' => '__return_true',
        ]);

        // Получение постов по категории
        register_rest_route(self::API_NAMESPACE, '/category/(?P<category>[^/]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_posts_by_category'],
            'permission_callback' => '__return_true',
            'args' => [
                'category' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Статистика блога
        register_rest_route(self::API_NAMESPACE, '/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'get_blog_stats'],
            'permission_callback' => '__return_true',
        ]);
    }

    /** Получение списка букв */
    public function get_letters($request) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.meta_value AS letter, COUNT(*) AS count
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND p.post_status = 'publish' AND p.post_type = 'post'
             GROUP BY pm.meta_value
             ORDER BY pm.meta_value",
            'abp_first_letter'
        ), ARRAY_A);

        $letters = [];
        foreach ($results as $row) {
            $letters[] = [
                'letter' => $row['letter'],
                'count' => (int)$row['count'],
                'url' => home_url('/blog/' . rawurlencode($row['letter']) . '/')
            ];
        }

        return rest_ensure_response([
            'letters' => $letters,
            'total' => count($letters)
        ]);
    }

    /** Получение постов по букве */
    public function get_posts_by_letter($request) {
        $letter = mb_strtoupper($request['letter'], 'UTF-8');
        $page = $request['page'];
        $per_page = min($request['per_page'], 50); // Максимум 50 постов за раз
        $search = $request['search'];

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_key' => 'abp_first_letter',
            'meta_value' => $letter,
            'orderby' => 'title',
            'order' => 'ASC',
            'paged' => $page,
            'posts_per_page' => $per_page,
        ];

        if ($search) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        
        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = $this->format_post_data($post);
        }

        return rest_ensure_response([
            'posts' => $posts,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
                'has_next' => $page < $query->max_num_pages,
                'has_prev' => $page > 1,
            ]
        ]);
    }

    /** Получение конкретного поста */
    public function get_post($request) {
        $post_id = $request['id'];
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'post') {
            return new WP_Error('post_not_found', 'Пост не найден', ['status' => 404]);
        }

        return rest_ensure_response($this->format_post_data($post, true));
    }

    /** Поиск постов */
    public function search_posts($request) {
        $query = $request['query'];
        $page = $request['page'];
        $per_page = min($request['per_page'], 50);

        $search_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            's' => $query,
            'paged' => $page,
            'posts_per_page' => $per_page,
            'orderby' => 'relevance',
        ];

        $search_query = new WP_Query($search_args);
        
        $posts = [];
        foreach ($search_query->posts as $post) {
            $posts[] = $this->format_post_data($post);
        }

        return rest_ensure_response([
            'posts' => $posts,
            'query' => $query,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $search_query->found_posts,
                'total_pages' => $search_query->max_num_pages,
                'has_next' => $page < $search_query->max_num_pages,
                'has_prev' => $page > 1,
            ]
        ]);
    }

    /** Получение категорий */
    public function get_categories($request) {
        global $wpdb;
        
        $results = $wpdb->get_results("
            SELECT pm.meta_value as category, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_ai_category' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
        ", ARRAY_A);

        $categories = [];
        foreach ($results as $row) {
            $categories[] = [
                'name' => $row['category'],
                'count' => (int)$row['count'],
                'slug' => sanitize_title($row['category'])
            ];
        }

        return rest_ensure_response([
            'categories' => $categories,
            'total' => count($categories)
        ]);
    }

    /** Получение постов по категории */
    public function get_posts_by_category($request) {
        $category = $request['category'];
        $page = $request['page'];
        $per_page = min($request['per_page'], 50);

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_key' => 'abp_ai_category',
            'meta_value' => $category,
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $page,
            'posts_per_page' => $per_page,
        ];

        $query = new WP_Query($args);
        
        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = $this->format_post_data($post);
        }

        return rest_ensure_response([
            'posts' => $posts,
            'category' => $category,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $query->found_posts,
                'total_pages' => $query->max_num_pages,
                'has_next' => $page < $query->max_num_pages,
                'has_prev' => $page > 1,
            ]
        ]);
    }

    /** Получение статистики блога */
    public function get_blog_stats($request) {
        global $wpdb;
        
        // Общая статистика
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'post' AND post_status = 'publish'
        ");
        
        $total_letters = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND p.post_status = 'publish'",
            'abp_first_letter'
        ));
        
        $total_categories = $wpdb->get_var("
            SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_ai_category' AND p.post_status = 'publish'
        ");
        
        // Популярные буквы
        $popular_letters = $wpdb->get_results($wpdb->prepare(
            "SELECT pm.meta_value AS letter, COUNT(*) AS count
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND p.post_status = 'publish'
             GROUP BY pm.meta_value
             ORDER BY count DESC
             LIMIT 5",
            'abp_first_letter'
        ), ARRAY_A);
        
        // Популярные категории
        $popular_categories = $wpdb->get_results("
            SELECT pm.meta_value as category, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'abp_ai_category' 
            AND p.post_type = 'post' 
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT 5
        ", ARRAY_A);

        return rest_ensure_response([
            'total_posts' => (int)$total_posts,
            'total_letters' => (int)$total_letters,
            'total_categories' => (int)$total_categories,
            'popular_letters' => $popular_letters,
            'popular_categories' => $popular_categories,
            'api_version' => self::VERSION,
            'generated_at' => current_time('c')
        ]);
    }

    /** Форматирование данных поста */
    private function format_post_data($post, $include_content = false) {
        $post_data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'url' => get_permalink($post->ID),
            'excerpt' => get_the_excerpt($post->ID),
            'date' => [
                'published' => $post->post_date,
                'published_gmt' => $post->post_date_gmt,
                'modified' => $post->post_modified,
                'modified_gmt' => $post->post_modified_gmt,
            ],
            'author' => [
                'id' => $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author),
            ],
            'featured_image' => $this->get_featured_image_data($post->ID),
            'first_letter' => get_post_meta($post->ID, 'abp_first_letter', true),
            'ai_category' => get_post_meta($post->ID, 'abp_ai_category', true),
            'yoast_seo' => $this->get_yoast_seo_data($post->ID),
        ];

        if ($include_content) {
            $post_data['content'] = apply_filters('the_content', $post->post_content);
            $post_data['raw_content'] = $post->post_content;
        }

        return $post_data;
    }

    /** Получение данных изображения записи */
    private function get_featured_image_data($post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return null;
        }

        $image = wp_get_attachment_image_src($thumbnail_id, 'full');
        
        if (!$image) {
            return null;
        }

        return [
            'id' => $thumbnail_id,
            'url' => $image[0],
            'width' => $image[1],
            'height' => $image[2],
            'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
        ];
    }

    /** Получение данных Yoast SEO */
    private function get_yoast_seo_data($post_id) {
        if (!defined('WPSEO_VERSION')) {
            return null;
        }

        return [
            'title' => get_post_meta($post_id, '_yoast_wpseo_title', true),
            'description' => get_post_meta($post_id, '_yoast_wpseo_metadesc', true),
            'focus_keyword' => get_post_meta($post_id, '_yoast_wpseo_focuskw', true),
            'canonical' => get_post_meta($post_id, '_yoast_wpseo_canonical', true),
        ];
    }
}

new ABP_External_API();



