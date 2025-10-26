<?php
/**
 * Менеджер внутренней перелинковки для BizFin SEO Article Generator
 * 
 * Отвечает за автоматическое создание релевантных внутренних ссылок
 * на основе реестра существующих статей и страниц сайта
 */

class BizFin_Internal_Linking_Manager {
    
    private $main_plugin;
    private $articles_registry;
    private $linking_rules;
    
    public function __construct($main_plugin) {
        $this->main_plugin = $main_plugin;
        $this->load_articles_registry();
        $this->load_linking_rules();
    }
    
    /**
     * Загружает реестр статей из JSON файла
     */
    private function load_articles_registry() {
        $registry_path = plugin_dir_path(__FILE__) . '../data/articles_registry.json';
        
        if (file_exists($registry_path)) {
            $json_content = file_get_contents($registry_path);
            $this->articles_registry = json_decode($json_content, true);
        } else {
            // Создаём базовый реестр если файл не существует
            $this->articles_registry = $this->create_default_registry();
            $this->save_articles_registry();
        }
    }
    
    /**
     * Загружает правила перелинковки из SEO матрицы
     */
    private function load_linking_rules() {
        $seo_matrix = $this->main_plugin->get_seo_matrix();
        $this->linking_rules = $seo_matrix['mandatory_internal_linking'] ?? [];
    }
    
    /**
     * Создаёт базовый реестр статей
     */
    private function create_default_registry() {
        return [
            'articles' => [
                'bank-guarantees' => [
                    '2833' => [
                        'title' => 'Банковская гарантия: что это, как работает и когда нужна',
                        'slug' => 'bankovskaya-garantiya-chto-eto-kak-rabotaet-i-kogda-nuzhna',
                        'url' => '/bankovskaya-garantiya-chto-eto-kak-rabotaet-i-kogda-nuzhna/',
                        'keywords' => ['банковская гарантия', 'гарантия', 'банк', 'финансы']
                    ]
                ]
            ],
            'pages' => [
                'services' => [
                    '14' => [
                        'title' => 'Калькулятор банковских гарантий',
                        'slug' => 'kalkulyator_bankovskikh_garantiy',
                        'url' => '/kalkulyator_bankovskikh_garantiy/',
                        'keywords' => ['калькулятор', 'банковские гарантии', 'расчет', 'стоимость']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Сохраняет реестр статей в JSON файл
     */
    private function save_articles_registry() {
        $registry_path = plugin_dir_path(__FILE__) . '../data/articles_registry.json';
        $data_dir = dirname($registry_path);
        
        if (!file_exists($data_dir)) {
            wp_mkdir_p($data_dir);
        }
        
        file_put_contents($registry_path, json_encode($this->articles_registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Обновляет реестр статей из WordPress
     */
    public function update_registry_from_wp() {
        // Получаем все опубликованные статьи
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        
        // Получаем все опубликованные страницы
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        
        $updated_registry = [
            'articles' => [],
            'pages' => []
        ];
        
        // Обрабатываем статьи
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            $keywords = $this->extract_keywords_from_content($post->post_content);
            
            $category = $this->categorize_content($keywords);
            
            if (!isset($updated_registry['articles'][$category])) {
                $updated_registry['articles'][$category] = [];
            }
            
            $updated_registry['articles'][$category][$post_id] = [
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'url' => '/' . $post->post_name . '/',
                'keywords' => $keywords
            ];
        }
        
        // Обрабатываем страницы
        foreach ($pages as $page_id) {
            $page = get_post($page_id);
            $keywords = $this->extract_keywords_from_content($page->post_content);
            
            $category = $this->categorize_page($page->post_name, $keywords);
            
            if (!isset($updated_registry['pages'][$category])) {
                $updated_registry['pages'][$category] = [];
            }
            
            $updated_registry['pages'][$category][$page_id] = [
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'url' => '/' . $page->post_name . '/',
                'keywords' => $keywords
            ];
        }
        
        $this->articles_registry = $updated_registry;
        $this->save_articles_registry();
        
        return $updated_registry;
    }
    
    /**
     * Извлекает ключевые слова из контента
     */
    private function extract_keywords_from_content($content) {
        // Убираем HTML теги
        $text = wp_strip_all_tags($content);
        
        // Простая экстракция ключевых слов (можно улучшить)
        $words = preg_split('/\s+/', mb_strtolower($text));
        $words = array_filter($words, function($word) {
            return mb_strlen($word) > 3 && !in_array($word, ['это', 'что', 'как', 'для', 'или', 'но', 'если', 'когда', 'где', 'почему']);
        });
        
        $word_freq = array_count_values($words);
        arsort($word_freq);
        
        return array_slice(array_keys($word_freq), 0, 10);
    }
    
    /**
     * Категоризирует контент по тематике
     */
    private function categorize_content($keywords) {
        $categories = [
            'bank-guarantees' => ['банковская', 'гарантия', 'банк', 'финансы', 'тендер', 'закупки'],
            'banking' => ['кредит', 'вклад', 'карта', 'ипотека', 'депозит'],
            'business' => ['ип', 'бизнес', 'предприниматель', 'компания', 'ооо'],
            'investments' => ['инвестиции', 'акции', 'облигации', 'портфель'],
            'taxes' => ['налог', 'ндс', 'прибыль', 'декларация']
        ];
        
        foreach ($categories as $category => $category_keywords) {
            $matches = array_intersect($keywords, $category_keywords);
            if (count($matches) > 0) {
                return $category;
            }
        }
        
        return 'general';
    }
    
    /**
     * Категоризирует страницы
     */
    private function categorize_page($slug, $keywords) {
        if (strpos($slug, 'kalkulyator') !== false || strpos($slug, 'calculator') !== false) {
            return 'services';
        }
        
        if (strpos($slug, 'contact') !== false || strpos($slug, 'kontakt') !== false) {
            return 'company';
        }
        
        if (strpos($slug, 'service') !== false || strpos($slug, 'uslugi') !== false) {
            return 'services';
        }
        
        return 'general';
    }
    
    /**
     * Генерирует внутренние ссылки для контента
     */
    public function generate_internal_links($content, $keyword, $max_links = 5) {
        if (!$this->linking_rules['enabled']) {
            return $content;
        }
        
        $target_links = $this->find_relevant_links($keyword, $max_links);
        
        if (empty($target_links)) {
            return $content;
        }
        
        $content_with_links = $this->insert_links_into_content($content, $target_links);
        
        return $content_with_links;
    }
    
    /**
     * Находит релевантные ссылки для ключевого слова
     */
    private function find_relevant_links($keyword, $max_links) {
        $links = [];
        
        // Ищем в правилах автолинковки
        foreach ($this->linking_rules['auto_linking_keywords'] as $category => $rules) {
            if (in_array($keyword, $rules['keywords'])) {
                // Добавляем статьи
                foreach ($rules['target_articles'] as $article_id) {
                    $article = $this->find_article_by_id($article_id);
                    if ($article) {
                        $links[] = $article;
                    }
                }
                
                // Добавляем сервисы
                foreach ($rules['target_services'] as $service_id) {
                    $service = $this->find_page_by_id($service_id);
                    if ($service) {
                        $links[] = $service;
                    }
                }
                
                // Добавляем страницы
                foreach ($rules['target_pages'] as $page_id) {
                    $page = $this->find_page_by_id($page_id);
                    if ($page) {
                        $links[] = $page;
                    }
                }
            }
        }
        
        return array_slice($links, 0, $max_links);
    }
    
    /**
     * Находит статью по ID
     */
    private function find_article_by_id($id) {
        foreach ($this->articles_registry['articles'] as $category => $articles) {
            if (isset($articles[$id])) {
                return $articles[$id];
            }
        }
        return null;
    }
    
    /**
     * Находит страницу по ID
     */
    private function find_page_by_id($id) {
        foreach ($this->articles_registry['pages'] as $category => $pages) {
            if (isset($pages[$id])) {
                return $pages[$id];
            }
        }
        return null;
    }
    
    /**
     * Вставляет ссылки в контент
     */
    private function insert_links_into_content($content, $links) {
        $link_count = 0;
        $max_links = $this->linking_rules['rules']['maximum_internal_links']['count'] ?? 7;
        
        foreach ($links as $link) {
            if ($link_count >= $max_links) {
                break;
            }
            
            // Ищем подходящее место для вставки ссылки
            $content = $this->insert_single_link($content, $link);
            $link_count++;
        }
        
        return $content;
    }
    
    /**
     * Вставляет одну ссылку в контент
     */
    private function insert_single_link($content, $link) {
        // Простая логика вставки - можно улучшить
        $link_html = '<a href="' . $link['url'] . '">' . $link['title'] . '</a>';
        
        // Ищем подходящее место для вставки (например, в конце абзаца)
        $pattern = '/(<p[^>]*>.*?<\/p>)/';
        $content = preg_replace_callback($pattern, function($matches) use ($link_html, &$inserted) {
            if (!isset($inserted) && strpos($matches[1], '<a ') === false) {
                $inserted = true;
                return $matches[1] . ' ' . $link_html;
            }
            return $matches[1];
        }, $content, 1);
        
        return $content;
    }
    
    /**
     * Валидирует внутренние ссылки в контенте
     */
    public function validate_internal_links($content) {
        $errors = [];
        
        // Извлекаем все ссылки из контента
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        $links = $matches[1];
        
        foreach ($links as $link) {
            // Проверяем внутренние ссылки
            if (strpos($link, 'http') !== 0 && strpos($link, '#') !== 0) {
                if (!$this->is_valid_internal_link($link)) {
                    $errors[] = "Битая внутренняя ссылка: {$link}";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Проверяет валидность внутренней ссылки
     */
    private function is_valid_internal_link($link) {
        // Убираем ведущий слэш
        $link = ltrim($link, '/');
        
        // Проверяем в реестре статей
        foreach ($this->articles_registry['articles'] as $category => $articles) {
            foreach ($articles as $article) {
                if ($article['slug'] === $link || $article['url'] === '/' . $link) {
                    return true;
                }
            }
        }
        
        // Проверяем в реестре страниц
        foreach ($this->articles_registry['pages'] as $category => $pages) {
            foreach ($pages as $page) {
                if ($page['slug'] === $link || $page['url'] === '/' . $link) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Получает реестр статей
     */
    public function get_articles_registry() {
        return $this->articles_registry;
    }
    
    /**
     * Получает правила перелинковки
     */
    public function get_linking_rules() {
        return $this->linking_rules;
    }
}

