<?php
/**
 * Plugin Name: ABP Article Quality Monitor
 * Description: Автоматический мониторинг качества статей: AI-категоризация, SEO-оптимизация и алфавитная система
 * Version: 1.0.0
 * Author: BizFin Pro Team
 */

if (!defined('ABSPATH')) exit;

class ABP_Article_Quality_Monitor {
    
    const META_KEY_QUALITY_CHECK = 'abp_quality_check';
    const META_KEY_AI_CATEGORY = 'abp_ai_category';
    const META_KEY_FIRST_LETTER = 'abp_first_letter';
    const NONCE_ACTION = 'abp_quality_action';
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('save_post', [$this, 'check_post_quality'], 20, 2);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_abp_bulk_optimize', [$this, 'ajax_bulk_optimize']);
        add_action('wp_ajax_abp_optimize_single', [$this, 'ajax_optimize_single']);
        add_action('wp_ajax_abp_get_quality_stats', [$this, 'ajax_get_quality_stats']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    public function init() {
        // Создаем таблицу для истории проверок
        $this->create_quality_table();
    }
    
    private function create_quality_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            post_id int(11) NOT NULL,
            check_date datetime NOT NULL,
            ai_category_status varchar(20) NOT NULL,
            seo_optimization_status varchar(20) NOT NULL,
            alphabet_system_status varchar(20) NOT NULL,
            meta_desc_keyword_status varchar(20) NOT NULL DEFAULT 'missing',
            title_keyword_match_status varchar(20) NOT NULL DEFAULT 'missing',
            overall_status varchar(20) NOT NULL,
            ai_category varchar(100),
            focus_keyword varchar(200),
            first_letter varchar(10),
            issues text,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY check_date (check_date),
            KEY overall_status (overall_status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Миграция для добавления новых полей к существующим записям
        $this->migrate_quality_table();
    }
    
    /** Миграция таблицы для добавления новых полей */
    private function migrate_quality_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        
        // Проверяем, существуют ли новые поля
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'meta_desc_keyword_status'");
        
        if (empty($columns)) {
            // Добавляем новые поля
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN meta_desc_keyword_status varchar(20) NOT NULL DEFAULT 'missing'");
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN title_keyword_match_status varchar(20) NOT NULL DEFAULT 'missing'");
            
            error_log("ABP Quality Monitor: Added new validation columns to quality_checks table");
        }
    }
    
    public function check_post_quality($post_id, $post) {
        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Проверяем качество статьи
        $quality_data = $this->perform_quality_check($post_id);
        
        // Сохраняем результаты проверки
        $this->save_quality_check($post_id, $quality_data);
        
        // Обновляем мета-данные поста
        update_post_meta($post_id, self::META_KEY_QUALITY_CHECK, $quality_data);
        
        // Логируем результаты
        error_log("ABP Quality Check for post $post_id: " . json_encode($quality_data));
    }
    
    private function perform_quality_check($post_id) {
        error_log("=== ABP Quality Monitor: perform_quality_check START for post $post_id ===");
        
        $post = get_post($post_id);
        $meta = get_post_meta($post_id);
        error_log("ABP Quality Monitor: Post retrieved: " . ($post ? "Yes (ID: {$post->ID})" : "No"));
        error_log("ABP Quality Monitor: Meta data count: " . count($meta));
        
        // Проверяем AI-категоризацию
        error_log("ABP Quality Monitor: Checking AI category with meta key: " . self::META_KEY_AI_CATEGORY);
        $ai_category = get_post_meta($post_id, self::META_KEY_AI_CATEGORY, true);
        $ai_category_status = !empty($ai_category) ? 'ok' : 'missing';
        error_log("ABP Quality Monitor: AI category value: " . ($ai_category ?: 'empty'));
        error_log("ABP Quality Monitor: AI category status: $ai_category_status");
        
        // Проверяем SEO-оптимизацию
        error_log("ABP Quality Monitor: Checking SEO optimization...");
        $seo_data = $this->check_seo_optimization($post_id, $meta);
        error_log("ABP Quality Monitor: SEO data: " . json_encode($seo_data));
        
        // Проверяем алфавитную систему
        error_log("ABP Quality Monitor: Checking alphabet system...");
        $alphabet_data = $this->check_alphabet_system($post_id, $post);
        error_log("ABP Quality Monitor: Alphabet data: " . json_encode($alphabet_data));
        
        // Проверяем соответствие Meta Description фокусному ключевому слову
        error_log("ABP Quality Monitor: Checking meta description keyword match...");
        $meta_desc_keyword_status = $this->check_meta_description_keyword_match($post_id);
        error_log("ABP Quality Monitor: Meta description keyword status: $meta_desc_keyword_status");
        
        // Проверяем соответствие заголовка фокусному ключевому слову
        error_log("ABP Quality Monitor: Checking title keyword match...");
        $title_keyword_match_status = $this->check_title_keyword_match($post_id, $post);
        error_log("ABP Quality Monitor: Title keyword match status: $title_keyword_match_status");
        
        // Определяем общий статус
        $overall_status = 'ok';
        $issues = [];
        
        if ($ai_category_status !== 'ok') {
            $overall_status = 'issues';
            $issues[] = 'AI-категория отсутствует';
            error_log("ABP Quality Monitor: AI category issue added");
        }
        
        if ($seo_data['status'] !== 'ok') {
            $overall_status = 'issues';
            $issues = array_merge($issues, $seo_data['issues']);
            error_log("ABP Quality Monitor: SEO issues added: " . json_encode($seo_data['issues']));
        }
        
        if ($alphabet_data['status'] !== 'ok') {
            $overall_status = 'issues';
            $issues[] = 'Проблемы с алфавитной системой';
            error_log("ABP Quality Monitor: Alphabet system issue added");
        }
        
        if ($meta_desc_keyword_status !== 'ok') {
            $overall_status = 'issues';
            $issues[] = 'Meta Description не начинается с фокусного ключевого слова';
            error_log("ABP Quality Monitor: Meta description keyword issue added");
        }
        
        if ($title_keyword_match_status !== 'ok') {
            $overall_status = 'issues';
            $issues[] = 'Заголовок не соответствует фокусному ключевому слову';
            error_log("ABP Quality Monitor: Title keyword match issue added");
        }
        
        $result = [
            'post_id' => $post_id,
            'check_date' => current_time('mysql'),
            'ai_category_status' => $ai_category_status,
            'seo_optimization_status' => $seo_data['status'],
            'alphabet_system_status' => $alphabet_data['status'],
            'meta_desc_keyword_status' => $meta_desc_keyword_status,
            'title_keyword_match_status' => $title_keyword_match_status,
            'overall_status' => $overall_status,
            'ai_category' => $ai_category,
            'focus_keyword' => $seo_data['focus_keyword'],
            'first_letter' => $alphabet_data['first_letter'],
            'issues' => implode(', ', $issues)
        ];
        
        error_log("ABP Quality Monitor: Final quality check result: " . json_encode($result));
        
        // Сохраняем результат в базу данных
        $this->save_quality_check($post_id, $result);
        error_log("ABP Quality Monitor: Quality check saved to database for post $post_id");
        
        error_log("=== ABP Quality Monitor: perform_quality_check END for post $post_id ===");
        
        return $result;
    }
    
    private function check_seo_optimization($post_id, $meta) {
        $yoast_title = isset($meta['_yoast_wpseo_title'][0]) ? $meta['_yoast_wpseo_title'][0] : '';
        $yoast_desc = isset($meta['_yoast_wpseo_metadesc'][0]) ? $meta['_yoast_wpseo_metadesc'][0] : '';
        $focus_keyword = isset($meta['_yoast_wpseo_focuskw'][0]) ? $meta['_yoast_wpseo_focuskw'][0] : '';
        $canonical = isset($meta['_yoast_wpseo_canonical'][0]) ? $meta['_yoast_wpseo_canonical'][0] : '';
        
        $issues = [];
        
        if (empty($yoast_title)) $issues[] = 'отсутствует SEO title';
        if (empty($yoast_desc)) $issues[] = 'отсутствует meta description';
        if (empty($focus_keyword)) $issues[] = 'отсутствует focus keyword';
        if (empty($canonical)) $issues[] = 'отсутствует canonical URL';
        
        $status = empty($issues) ? 'ok' : 'incomplete';
        
        return [
            'status' => $status,
            'focus_keyword' => $focus_keyword,
            'issues' => $issues
        ];
    }
    
    private function check_alphabet_system($post_id, $post) {
        $first_letter_meta = get_post_meta($post_id, self::META_KEY_FIRST_LETTER, true);
        
        // Вычисляем первую букву из заголовка
        $title = $post->post_title;
        $clean_title = preg_replace('/<[^>]+>/', '', $title);
        $clean_title = trim($clean_title);
        
        if (empty($clean_title)) {
            return ['status' => 'error', 'first_letter' => '', 'issues' => ['пустой заголовок']];
        }
        
        $first_char = mb_strtoupper(mb_substr($clean_title, 0, 1, 'UTF-8'), 'UTF-8');
        
        // Нормализуем кириллицу
        if ($first_char === 'Ё') $first_char = 'Ё';
        elseif ($first_char === 'Е') $first_char = 'Е';
        
        $status = ($first_letter_meta === $first_char && !empty($first_letter_meta)) ? 'ok' : 'missing';
        
        return [
            'status' => $status,
            'first_letter' => $first_letter_meta,
            'issues' => $status === 'missing' ? ['неправильная первая буква'] : []
        ];
    }
    
    private function save_quality_check($post_id, $quality_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        
        // Сначала удаляем существующие записи для этого поста
        $wpdb->delete(
            $table_name,
            ['post_id' => $post_id],
            ['%d']
        );
        
        // Добавляем новые поля для Meta Description и Title Keyword Match
        $insert_data = [
                'post_id' => $post_id,
                'check_date' => $quality_data['check_date'],
                'ai_category_status' => $quality_data['ai_category_status'],
                'seo_optimization_status' => $quality_data['seo_optimization_status'],
                'alphabet_system_status' => $quality_data['alphabet_system_status'],
                'overall_status' => $quality_data['overall_status'],
                'ai_category' => $quality_data['ai_category'],
                'focus_keyword' => $quality_data['focus_keyword'],
                'first_letter' => $quality_data['first_letter'],
                'issues' => $quality_data['issues']
        ];
        
        $format_data = ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'];
        
        // Добавляем новые поля если они есть
        if (isset($quality_data['meta_desc_keyword_status'])) {
            $insert_data['meta_desc_keyword_status'] = $quality_data['meta_desc_keyword_status'];
            $format_data[] = '%s';
        }
        
        if (isset($quality_data['title_keyword_match_status'])) {
            $insert_data['title_keyword_match_status'] = $quality_data['title_keyword_match_status'];
            $format_data[] = '%s';
        }
        
        $result = $wpdb->insert($table_name, $insert_data, $format_data);
        
        if ($result === false) {
            error_log("ABP Quality Monitor: Failed to save quality check for post $post_id. Error: " . $wpdb->last_error);
        } else {
            error_log("ABP Quality Monitor: Successfully saved quality check for post $post_id (ID: " . $wpdb->insert_id . ")");
        }
        
        return $result;
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Качество статей',
            'Качество статей',
            'manage_options',
            'abp-article-quality',
            [$this, 'admin_page'],
            'dashicons-chart-line',
            30
        );
    }
    
    public function admin_page() {
        $stats = $this->get_quality_statistics();
        $problem_posts = $this->get_problem_posts();
        $all_posts = $this->get_all_posts();
        
        ?>
        <div class="wrap">
            <h1>📊 Мониторинг качества статей</h1>
            
            <div class="abp-quality-dashboard">
                <!-- Статистика -->
                <div class="abp-stats-grid">
                    <div class="abp-stat-card">
                        <h3>Всего статей</h3>
                        <div class="abp-stat-number"><?php echo $stats['total_posts']; ?></div>
                    </div>
                    <div class="abp-stat-card">
                        <h3>Качественные</h3>
                        <div class="abp-stat-number abp-stat-good"><?php echo $stats['quality_posts']; ?></div>
                        <div class="abp-stat-percent"><?php echo $stats['quality_percent']; ?>%</div>
                    </div>
                    <div class="abp-stat-card">
                        <h3>Требуют доработки</h3>
                        <div class="abp-stat-number abp-stat-bad"><?php echo $stats['problem_posts']; ?></div>
                        <div class="abp-stat-percent"><?php echo $stats['problem_percent']; ?>%</div>
                    </div>
                    <div class="abp-stat-card">
                        <h3>AI-категоризированы</h3>
                        <div class="abp-stat-number"><?php echo $stats['ai_categorized']; ?></div>
                        <div class="abp-stat-percent"><?php echo $stats['ai_percent']; ?>%</div>
                    </div>
                </div>
                
                <!-- Детальная статистика -->
                <div class="abp-detailed-stats">
                    <h2>📈 Детальная статистика</h2>
                    <div class="abp-stats-table">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Критерий</th>
                                    <th>Всего</th>
                                    <th>OK</th>
                                    <th>Проблемы</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>AI-категоризация</td>
                                    <td><?php echo $stats['total_posts']; ?></td>
                                    <td><?php echo $stats['ai_categorized']; ?></td>
                                    <td><?php echo $stats['ai_missing']; ?></td>
                                    <td><?php echo $stats['ai_percent']; ?>%</td>
                                </tr>
                                <tr>
                                    <td>SEO-оптимизация</td>
                                    <td><?php echo $stats['total_posts']; ?></td>
                                    <td><?php echo $stats['seo_optimized']; ?></td>
                                    <td><?php echo $stats['seo_missing']; ?></td>
                                    <td><?php echo $stats['seo_percent']; ?>%</td>
                                </tr>
                                <tr>
                                    <td>Алфавитная система</td>
                                    <td><?php echo $stats['total_posts']; ?></td>
                                    <td><?php echo $stats['alphabet_correct']; ?></td>
                                    <td><?php echo $stats['alphabet_missing']; ?></td>
                                    <td><?php echo $stats['alphabet_percent']; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Вкладки -->
                <div class="abp-tabs">
                    <h2 class="nav-tab-wrapper">
                        <a href="#problem-posts" class="nav-tab nav-tab-active" id="tab-problem-posts">
                            ⚠️ Проблемные статьи (<?php echo count($problem_posts); ?>)
                        </a>
                        <a href="#all-posts" class="nav-tab" id="tab-all-posts">
                            📋 Все статьи (<?php echo $stats['total_posts']; ?>)
                        </a>
                    </h2>
                    
                    <!-- Вкладка проблемных статей -->
                    <div id="problem-posts" class="abp-tab-content">
                    <div class="abp-bulk-actions">
                        <button id="abp-bulk-optimize" class="button button-primary button-large">
                            🔧 Комплексная оптимизация всех проблемных статей
                        </button>
                        <div id="abp-bulk-progress" style="display: none;">
                            <div class="abp-progress-bar">
                                <div class="abp-progress-fill"></div>
                            </div>
                            <div class="abp-progress-text">Обработка...</div>
                        </div>
                    </div>
                    
                        <?php if (!empty($problem_posts)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                        <th style="width: 30px;"><input type="checkbox" id="select-all-problem-posts" title="Выбрать все проблемные"></th>
                                <th>ID</th>
                                <th>Заголовок</th>
                                <th>AI-категория</th>
                                        <th style="width: 60px;" title="SEO Title">SEO Title</th>
                                        <th style="width: 60px;" title="Meta Description">Meta Desc</th>
                                        <th style="width: 60px;" title="Focus Keyword">Focus KW</th>
                                        <th style="width: 60px;" title="Canonical URL">Canonical</th>
                                        <th style="width: 60px;" title="Meta Description начинается с ключевого слова">Meta KW</th>
                                        <th style="width: 60px;" title="Заголовок соответствует ключевому слову">Title KW</th>
                                <th>Алфавит</th>
                                <th>Проблемы</th>
                                        <th style="width: 80px;" title="Прогресс оптимизации">⏱️</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($problem_posts as $post): ?>
                                    <tr data-post-id="<?php echo $post->ID; ?>">
                                        <td><input type="checkbox" class="post-checkbox" value="<?php echo $post->ID; ?>"></td>
                                <td><?php echo $post->ID; ?></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>" target="_blank">
                                        <?php echo wp_trim_words($post->post_title, 8); ?>
                                    </a>
                                </td>
                                <td class="abp-status-cell">
                                    <?php if ($post->ai_category_status === 'ok'): ?>
                                        <span class="abp-status-ok">✅</span>
                                    <?php else: ?>
                                        <span class="abp-status-missing">❌</span>
                                    <?php endif; ?>
                                </td>
                                        <!-- SEO Title -->
                                <td class="abp-status-cell">
                                            <?php if ($post->seo_details['seo_title']['status'] === 'ok'): ?>
                                                <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['seo_title']['value']); ?>">✅</span>
                                    <?php else: ?>
                                                <span class="abp-status-missing" title="Отсутствует SEO title">❌</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Meta Description -->
                                        <td class="abp-status-cell">
                                            <?php if ($post->seo_details['meta_desc']['status'] === 'ok'): ?>
                                                <span class="abp-status-ok" title="<?php echo esc_attr(wp_trim_words($post->seo_details['meta_desc']['value'], 10)); ?>">✅</span>
                                            <?php else: ?>
                                                <span class="abp-status-missing" title="Отсутствует meta description">❌</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Focus Keyword -->
                                        <td class="abp-status-cell">
                                            <?php if ($post->seo_details['focus_kw']['status'] === 'ok'): ?>
                                                <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['focus_kw']['value']); ?>">✅</span>
                                            <?php else: ?>
                                                <span class="abp-status-missing" title="Отсутствует focus keyword">❌</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Canonical URL -->
                                        <td class="abp-status-cell">
                                            <?php if ($post->seo_details['canonical']['status'] === 'ok'): ?>
                                                <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['canonical']['value']); ?>">✅</span>
                                            <?php else: ?>
                                                <span class="abp-status-missing" title="Отсутствует canonical URL">❌</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Meta Description Keyword Match -->
                                        <td class="abp-status-cell">
                                            <?php if (isset($post->meta_desc_keyword_status) && $post->meta_desc_keyword_status === 'ok'): ?>
                                                <span class="abp-status-ok" title="Meta Description начинается с ключевого слова">✅</span>
                                            <?php else: ?>
                                                <span class="abp-status-missing" title="Meta Description не начинается с ключевого слова">❌</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Title Keyword Match -->
                                <td class="abp-status-cell">
                                            <?php if (isset($post->title_keyword_match_status) && $post->title_keyword_match_status === 'ok'): ?>
                                                <span class="abp-status-ok" title="Заголовок соответствует ключевому слову">✅</span>
                                    <?php else: ?>
                                                <span class="abp-status-missing" title="Заголовок не соответствует ключевому слову">❌</span>
                                    <?php endif; ?>
                                </td>
                                <td class="abp-status-cell">
                                    <?php if ($post->alphabet_system_status === 'ok'): ?>
                                        <span class="abp-status-ok">✅</span>
                                    <?php else: ?>
                                        <span class="abp-status-missing">❌</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $post->issues; ?></td>
                                        <td class="abp-progress-column">
                                            <div class="abp-progress-indicator" data-post-id="<?php echo $post->ID; ?>">
                                                <span class="abp-progress-text">—</span>
                                            </div>
                                        </td>
                                <td>
                                    <button class="button button-small abp-optimize-single" data-post-id="<?php echo $post->ID; ?>">
                                        Оптимизировать
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                <div class="abp-no-problems">
                                <p>🎉 <strong>Отлично! Все статьи оптимизированы!</strong></p>
                    <p>У вас нет статей, требующих доработки.</p>
                </div>
                <?php endif; ?>
                    </div>
                    
                    <!-- Вкладка всех статей -->
                    <div id="all-posts" class="abp-tab-content" style="display: none;">
                        <div class="abp-bulk-actions">
                            <button id="abp-bulk-optimize-all" class="button button-primary button-large">
                                🔧 Оптимизировать выбранные статьи
                            </button>
                            <div id="abp-bulk-progress-all" style="display: none;">
                                <div class="abp-progress-bar">
                                    <div class="abp-progress-fill"></div>
                                </div>
                                <div class="abp-progress-text">Обработка...</div>
                            </div>
                        </div>
                        
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" id="select-all-posts" title="Выбрать все"></th>
                                    <th>ID</th>
                                    <th>Заголовок</th>
                                    <th>AI-категория</th>
                                    <th style="width: 60px;" title="SEO Title">SEO Title</th>
                                    <th style="width: 60px;" title="Meta Description">Meta Desc</th>
                                    <th style="width: 60px;" title="Focus Keyword">Focus KW</th>
                                    <th style="width: 60px;" title="Canonical URL">Canonical</th>
                                    <th style="width: 60px;" title="Meta Description начинается с ключевого слова">Meta KW</th>
                                    <th style="width: 60px;" title="Заголовок соответствует ключевому слову">Title KW</th>
                                    <th>Алфавит</th>
                                    <th>Проблемы</th>
                                    <th style="width: 80px;" title="Прогресс оптимизации">⏱️</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_posts as $post): ?>
                                <tr data-post-id="<?php echo $post->ID; ?>">
                                    <td><input type="checkbox" class="post-checkbox" value="<?php echo $post->ID; ?>"></td>
                                    <td><?php echo $post->ID; ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($post->ID); ?>" target="_blank">
                                            <?php echo wp_trim_words($post->post_title, 8); ?>
                                        </a>
                                    </td>
                                    <td class="abp-status-cell">
                                        <?php if ($post->ai_category_status === 'ok'): ?>
                                            <span class="abp-status-ok">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- SEO Title -->
                                    <td class="abp-status-cell">
                                        <?php if ($post->seo_details['seo_title']['status'] === 'ok'): ?>
                                            <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['seo_title']['value']); ?>">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Отсутствует SEO title">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Meta Description -->
                                    <td class="abp-status-cell">
                                        <?php if ($post->seo_details['meta_desc']['status'] === 'ok'): ?>
                                            <span class="abp-status-ok" title="<?php echo esc_attr(wp_trim_words($post->seo_details['meta_desc']['value'], 10)); ?>">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Отсутствует meta description">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Focus Keyword -->
                                    <td class="abp-status-cell">
                                        <?php if ($post->seo_details['focus_kw']['status'] === 'ok'): ?>
                                            <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['focus_kw']['value']); ?>">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Отсутствует focus keyword">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Canonical URL -->
                                    <td class="abp-status-cell">
                                        <?php if ($post->seo_details['canonical']['status'] === 'ok'): ?>
                                            <span class="abp-status-ok" title="<?php echo esc_attr($post->seo_details['canonical']['value']); ?>">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Отсутствует canonical URL">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Meta Description Keyword Match -->
                                    <td class="abp-status-cell">
                                        <?php if (isset($post->meta_desc_keyword_status) && $post->meta_desc_keyword_status === 'ok'): ?>
                                            <span class="abp-status-ok" title="Meta Description начинается с ключевого слова">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Meta Description не начинается с ключевого слова">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Title Keyword Match -->
                                    <td class="abp-status-cell">
                                        <?php if (isset($post->title_keyword_match_status) && $post->title_keyword_match_status === 'ok'): ?>
                                            <span class="abp-status-ok" title="Заголовок соответствует ключевому слову">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing" title="Заголовок не соответствует ключевому слову">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="abp-status-cell">
                                        <?php if ($post->alphabet_system_status === 'ok'): ?>
                                            <span class="abp-status-ok">✅</span>
                                        <?php else: ?>
                                            <span class="abp-status-missing">❌</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $post->issues; ?></td>
                                    <td class="abp-progress-column">
                                        <div class="abp-progress-indicator" data-post-id="<?php echo $post->ID; ?>">
                                            <span class="abp-progress-text">—</span>
                                        </div>
                                    </td>
                                <td>
                                    <button class="button button-small abp-optimize-single" data-post-id="<?php echo $post->ID; ?>">
                                        Оптимизировать
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
                </div>
                
                <!-- История проверок -->
                <div class="abp-check-history">
                    <h2>📋 История проверок</h2>
                    <div id="abp-history-chart" class="abp-chart-container">
                        <!-- Здесь будет график -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_quality_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        $posts_table = $wpdb->prefix . 'posts';
        
        // Получаем общую статистику
        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $posts_table WHERE post_type = 'post' AND post_status = 'publish'");
        
        // Получаем последние проверки для каждой статьи (исправленная логика)
        $latest_checks = $wpdb->get_results("
            SELECT q1.* FROM $table_name q1
            INNER JOIN (
                SELECT post_id, MAX(id) as max_id
                FROM $table_name
                GROUP BY post_id
            ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
        ");
        
        // Если нет записей в таблице качества, создаем их для всех статей
        if (empty($latest_checks)) {
            error_log("ABP Quality Monitor: No quality checks found, creating them for all posts");
            $posts = $wpdb->get_results("SELECT ID FROM $posts_table WHERE post_type = 'post' AND post_status = 'publish'");
            foreach ($posts as $post) {
                $this->perform_quality_check($post->ID);
            }
            // Повторно получаем данные
            $latest_checks = $wpdb->get_results("
                SELECT q1.* FROM $table_name q1
                INNER JOIN (
                    SELECT post_id, MAX(id) as max_id
                    FROM $table_name
                    GROUP BY post_id
                ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
            ");
        }
        
        $stats = [
            'total_posts' => (int)$total_posts,
            'quality_posts' => 0,
            'problem_posts' => 0,
            'ai_categorized' => 0,
            'ai_missing' => 0,
            'seo_optimized' => 0,
            'seo_missing' => 0,
            'alphabet_correct' => 0,
            'alphabet_missing' => 0
        ];
        
        foreach ($latest_checks as $check) {
            if ($check->overall_status === 'ok') {
                $stats['quality_posts']++;
            } else {
                $stats['problem_posts']++;
            }
            
            if ($check->ai_category_status === 'ok') {
                $stats['ai_categorized']++;
            } else {
                $stats['ai_missing']++;
            }
            
            if ($check->seo_optimization_status === 'ok') {
                $stats['seo_optimized']++;
            } else {
                $stats['seo_missing']++;
            }
            
            if ($check->alphabet_system_status === 'ok') {
                $stats['alphabet_correct']++;
            } else {
                $stats['alphabet_missing']++;
            }
        }
        
        // Вычисляем проценты
        $stats['quality_percent'] = $total_posts > 0 ? round(($stats['quality_posts'] / $total_posts) * 100, 1) : 0;
        $stats['problem_percent'] = $total_posts > 0 ? round(($stats['problem_posts'] / $total_posts) * 100, 1) : 0;
        $stats['ai_percent'] = $total_posts > 0 ? round(($stats['ai_categorized'] / $total_posts) * 100, 1) : 0;
        $stats['seo_percent'] = $total_posts > 0 ? round(($stats['seo_optimized'] / $total_posts) * 100, 1) : 0;
        $stats['alphabet_percent'] = $total_posts > 0 ? round(($stats['alphabet_correct'] / $total_posts) * 100, 1) : 0;
        
        return $stats;
    }
    
    private function get_problem_posts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        $posts_table = $wpdb->prefix . 'posts';
        
        $posts = $wpdb->get_results("
            SELECT p.ID, p.post_title, q.* FROM $posts_table p
            INNER JOIN (
                SELECT q1.* FROM $table_name q1
                INNER JOIN (
                    SELECT post_id, MAX(id) as max_id
                    FROM $table_name
                    GROUP BY post_id
                ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
                WHERE q1.overall_status != 'ok'
            ) q ON p.ID = q.post_id
            WHERE p.post_type = 'post' AND p.post_status = 'publish'
            ORDER BY q.check_date DESC
            LIMIT 50
        ");
        
        // Добавляем детальную SEO информацию для каждого поста
        foreach ($posts as $post) {
            $post->seo_details = $this->get_seo_details($post->ID);
        }
        
        return $posts;
    }
    
    private function get_all_posts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'abp_quality_checks';
        $posts_table = $wpdb->prefix . 'posts';
        
        $posts = $wpdb->get_results("
            SELECT p.ID, p.post_title, q.* FROM $posts_table p
            LEFT JOIN (
                SELECT q1.* FROM $table_name q1
                INNER JOIN (
                    SELECT post_id, MAX(id) as max_id
                    FROM $table_name
                    GROUP BY post_id
                ) q2 ON q1.post_id = q2.post_id AND q1.id = q2.max_id
            ) q ON p.ID = q.post_id
            WHERE p.post_type = 'post' AND p.post_status = 'publish'
            ORDER BY p.post_date DESC
            LIMIT 100
        ");
        
        // Добавляем детальную SEO информацию для каждого поста
        foreach ($posts as $post) {
            $post->seo_details = $this->get_seo_details($post->ID);
            
            // Если нет данных о качестве, создаем дефолтные
            if (!$post->ai_category_status) {
                $post->ai_category_status = 'missing';
                $post->seo_optimization_status = 'missing';
                $post->alphabet_system_status = 'missing';
                $post->meta_desc_keyword_status = 'missing';
                $post->title_keyword_match_status = 'missing';
                $post->overall_status = 'missing';
                $post->issues = 'Не проверено';
            } else {
                // Инициализируем новые поля если их нет
                if (!isset($post->meta_desc_keyword_status)) {
                    $post->meta_desc_keyword_status = 'missing';
                }
                if (!isset($post->title_keyword_match_status)) {
                    $post->title_keyword_match_status = 'missing';
                }
            }
        }
        
        return $posts;
    }
    
    private function get_seo_details($post_id) {
        $seo_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
        $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        $focus_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        $canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
        
        return [
            'seo_title' => [
                'value' => $seo_title,
                'status' => !empty($seo_title) ? 'ok' : 'missing'
            ],
            'meta_desc' => [
                'value' => $meta_desc,
                'status' => !empty($meta_desc) ? 'ok' : 'missing'
            ],
            'focus_kw' => [
                'value' => $focus_kw,
                'status' => !empty($focus_kw) ? 'ok' : 'missing'
            ],
            'canonical' => [
                'value' => $canonical,
                'status' => !empty($canonical) ? 'ok' : 'missing'
            ]
        ];
    }
    
    public function ajax_bulk_optimize() {
        error_log('=== ABP Quality Monitor: ajax_bulk_optimize START ===');
        
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('ABP Quality Monitor: User does not have manage_options capability for bulk optimization');
            wp_send_json_error('Недостаточно прав');
        }
        
        $problem_posts = $this->get_problem_posts();
        error_log("ABP Quality Monitor: Found " . count($problem_posts) . " problem posts for bulk optimization");
        
        $results = [];
        $success_count = 0;
        $error_count = 0;
        
        foreach ($problem_posts as $post) {
            error_log("ABP Quality Monitor: Optimizing post {$post->ID}: {$post->post_title}");
            $result = $this->optimize_single_post($post->ID);
            
            if ($result === 'optimized') {
                $success_count++;
            } else {
                $error_count++;
            }
            
            $results[] = [
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'result' => $result
            ];
        }
        
        error_log("ABP Quality Monitor: Bulk optimization completed. Success: $success_count, Errors: $error_count");
        error_log('=== ABP Quality Monitor: ajax_bulk_optimize END ===');
        
        wp_send_json_success([
            'results' => $results,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'total_processed' => count($problem_posts)
        ]);
    }
    
    public function ajax_optimize_single() {
        error_log('=== ABP Quality Monitor: ajax_optimize_single START ===');
        error_log('ABP Quality Monitor: POST data: ' . json_encode($_POST));
        error_log('ABP Quality Monitor: Current user ID: ' . get_current_user_id());
        error_log('ABP Quality Monitor: Current user capabilities: ' . json_encode(wp_get_current_user()->allcaps));
        
        // Проверяем nonce
        $nonce = $_POST['nonce'] ?? '';
        $nonce_action = self::NONCE_ACTION;
        error_log("ABP Quality Monitor: Verifying nonce '$nonce' with action '$nonce_action'");
        
        if (!wp_verify_nonce($nonce, $nonce_action)) {
            error_log('ABP Quality Monitor: Nonce verification FAILED');
            wp_send_json_error('Ошибка безопасности');
        }
        error_log('ABP Quality Monitor: Nonce verification SUCCESS');
        
        if (!current_user_can('manage_options')) {
            error_log('ABP Quality Monitor: User does not have manage_options capability');
            wp_send_json_error('Недостаточно прав');
        }
        error_log('ABP Quality Monitor: User permissions OK');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        error_log("ABP Quality Monitor: Post ID from request: $post_id");
        
        if (!$post_id) {
            error_log('ABP Quality Monitor: Invalid post ID');
            wp_send_json_error('Неверный ID поста');
        }
        
        $post = get_post($post_id);
        error_log("ABP Quality Monitor: Post object: " . ($post ? "Found (ID: {$post->ID}, Type: {$post->post_type}, Status: {$post->post_status})" : "Not found"));
        
        if (!$post || $post->post_type !== 'post') {
            error_log('ABP Quality Monitor: Post not found or wrong type');
            wp_send_json_error('Пост не найден');
        }
        
        try {
            error_log("=== ABP Quality Monitor: Starting optimization for post $post_id ===");
            
            // Выполняем оптимизацию
            $result = $this->optimize_single_post($post_id);
            error_log("ABP Quality Monitor: Optimization result for post $post_id: $result");
            
            // Получаем обновленные данные о качестве
            $quality_data = $this->perform_quality_check($post_id);
            error_log("ABP Quality Monitor: Quality data for post $post_id: " . json_encode($quality_data));
            
            // Сохраняем результаты проверки
            $this->save_quality_check($post_id, $quality_data);
            error_log("ABP Quality Monitor: Quality check saved for post $post_id");
            
            // Добавляем детальную SEO информацию
            $quality_data['seo_details'] = $this->get_seo_details($post_id);
            
            $response_data = [
                'message' => 'Статья успешно оптимизирована',
                'post_id' => $post_id,
                'quality_data' => $quality_data,
                'result' => $result
            ];
            
            error_log("ABP Quality Monitor: Sending success response: " . json_encode($response_data));
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            error_log('ABP Quality Monitor Error: ' . $e->getMessage());
            error_log('ABP Quality Monitor Error Stack: ' . $e->getTraceAsString());
            wp_send_json_error('Ошибка оптимизации: ' . $e->getMessage());
        }
        
        error_log('=== ABP Quality Monitor: ajax_optimize_single END ===');
    }
    
    private function optimize_single_post($post_id) {
        error_log("=== ABP Quality Monitor: optimize_single_post START for post $post_id ===");
        
        $post = get_post($post_id);
        error_log("ABP Quality Monitor: Post retrieved: " . ($post ? "Yes (ID: {$post->ID})" : "No"));
        
        if (!$post) {
            error_log("ABP Quality Monitor: Post not found, returning error");
            return 'error';
        }
        
        try {
        // Запускаем AI-категоризацию
            error_log("ABP Quality Monitor: Checking ABP_AI_Categorization class...");
        if (class_exists('ABP_AI_Categorization')) {
                error_log("ABP Quality Monitor: ABP_AI_Categorization class exists, running categorization");
            $ai_cat = new ABP_AI_Categorization();
            $ai_cat->categorize_post_with_ai($post_id);
                error_log("ABP Quality Monitor: AI categorization completed for post $post_id");
            } else {
                error_log("ABP Quality Monitor: ABP_AI_Categorization class NOT found, trying to load plugin file");
                // Попробуем загрузить файл плагина напрямую
                $plugin_file = WP_PLUGIN_DIR . '/abp-ai-categorization/abp-ai-categorization.php';
                if (file_exists($plugin_file)) {
                    require_once $plugin_file;
        if (class_exists('ABP_AI_Categorization')) {
                        error_log("ABP Quality Monitor: ABP_AI_Categorization class loaded successfully");
            $ai_cat = new ABP_AI_Categorization();
            $ai_cat->categorize_post_with_ai($post_id);
                        error_log("ABP Quality Monitor: AI categorization completed for post $post_id");
                    } else {
                        error_log("ABP Quality Monitor: ABP_AI_Categorization class still not found after loading file");
                    }
                } else {
                    error_log("ABP Quality Monitor: Plugin file not found: $plugin_file");
                }
        }
        
        // Запускаем SEO-оптимизацию
            error_log("ABP Quality Monitor: Checking YoastAlphabetIntegration class...");
            if (class_exists('YoastAlphabetIntegration')) {
                error_log("ABP Quality Monitor: YoastAlphabetIntegration class exists");
                $seo_opt = new YoastAlphabetIntegration();
                error_log("ABP Quality Monitor: YoastAlphabetIntegration instance created");
                // Вызываем SEO оптимизацию
                $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
                error_log("ABP Quality Monitor: Current focus keyword: " . ($focus_keyword ?: 'empty'));
                if (empty($focus_keyword)) {
                    // Генерируем focus keyword из заголовка
                    $post_title = get_the_title($post_id);
                    $focus_keyword = $post_title;
                    error_log("ABP Quality Monitor: Generated focus keyword from title: $focus_keyword");
                }
                error_log("ABP Quality Monitor: Calling optimize_post_for_yoast with keyword: $focus_keyword");
                $result = $seo_opt->optimize_post_for_yoast($post_id, $focus_keyword);
                error_log("ABP Quality Monitor: optimize_post_for_yoast result: " . ($result ? 'success' : 'failed'));
                error_log("ABP Quality Monitor: SEO optimization completed");
            } else {
                error_log("ABP Quality Monitor: YoastAlphabetIntegration class NOT found, trying to load plugin file");
                // Попробуем загрузить файл плагина напрямую
                $plugin_file = WP_PLUGIN_DIR . '/yoast-alphabet-integration/yoast-alphabet-integration.php';
                if (file_exists($plugin_file)) {
                    require_once $plugin_file;
                    if (class_exists('YoastAlphabetIntegration')) {
                        error_log("ABP Quality Monitor: YoastAlphabetIntegration class loaded successfully");
                        $seo_opt = new YoastAlphabetIntegration();
                        // Вызываем SEO оптимизацию
                        $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
                        if (empty($focus_keyword)) {
                            // Генерируем focus keyword из заголовка
                            $post_title = get_the_title($post_id);
                            $focus_keyword = $post_title;
                        }
                        $seo_opt->optimize_post_for_yoast($post_id, $focus_keyword);
                        error_log("ABP Quality Monitor: SEO optimization completed");
                    } else {
                        error_log("ABP Quality Monitor: YoastAlphabetIntegration class still not found after loading file");
                    }
                } else {
                    error_log("ABP Quality Monitor: Plugin file not found: $plugin_file");
                }
            }
            
            // Добавляем canonical URL если его нет
            error_log("ABP Quality Monitor: Checking canonical URL...");
            $canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
            if (empty($canonical)) {
                $canonical_url = get_permalink($post_id);
                update_post_meta($post_id, '_yoast_wpseo_canonical', $canonical_url);
                error_log("ABP Quality Monitor: Canonical URL added: $canonical_url");
            } else {
                error_log("ABP Quality Monitor: Canonical URL already exists: $canonical");
        }
        
        // Проверяем алфавитную систему
            error_log("ABP Quality Monitor: Checking ABP_Plugin class...");
        if (class_exists('ABP_Plugin')) {
                error_log("ABP Quality Monitor: ABP_Plugin class exists, saving first letter");
            $abp = new ABP_Plugin();
            $abp->save_first_letter($post_id, $post);
                error_log("ABP Quality Monitor: First letter saved for post $post_id");
            } else {
                error_log("ABP Quality Monitor: ABP_Plugin class NOT found");
        }
        
        // Повторно проверяем качество
            error_log("ABP Quality Monitor: Running quality check for post $post_id");
        $this->check_post_quality($post_id, $post);
            error_log("ABP Quality Monitor: Quality check completed for post $post_id");
        
            error_log("ABP Quality Monitor: optimize_single_post completed successfully for post $post_id");
        return 'optimized';
            
        } catch (Exception $e) {
            error_log('ABP Quality Monitor optimize_single_post error: ' . $e->getMessage());
            error_log('ABP Quality Monitor optimize_single_post error stack: ' . $e->getTraceAsString());
            return 'error';
        }
        
        error_log("=== ABP Quality Monitor: optimize_single_post END for post $post_id ===");
    }
    
    public function ajax_get_quality_stats() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        
        $stats = $this->get_quality_statistics();
        wp_send_json_success($stats);
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_abp-article-quality') {
            return;
        }
        
        wp_enqueue_script('abp-quality-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery'], '1.0.0', true);
        wp_enqueue_style('abp-quality-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', [], '1.0.0');
        
        wp_localize_script('abp-quality-admin', 'abpQuality', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION)
        ]);
    }
    
    /** Проверка соответствия Meta Description фокусному ключевому слову */
    private function check_meta_description_keyword_match($post_id) {
        $meta_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        
        if (empty($meta_desc) || empty($focus_keyword)) {
            return 'missing';
        }
        
        // Проверяем, начинается ли Meta Description с фокусного ключевого слова
        $meta_desc_lower = mb_strtolower(trim($meta_desc), 'UTF-8');
        $keyword_lower = mb_strtolower(trim($focus_keyword), 'UTF-8');
        
        if (mb_strpos($meta_desc_lower, $keyword_lower, 0, 'UTF-8') === 0) {
            return 'ok';
        }
        
        return 'mismatch';
    }
    
    /** Проверка соответствия заголовка фокусному ключевому слову */
    private function check_title_keyword_match($post_id, $post) {
        $focus_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        
        if (empty($focus_keyword) || !$post) {
            return 'missing';
        }
        
        $title_lower = mb_strtolower($post->post_title, 'UTF-8');
        $keyword_lower = mb_strtolower(trim($focus_keyword), 'UTF-8');
        
        // Проверяем, содержит ли заголовок фокусное ключевое слово
        if (mb_strpos($title_lower, $keyword_lower, 0, 'UTF-8') !== false) {
            return 'ok';
        }
        
        return 'mismatch';
    }
    
    /** Публичный метод для тестирования проверки качества */
    public function test_quality_check($post_id) {
        return $this->perform_quality_check($post_id);
    }
    
    /** Публичный метод для тестирования оптимизации */
    public function test_optimize_single_post($post_id) {
        return $this->optimize_single_post($post_id);
    }
    
    /** Публичный метод для получения статистики */
    public function get_public_statistics() {
        return $this->get_quality_statistics();
    }
}

new ABP_Article_Quality_Monitor();



