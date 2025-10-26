<?php
/**
 * Класс для админ-панели
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Admin {
    
    private $logger;
    private $database;
    private $openai_client;
    private $chat_handler;
    
    public function __construct() {
        // Компоненты будут инициализированы в init()
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        $this->database = bizfin_chatgpt_consultant()->get_database();
        $this->openai_client = bizfin_chatgpt_consultant()->get_openai_client();
        $this->chat_handler = bizfin_chatgpt_consultant()->get_chat_handler();
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('wp_ajax_bcc_test_openai', [$this, 'ajax_test_openai']);
        add_action('wp_ajax_bcc_get_statistics', [$this, 'ajax_get_statistics']);
        add_action('wp_ajax_bcc_cleanup_data', [$this, 'ajax_cleanup_data']);
        add_action('wp_ajax_bcc_export_logs', [$this, 'ajax_export_logs']);
        
        $this->logger->info('BCC_Admin initialized');
    }
    
    /**
     * Добавление меню в админ-панель
     */
    public function add_admin_menu() {
        add_menu_page(
            'ChatGPT Консультант',
            'ChatGPT Консультант',
            'manage_options',
            'bizfin-chatgpt-consultant',
            [$this, 'admin_page_main'],
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'bizfin-chatgpt-consultant',
            'Настройки',
            'Настройки',
            'manage_options',
            'bizfin-chatgpt-consultant-settings',
            [$this, 'admin_page_settings']
        );
        
        add_submenu_page(
            'bizfin-chatgpt-consultant',
            'Статистика',
            'Статистика',
            'manage_options',
            'bizfin-chatgpt-consultant-stats',
            [$this, 'admin_page_statistics']
        );
        
        add_submenu_page(
            'bizfin-chatgpt-consultant',
            'Логи',
            'Логи',
            'manage_options',
            'bizfin-chatgpt-consultant-logs',
            [$this, 'admin_page_logs']
        );
    }
    
    /**
     * Инициализация админ-настроек
     */
    public function admin_init() {
        // Регистрируем настройки
        register_setting('bcc_settings', 'bcc_agent_name');
        register_setting('bcc_settings', 'bcc_instructions');
        register_setting('bcc_settings', 'bcc_model');
        register_setting('bcc_settings', 'bcc_max_tokens');
        register_setting('bcc_settings', 'bcc_temperature');
        register_setting('bcc_settings', 'bcc_max_file_size');
        register_setting('bcc_settings', 'bcc_allowed_file_types');
        register_setting('bcc_settings', 'bcc_enable_file_processing');
        register_setting('bcc_settings', 'bcc_enable_vector_db');
        register_setting('bcc_settings', 'bcc_session_timeout');
        register_setting('bcc_settings', 'bcc_max_history_messages');
        register_setting('bcc_settings', 'bcc_enable_logging');
    }
    
    /**
     * Главная страница админ-панели
     */
    public function admin_page_main() {
        ?>
        <div class="wrap">
            <h1>ChatGPT Консультант BizFin Pro</h1>
            
            <div class="bcc-admin-dashboard">
                <div class="bcc-dashboard-grid">
                    <!-- Статус системы -->
                    <div class="bcc-dashboard-card">
                        <h3>📊 Статус системы</h3>
                        <div id="bcc-system-status">
                            <p>Проверка подключения к OpenAI API...</p>
                        </div>
                    </div>
                    
                    <!-- Быстрая статистика -->
                    <div class="bcc-dashboard-card">
                        <h3>📈 Быстрая статистика</h3>
                        <div id="bcc-quick-stats">
                            <p>Загрузка статистики...</p>
                        </div>
                    </div>
                    
                    <!-- Настройки -->
                    <div class="bcc-dashboard-card">
                        <h3>⚙️ Настройки</h3>
                        <p><strong>Агент:</strong> <?php echo esc_html(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME)); ?></p>
                        <p><strong>Модель:</strong> <?php echo esc_html(get_option('bcc_model', BCC_DEFAULT_MODEL)); ?></p>
                        <p><strong>Макс. токенов:</strong> <?php echo esc_html(get_option('bcc_max_tokens', 2000)); ?></p>
                        <p><strong>Температура:</strong> <?php echo esc_html(get_option('bcc_temperature', 0.7)); ?></p>
                        <p><a href="<?php echo admin_url('admin.php?page=bizfin-chatgpt-consultant-settings'); ?>" class="button button-primary">Настроить</a></p>
                    </div>
                    
                    <!-- Действия -->
                    <div class="bcc-dashboard-card">
                        <h3>🔧 Действия</h3>
                        <p>
                            <button type="button" class="button" onclick="testOpenAIConnection()">Тест API</button>
                            <button type="button" class="button" onclick="cleanupOldData()">Очистить старые данные</button>
                        </p>
                        <p>
                            <button type="button" class="button" onclick="exportLogs()">Экспорт логов</button>
                            <button type="button" class="button" onclick="viewStatistics()">Подробная статистика</button>
                        </p>
                    </div>
                </div>
                
                <!-- Логи в реальном времени -->
                <div class="bcc-dashboard-card bcc-full-width">
                    <h3>📝 Последние логи</h3>
                    <div id="bcc-recent-logs" class="bcc-logs-container">
                        <p>Загрузка логов...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            loadSystemStatus();
            loadQuickStats();
            loadRecentLogs();
            
            // Обновляем каждые 30 секунд
            setInterval(function() {
                loadSystemStatus();
                loadQuickStats();
                loadRecentLogs();
            }, 30000);
        });
        
        function loadSystemStatus() {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bcc_test_openai',
                    nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>'
                },
                timeout: 15000
            }).done(function(response){
                if (response && response.success) {
                    jQuery('#bcc-system-status').html('<p style="color: green;">✅ ' + response.data.message + '</p>');
                } else if (response && response.data) {
                    jQuery('#bcc-system-status').html('<p style="color: red;">❌ ' + (response.data.message || response.data) + '</p>');
                } else {
                    jQuery('#bcc-system-status').html('<p style="color: red;">❌ Неизвестная ошибка ответа</p>');
                }
            }).fail(function(xhr, status){
                var msg = status === 'timeout' ? 'Превышено время ожидания' : 'Ошибка соединения (' + status + ')';
                jQuery('#bcc-system-status').html('<p style="color: red;">❌ ' + msg + '</p>');
            }).always(function(){
                // Безусловно снимаем индикатор, если остался
                var $cont = jQuery('#bcc-system-status');
                if ($cont.find('.bcc-loading').length) {
                    $cont.find('.bcc-loading').remove();
                }
            });
        }
        
        function loadQuickStats() {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bcc_get_statistics',
                    nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>'
                },
                timeout: 15000
            }).done(function(response){
                if (response && response.success) {
                    var stats = response.data || {};
                    var html = '<ul>';
                    html += '<li><strong>Сообщений:</strong> ' + (stats.total_messages || 0) + '</li>';
                    html += '<li><strong>Сессий:</strong> ' + (stats.total_sessions || 0) + '</li>';
                    html += '<li><strong>Токенов:</strong> ' + ((stats.total_tokens || 0).toLocaleString()) + '</li>';
                    html += '<li><strong>Файлов:</strong> ' + (stats.total_files || 0) + '</li>';
                    html += '</ul>';
                    jQuery('#bcc-quick-stats').html(html);
                } else {
                    jQuery('#bcc-quick-stats').html('<p style="color: red;">Ошибка загрузки статистики</p>');
                }
            }).fail(function(){
                jQuery('#bcc-quick-stats').html('<p style="color: red;">Ошибка подключения</p>');
            });
        }
        
        function loadRecentLogs() {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bcc_export_logs',
                    nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>',
                    lines: 20
                },
                timeout: 15000
            }).done(function(response){
                if (response && response.success) {
                    jQuery('#bcc-recent-logs').html('<pre>' + response.data + '</pre>');
                } else {
                    jQuery('#bcc-recent-logs').html('<p style="color: red;">Ошибка загрузки логов</p>');
                }
            }).fail(function(){
                jQuery('#bcc-recent-logs').html('<p style="color: red;">Ошибка подключения</p>');
            });
        }
        
        function testOpenAIConnection() {
            jQuery('#bcc-system-status').html('<p>Тестирование подключения...</p>');
            loadSystemStatus();
        }
        
        function cleanupOldData() {
            if (confirm('Вы уверены, что хотите очистить старые данные? Это действие нельзя отменить.')) {
                jQuery.post(ajaxurl, {
                    action: 'bcc_cleanup_data',
                    nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Очистка завершена. Удалено: ' + response.data + ' записей.');
                        loadQuickStats();
                    } else {
                        alert('Ошибка очистки: ' + response.data);
                    }
                });
            }
        }
        
        function exportLogs() {
            window.open(ajaxurl + '?action=bcc_export_logs&nonce=<?php echo wp_create_nonce('bcc_admin_nonce'); ?>&download=1');
        }
        
        function viewStatistics() {
            window.location.href = '<?php echo admin_url('admin.php?page=bizfin-chatgpt-consultant-stats'); ?>';
        }
        </script>
        <?php
    }
    
    /**
     * Страница настроек
     */
    public function admin_page_settings() {
        ?>
        <div class="wrap">
            <h1>Настройки ChatGPT Консультанта</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('bcc_settings');
                do_settings_sections('bcc_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Имя агента</th>
                        <td>
                            <input type="text" name="bcc_agent_name" value="<?php echo esc_attr(get_option('bcc_agent_name', BCC_DEFAULT_AGENT_NAME)); ?>" class="regular-text" />
                            <p class="description">Имя, под которым будет представляться AI консультант</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Инструкции для агента</th>
                        <td>
                            <textarea name="bcc_instructions" rows="10" cols="50" class="large-text"><?php echo esc_textarea(get_option('bcc_instructions', BCC_DEFAULT_INSTRUCTIONS)); ?></textarea>
                            <p class="description">Подробные инструкции для AI агента о том, как он должен себя вести и что может обсуждать</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Модель OpenAI</th>
                        <td>
                            <select name="bcc_model" id="bcc_model">
                                <option value="gpt-4o" <?php selected(get_option('bcc_model', BCC_DEFAULT_MODEL), 'gpt-4o'); ?>>GPT-4o</option>
                                <option value="gpt-4o-mini" <?php selected(get_option('bcc_model', BCC_DEFAULT_MODEL), 'gpt-4o-mini'); ?>>GPT-4o Mini</option>
                                <option value="gpt-4-turbo" <?php selected(get_option('bcc_model', BCC_DEFAULT_MODEL), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                <option value="gpt-4" <?php selected(get_option('bcc_model', BCC_DEFAULT_MODEL), 'gpt-4'); ?>>GPT-4</option>
                                <option value="gpt-3.5-turbo" <?php selected(get_option('bcc_model', BCC_DEFAULT_MODEL), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                            </select>
                            <p class="description">Выберите модель OpenAI для использования</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Максимальное количество токенов</th>
                        <td>
                            <input type="number" name="bcc_max_tokens" value="<?php echo esc_attr(get_option('bcc_max_tokens', 2000)); ?>" min="100" max="4000" class="small-text" />
                            <p class="description">Максимальное количество токенов в ответе (100-4000)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Температура</th>
                        <td>
                            <input type="number" name="bcc_temperature" value="<?php echo esc_attr(get_option('bcc_temperature', 0.7)); ?>" min="0" max="2" step="0.1" class="small-text" />
                            <p class="description">Креативность ответов (0.0 - детерминированные, 2.0 - очень креативные)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Максимальный размер файла</th>
                        <td>
                            <input type="number" name="bcc_max_file_size" value="<?php echo esc_attr(get_option('bcc_max_file_size', 10485760)); ?>" class="regular-text" />
                            <p class="description">Максимальный размер загружаемого файла в байтах (по умолчанию 10MB)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Разрешенные типы файлов</th>
                        <td>
                            <input type="text" name="bcc_allowed_file_types" value="<?php echo esc_attr(get_option('bcc_allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx')); ?>" class="regular-text" />
                            <p class="description">Список разрешенных расширений файлов через запятую</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Обработка файлов</th>
                        <td>
                            <label>
                                <input type="checkbox" name="bcc_enable_file_processing" value="1" <?php checked(get_option('bcc_enable_file_processing', true), 1); ?> />
                                Включить обработку загруженных файлов
                            </label>
                            <p class="description">Позволяет пользователям загружать и обрабатывать файлы</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Векторная база данных</th>
                        <td>
                            <label>
                                <input type="checkbox" name="bcc_enable_vector_db" value="1" <?php checked(get_option('bcc_enable_vector_db', true), 1); ?> />
                                Включить векторную базу данных для контекста
                            </label>
                            <p class="description">Использовать векторную БД для поиска релевантного контекста</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Таймаут сессии</th>
                        <td>
                            <input type="number" name="bcc_session_timeout" value="<?php echo esc_attr(get_option('bcc_session_timeout', 3600)); ?>" class="small-text" />
                            <p class="description">Время жизни сессии в секундах (по умолчанию 1 час)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Максимум сообщений в истории</th>
                        <td>
                            <input type="number" name="bcc_max_history_messages" value="<?php echo esc_attr(get_option('bcc_max_history_messages', 50)); ?>" class="small-text" />
                            <p class="description">Максимальное количество сообщений для сохранения в истории</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Логирование</th>
                        <td>
                            <label>
                                <input type="checkbox" name="bcc_enable_logging" value="1" <?php checked(get_option('bcc_enable_logging', true), 1); ?> />
                                Включить подробное логирование
                            </label>
                            <p class="description">Записывать подробные логи для отладки</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Страница статистики
     */
    public function admin_page_statistics() {
        ?>
        <div class="wrap">
            <h1>Статистика ChatGPT Консультанта</h1>
            
            <div id="bcc-statistics-container">
                <p>Загрузка статистики...</p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            loadDetailedStatistics();
        });
        
        function loadDetailedStatistics() {
            jQuery.post(ajaxurl, {
                action: 'bcc_get_statistics',
                nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    var stats = response.data;
                    var html = '<div class="bcc-stats-grid">';
                    
                    // Общая статистика
                    html += '<div class="bcc-stats-card">';
                    html += '<h3>📊 Общая статистика</h3>';
                    html += '<ul>';
                    html += '<li><strong>Всего сообщений:</strong> ' + stats.total_messages + '</li>';
                    html += '<li><strong>Всего сессий:</strong> ' + stats.total_sessions + '</li>';
                    html += '<li><strong>Всего токенов:</strong> ' + stats.total_tokens.toLocaleString() + '</li>';
                    html += '<li><strong>Всего файлов:</strong> ' + stats.total_files + '</li>';
                    html += '<li><strong>Всего векторов:</strong> ' + stats.total_vectors + '</li>';
                    html += '</ul>';
                    html += '</div>';
                    
                    // Статистика файлов
                    if (stats.files_by_type) {
                        html += '<div class="bcc-stats-card">';
                        html += '<h3>📁 Файлы по типам</h3>';
                        html += '<ul>';
                        for (var type in stats.files_by_type) {
                            html += '<li><strong>' + type + ':</strong> ' + stats.files_by_type[type] + '</li>';
                        }
                        html += '</ul>';
                        html += '</div>';
                    }
                    
                    // Статистика векторов
                    if (stats.vectors_by_type) {
                        html += '<div class="bcc-stats-card">';
                        html += '<h3>🔍 Векторы по типам</h3>';
                        html += '<ul>';
                        for (var type in stats.vectors_by_type) {
                            html += '<li><strong>' + type + ':</strong> ' + stats.vectors_by_type[type] + '</li>';
                        }
                        html += '</ul>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    
                    jQuery('#bcc-statistics-container').html(html);
                } else {
                    jQuery('#bcc-statistics-container').html('<p style="color: red;">Ошибка загрузки статистики</p>');
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Страница логов
     */
    public function admin_page_logs() {
        ?>
        <div class="wrap">
            <h1>Логи ChatGPT Консультанта</h1>
            
            <div class="bcc-logs-actions">
                <button type="button" class="button" onclick="refreshLogs()">Обновить</button>
                <button type="button" class="button" onclick="clearLogs()">Очистить</button>
                <button type="button" class="button" onclick="exportLogs()">Экспорт</button>
            </div>
            
            <div id="bcc-logs-container" class="bcc-logs-container">
                <p>Загрузка логов...</p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            loadLogs();
        });
        
        function loadLogs() {
            jQuery.post(ajaxurl, {
                action: 'bcc_export_logs',
                nonce: '<?php echo wp_create_nonce('bcc_admin_nonce'); ?>',
                lines: 100
            }, function(response) {
                if (response.success) {
                    jQuery('#bcc-logs-container').html('<pre>' + response.data + '</pre>');
                } else {
                    jQuery('#bcc-logs-container').html('<p style="color: red;">Ошибка загрузки логов</p>');
                }
            });
        }
        
        function refreshLogs() {
            loadLogs();
        }
        
        function clearLogs() {
            if (confirm('Вы уверены, что хотите очистить все логи?')) {
                // Здесь можно добавить AJAX запрос для очистки логов
                alert('Функция очистки логов будет добавлена в следующей версии');
            }
        }
        
        function exportLogs() {
            window.open(ajaxurl + '?action=bcc_export_logs&nonce=<?php echo wp_create_nonce('bcc_admin_nonce'); ?>&download=1');
        }
        </script>
        <?php
    }
    
    /**
     * AJAX: Тест подключения к OpenAI
     */
    public function ajax_test_openai() {
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $result = $this->openai_client->test_connection();
        // Возвращаем в формате WP { success: true, data: {...} }
        if ($result && isset($result['success']) && $result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message'] ?? 'Неизвестная ошибка');
        }
    }
    
    /**
     * AJAX: Получение статистики
     */
    public function ajax_get_statistics() {
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $stats = $this->chat_handler->get_chat_statistics(30);
        if (is_array($stats)) {
            wp_send_json_success($stats);
        } else {
            wp_send_json_error('Ошибка получения статистики');
        }
    }
    
    /**
     * AJAX: Очистка старых данных
     */
    public function ajax_cleanup_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $deleted_count = $this->chat_handler->cleanup_old_sessions(30);
        
        if ($deleted_count !== false) {
            wp_send_json_success($deleted_count);
        } else {
            wp_send_json_error('Ошибка очистки данных');
        }
    }
    
    /**
     * AJAX: Экспорт логов
     */
    public function ajax_export_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'bcc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $lines = intval($_POST['lines'] ?? 100);
        $download = isset($_GET['download']) && $_GET['download'] == '1';
        
        $logger = bizfin_chatgpt_consultant()->get_logger();
        $log_content = $logger->get_log_content($lines);
        
        if ($download) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="bizfin-chatgpt-logs-' . date('Y-m-d-H-i-s') . '.txt"');
            echo $log_content;
            exit;
        }
        
        wp_send_json_success($log_content);
    }
}
