<?php
/*
Plugin Name: Company Rating Checker
Description: Плагин для проверки рейтинга предприятия по ИНН с интеграцией API DaData
Version: 1.0
Author: Business Guarantees
Text Domain: company-rating-checker
*/

if (!defined('ABSPATH')) { exit; }

define('CRC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CRC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CRC_VERSION', '1.0');

// Подключаем дополнительные классы
require_once CRC_PLUGIN_PATH . 'simple-arbitration.php';
require_once CRC_PLUGIN_PATH . 'zakupki-api.php';
require_once CRC_PLUGIN_PATH . 'cache-manager.php';
require_once CRC_PLUGIN_PATH . 'fns-api.php';
require_once CRC_PLUGIN_PATH . 'rosstat-api.php';
require_once CRC_PLUGIN_PATH . 'advanced-analytics.php';
require_once CRC_PLUGIN_PATH . 'data-export.php';
require_once CRC_PLUGIN_PATH . 'efrsb-api.php';
require_once CRC_PLUGIN_PATH . 'rnp-api.php';
require_once CRC_PLUGIN_PATH . 'fssp-api.php';
require_once CRC_PLUGIN_PATH . 'egrul-api-improved.php';
require_once CRC_PLUGIN_PATH . 'msp-api-improved.php';
require_once CRC_PLUGIN_PATH . 'zakupki-api-improved.php';
require_once CRC_PLUGIN_PATH . 'fns-api-improved.php';
require_once CRC_PLUGIN_PATH . 'fssp-api-improved.php';
require_once CRC_PLUGIN_PATH . 'zakupki-api-real.php';

class CompanyRatingChecker {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_crc_get_company_rating', array($this, 'ajax_get_company_rating'));
        add_action('wp_ajax_nopriv_crc_get_company_rating', array($this, 'ajax_get_company_rating'));
        add_action('wp_ajax_crc_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_crc_cleanup_expired_cache', array($this, 'ajax_cleanup_expired_cache'));
        add_action('wp_ajax_crc_export_company', array($this, 'ajax_export_company'));
        add_action('wp_ajax_crc_get_export_files', array($this, 'ajax_get_export_files'));
        add_action('wp_ajax_crc_delete_export_file', array($this, 'ajax_delete_export_file'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        
        add_shortcode('company_rating_checker', array($this, 'display_rating_form'));
    }
    
    public function init() {
        // Инициализация плагина
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('crc-style', CRC_PLUGIN_URL . 'assets/style.css', array(), CRC_VERSION);
        wp_enqueue_script('crc-script', CRC_PLUGIN_URL . 'assets/script.js', array('jquery'), CRC_VERSION, true);
        
        wp_localize_script('crc-script', 'crc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crc_nonce'),
            'is_logged_in' => is_user_logged_in()
        ));
    }
    
    public function display_rating_form($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Проверка рейтинга предприятия',
            'button_text' => 'Получить рейтинг',
            'full_width' => 'false'
        ), $atts);
        
        $container_class = $atts['full_width'] === 'true' ? 'crc-container crc-full-width' : 'crc-container';
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($container_class); ?>">
            <div class="crc-form-wrapper">
                <h3 class="crc-title"><?php echo esc_html($atts['title']); ?></h3>
                <form id="crc-rating-form" class="crc-form">
                    <div class="crc-form-group">
                        <label for="crc-inn" class="crc-label">ИНН предприятия:</label>
                        <input type="text" 
                               id="crc-inn" 
                               name="inn" 
                               class="crc-input" 
                               placeholder="Введите 10 или 12 цифр ИНН"
                               maxlength="12"
                               required>
                        <div class="crc-input-hint">Введите корректный ИНН (10 цифр для организаций, 12 для ИП)</div>
                    </div>
                    <button type="submit" class="crc-button" id="crc-submit-btn">
                        <span class="crc-button-text"><?php echo esc_html($atts['button_text']); ?></span>
                        <span class="crc-spinner" style="display: none;">⏳</span>
                    </button>
                </form>
                <div id="crc-result" class="crc-result" style="display: none;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_get_company_rating() {
        // Увеличиваем лимит времени выполнения для PHP
        set_time_limit(120); // 2 минуты
        
        // Проверяем nonce только если он передан
        if (isset($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'crc_nonce')) {
                wp_send_json_error('Ошибка безопасности');
            }
        } else {
            // Для неавторизованных пользователей разрешаем запрос без nonce
            // но добавляем дополнительную проверку
            if (is_user_logged_in()) {
                wp_send_json_error('Ошибка безопасности');
            }
        }
        
        $inn = sanitize_text_field($_POST['inn'] ?? '');
        
        if (!$this->validate_inn($inn)) {
            wp_send_json_error('Некорректный формат ИНН');
        }
        
        // Проверяем кэш с помощью менеджера кэша
        global $crc_cache_manager;
        $cache_key = $crc_cache_manager->get_company_cache_key($inn);
        $cached_result = $crc_cache_manager->get($cache_key);
        
        if ($cached_result !== false) {
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: Using cached result for INN ' . $inn);
            }
            wp_send_json_success($cached_result);
        }
        
        $company_data = $this->get_company_data($inn);
        
        if (is_wp_error($company_data)) {
            wp_send_json_error($company_data->get_error_message());
        }
        
        // Получаем дополнительные данные из ЕГРЮЛ с обработкой ошибок
        $egrul_data = $this->get_egrul_data($inn);
        if (is_wp_error($egrul_data)) {
            error_log('EGRUL API error: ' . $egrul_data->get_error_message());
            $egrul_data = null;
        }
        
        // Получаем данные ФНС для корректного определения МСП с обработкой ошибок
        $fns_data = $this->get_fns_data($inn);
        if (is_wp_error($fns_data)) {
            error_log('FNS API error: ' . $fns_data->get_error_message());
            $fns_data = null;
        }
        
        // Получаем данные МСП с учетом данных ФНС с обработкой ошибок
        $msp_data = $this->get_msp_data($inn, $fns_data);
        if (is_wp_error($msp_data)) {
            error_log('MSP API error: ' . $msp_data->get_error_message());
            $msp_data = null;
        }
        
        // Получаем данные об арбитражных делах с обработкой ошибок
        $arbitration_data = $this->get_arbitration_data($inn);
        if (is_wp_error($arbitration_data)) {
            error_log('Arbitration API error: ' . $arbitration_data->get_error_message());
            $arbitration_data = null;
        }
        
        // Получаем данные о государственных закупках с обработкой ошибок
        $zakupki_data = $this->get_zakupki_data($inn);
        if (is_wp_error($zakupki_data)) {
            error_log('Zakupki API error: ' . $zakupki_data->get_error_message());
            $zakupki_data = null;
        }
        
        // Добавляем дополнительные данные к основным
        if ($egrul_data) {
            $company_data['egrul'] = $egrul_data;
        }
        if ($msp_data) {
            $company_data['msp'] = $msp_data;
        }
        if ($arbitration_data) {
            $company_data['arbitration'] = $arbitration_data;
        }
        if ($zakupki_data) {
            $company_data['zakupki'] = $zakupki_data;
        }
        if ($fns_data) {
            $company_data['fns'] = $fns_data;
        }
        
        // Получаем статистические данные из Росстат с обработкой ошибок
        $rosstat_data = $this->get_rosstat_data($inn);
        if (is_wp_error($rosstat_data)) {
            error_log('Rosstat API error: ' . $rosstat_data->get_error_message());
            $rosstat_data = null;
        }
        if ($rosstat_data) {
            $company_data['rosstat'] = $rosstat_data;
        }
        
        // Получаем данные о банкротстве из ЕФРСБ с обработкой ошибок
        $efrsb_data = $this->get_efrsb_data($inn);
        if (is_wp_error($efrsb_data)) {
            error_log('EFRSB API error: ' . $efrsb_data->get_error_message());
            $efrsb_data = null;
        }
        if ($efrsb_data) {
            $company_data['efrsb'] = $efrsb_data;
        }
        
        // Получаем данные о недобросовестных поставщиках из РНП с обработкой ошибок
        $rnp_data = $this->get_rnp_data($inn);
        if (is_wp_error($rnp_data)) {
            error_log('RNP API error: ' . $rnp_data->get_error_message());
            $rnp_data = null;
        }
        if ($rnp_data) {
            $company_data['rnp'] = $rnp_data;
        }
        
        // Получаем данные об исполнительных производствах из ФССП с обработкой ошибок
        $fssp_data = $this->get_fssp_data($inn);
        if (is_wp_error($fssp_data)) {
            error_log('FSSP API error: ' . $fssp_data->get_error_message());
            $fssp_data = null;
        }
        if ($fssp_data) {
            $company_data['fssp'] = $fssp_data;
        }
        
        $rating = $this->calculate_company_rating($company_data);
        
        $result = array(
            'company' => $company_data,
            'rating' => $rating
        );
        
        // Кэшируем результат с помощью менеджера кэша
        $crc_cache_manager->set($cache_key, $result);
        
        if (get_option('crc_debug_mode', 0)) {
            error_log('CRC Debug: Cached result for INN ' . $inn);
        }
        
        wp_send_json_success($result);
    }
    
    private function validate_inn($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        
        if (strlen($inn) !== 10 && strlen($inn) !== 12) {
            return false;
        }
        
        if (strlen($inn) === 10) {
            $weights = array(2, 4, 10, 3, 5, 9, 4, 6, 8);
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += intval($inn[$i]) * $weights[$i];
            }
            $check_digit = $sum % 11;
            if ($check_digit > 9) {
                $check_digit = $check_digit % 10;
            }
            return $check_digit == intval($inn[9]);
        }
        
        if (strlen($inn) === 12) {
            $weights1 = array(7, 2, 4, 10, 3, 5, 9, 4, 6, 8);
            $weights2 = array(3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8);
            
            $sum1 = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum1 += intval($inn[$i]) * $weights1[$i];
            }
            $check_digit1 = $sum1 % 11;
            if ($check_digit1 > 9) {
                $check_digit1 = $check_digit1 % 10;
            }
            
            $sum2 = 0;
            for ($i = 0; $i < 11; $i++) {
                $sum2 += intval($inn[$i]) * $weights2[$i];
            }
            $check_digit2 = $sum2 % 11;
            if ($check_digit2 > 9) {
                $check_digit2 = $check_digit2 % 10;
            }
            
            return $check_digit1 == intval($inn[10]) && $check_digit2 == intval($inn[11]);
        }
        
        return false;
    }
    
    private function get_company_data($inn) {
        $dadata_token = get_option('crc_dadata_token');
        
        if (empty($dadata_token)) {
            return new WP_Error('no_api_key', 'API ключ DaData не настроен. Перейдите в настройки плагина.');
        }
        
        $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $dadata_token,
            'Accept' => 'application/json'
        );
        
        $body = json_encode(array('query' => $inn));
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body,
            'timeout' => 15, // Уменьшаем таймаут для DaData до 15 секунд
            'blocking' => true
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Ошибка API DaData: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data['suggestions'])) {
            return new WP_Error('company_not_found', 'Предприятие с указанным ИНН не найдено');
        }
        
        return $data['suggestions'][0]['data'];
    }
    
    private function get_egrul_data($inn) {
        try {
            $egrul_api = new EGRULApiImproved();
            $egrul_info = $egrul_api->get_egrul_data($inn);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: EGRUL data retrieved for INN ' . $inn);
            }
            
            return $egrul_info;
        } catch (Exception $e) {
            error_log('EGRUL data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_msp_data($inn, $fns_data = null) {
        try {
            $msp_api = new MSPApiImproved();
            $msp_info = $msp_api->get_msp_data($inn, $fns_data);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: MSP data retrieved for INN ' . $inn);
            }
            
            return $msp_info;
        } catch (Exception $e) {
            error_log('MSP data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_arbitration_data($inn) {
        // Проверяем, включен ли анализ арбитражных данных
        if (!get_option('crc_arbitration_enabled', 1)) {
            return null;
        }
        
        try {
            $arbitration_api = new SimpleArbitrationAPI();
            $arbitration_info = $arbitration_api->get_arbitration_info($inn);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: Arbitration data retrieved for INN ' . $inn);
            }
            
            return $arbitration_info;
        } catch (Exception $e) {
            error_log('Arbitration data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_zakupki_data($inn) {
        // Проверяем, включен ли анализ госзакупок
        if (!get_option('crc_zakupki_enabled', 1)) {
            return null;
        }
        
        try {
            // Сначала пытаемся использовать реальную версию
            $zakupki_api = new ZakupkiApiReal();
            $zakupki_info = $zakupki_api->get_zakupki_info($inn);
            
            // Если реальная версия не дала результатов, используем улучшенную
            if (!$zakupki_info || is_wp_error($zakupki_info)) {
                $zakupki_api_improved = new ZakupkiApiImproved();
                $zakupki_info = $zakupki_api_improved->get_zakupki_info($inn);
            }
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: Zakupki data retrieved for INN ' . $inn);
            }
            
            return $zakupki_info;
        } catch (Exception $e) {
            error_log('Zakupki data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function calculate_company_rating($company_data) {
        $score = 0;
        $max_score = 100; // Базовый максимум
        
        // Добавляем баллы за дополнительные факторы в зависимости от настроек
        if (get_option('crc_arbitration_enabled', 1)) {
            $max_score += 10; // Арбитражные риски
        }
        // ФССП, госзакупки и ФНС отключены из-за некорректных данных
        // if (get_option('crc_zakupki_enabled', 1)) {
        //     $max_score += 10; // Государственные закупки
        // }
        // if (get_option('crc_fns_enabled', 1)) {
        //     $max_score += 15; // ФНС данные
        // }
        if (get_option('crc_rosstat_enabled', 1)) {
            $max_score += 10; // Росстат данные
        }
        if (get_option('crc_efrsb_enabled', 1)) {
            $max_score += 20; // ЕФРСБ данные
        }
        if (get_option('crc_rnp_enabled', 1)) {
            $max_score += 15; // РНП данные
        }
        // if (get_option('crc_fssp_enabled', 1)) {
        //     $max_score += 15; // ФССП данные
        // }
        
        $factors = array();
        
        // Фактор 1: Статус компании (25 баллов) - увеличил важность
        $status_score = $this->calculate_status_score($company_data);
        $score += $status_score;
        $factors['status'] = array(
            'name' => 'Статус компании',
            'score' => $status_score,
            'max_score' => 25,
            'description' => $this->get_status_description($company_data)
        );
        
        // Фактор 2: Время существования (20 баллов) - увеличил важность
        $age_score = $this->calculate_age_score($company_data);
        $score += $age_score;
        $factors['age'] = array(
            'name' => 'Время существования',
            'score' => $age_score,
            'max_score' => 20,
            'description' => $this->get_age_description($company_data)
        );
        
        // Фактор 3: Размер уставного капитала (15 баллов)
        $capital_score = $this->calculate_capital_score($company_data);
        $score += $capital_score;
        $factors['capital'] = array(
            'name' => 'Уставный капитал',
            'score' => $capital_score,
            'max_score' => 15,
            'description' => $this->get_capital_description($company_data)
        );
        
        // Фактор 4: Количество сотрудников (10 баллов)
        $employees_score = $this->calculate_employees_score($company_data);
        $score += $employees_score;
        $factors['employees'] = array(
            'name' => 'Количество сотрудников',
            'score' => $employees_score,
            'max_score' => 10,
            'description' => $this->get_employees_description($company_data)
        );
        
        // Фактор 5: Вид деятельности (8 баллов) - уменьшил
        $activity_score = $this->calculate_activity_score($company_data);
        $score += $activity_score;
        $factors['activity'] = array(
            'name' => 'Вид деятельности',
            'score' => $activity_score,
            'max_score' => 8,
            'description' => $this->get_activity_description($company_data)
        );
        
        // Фактор 6: Регион (7 баллов) - уменьшил
        $region_score = $this->calculate_region_score($company_data);
        $score += $region_score;
        $factors['region'] = array(
            'name' => 'Регион регистрации',
            'score' => $region_score,
            'max_score' => 7,
            'description' => $this->get_region_description($company_data)
        );
        
        // Фактор 7: МСП статус (10 баллов)
        $msp_score = $this->calculate_msp_score($company_data);
        $score += $msp_score;
        $factors['msp'] = array(
            'name' => 'Статус МСП',
            'score' => $msp_score,
            'max_score' => 10,
            'description' => $this->get_msp_description($company_data)
        );
        
        // Фактор 8: Финансовые показатели (5 баллов) - уменьшил
        $financial_score = $this->calculate_financial_score($company_data);
        $score += $financial_score;
        $factors['financial'] = array(
            'name' => 'Финансовые показатели',
            'score' => $financial_score,
            'max_score' => 5,
            'description' => $this->get_financial_description($company_data)
        );
        
        // Фактор 9: Арбитражные риски (10 баллов) - новый фактор
        $arbitration_score = $this->calculate_arbitration_score($company_data);
        $score += $arbitration_score;
        $factors['arbitration'] = array(
            'name' => 'Арбитражные риски',
            'score' => $arbitration_score,
            'max_score' => 10,
            'description' => $this->get_arbitration_description($company_data)
        );
        
        // Фактор 10: Государственные закупки (10 баллов) - ОТКЛЮЧЕН из-за некорректных данных
        // $zakupki_score = $this->calculate_zakupki_score($company_data);
        // $score += $zakupki_score;
        // $factors['zakupki'] = array(
        //     'name' => 'Государственные закупки',
        //     'score' => $zakupki_score,
        //     'max_score' => 10,
        //     'description' => $this->get_zakupki_description($company_data)
        // );
        
        // Фактор 11: ФНС данные (15 баллов) - новый фактор
        if (get_option('crc_fns_enabled', 1)) {
            $fns_score = $this->calculate_fns_score($company_data);
            $score += $fns_score;
            $factors['fns'] = array(
                'name' => 'ФНС данные',
                'score' => $fns_score,
                'max_score' => 15,
                'description' => $this->get_fns_description($company_data)
            );
        }
        
        // Фактор 12: Росстат данные (10 баллов) - новый фактор
        if (get_option('crc_rosstat_enabled', 1)) {
            $rosstat_score = $this->calculate_rosstat_score($company_data);
            $score += $rosstat_score;
            $factors['rosstat'] = array(
                'name' => 'Росстат данные',
                'score' => $rosstat_score,
                'max_score' => 10,
                'description' => $this->get_rosstat_description($company_data)
            );
        }
        
        // Фактор 13: ЕФРСБ данные (20 баллов) - новый фактор
        if (get_option('crc_efrsb_enabled', 1)) {
            $efrsb_score = $this->calculate_efrsb_score($company_data);
            $score += $efrsb_score;
            $factors['efrsb'] = array(
                'name' => 'ЕФРСБ данные',
                'score' => $efrsb_score,
                'max_score' => 20,
                'description' => $this->get_efrsb_description($company_data)
            );
        }
        
        // Фактор 14: РНП данные (15 баллов) - новый фактор
        if (get_option('crc_rnp_enabled', 1)) {
            $rnp_score = $this->calculate_rnp_score($company_data);
            $score += $rnp_score;
            $factors['rnp'] = array(
                'name' => 'РНП данные',
                'score' => $rnp_score,
                'max_score' => 15,
                'description' => $this->get_rnp_description($company_data)
            );
        }
        
        // Фактор 15: ФССП данные (15 баллов) - новый фактор
        if (get_option('crc_fssp_enabled', 1)) {
            $fssp_score = $this->calculate_fssp_score($company_data);
            $score += $fssp_score;
            $factors['fssp'] = array(
                'name' => 'ФССП данные',
                'score' => $fssp_score,
                'max_score' => 15,
                'description' => $this->get_fssp_description($company_data)
            );
        }
        
        // Добавляем расширенную аналитику
        $advanced_analytics = $this->get_advanced_analytics($company_data);
        
        $rating = $this->get_rating_level($score);
        
        return array(
            'total_score' => $score,
            'max_score' => $max_score,
            'rating' => $rating,
            'factors' => $factors,
            'advanced_analytics' => $advanced_analytics
        );
    }
    
    private function calculate_status_score($data) {
        $status = $data['state']['status'] ?? '';
        
        switch ($status) {
            case 'ACTIVE':
                return 25; // Максимальный балл для действующих
            case 'LIQUIDATING':
                return 5;  // Низкий балл для ликвидирующихся
            case 'LIQUIDATED':
                return 0;  // Нулевой балл для ликвидированных
            default:
                return 10; // Средний балл для неизвестного статуса
        }
    }
    
    private function calculate_age_score($data) {
        $registration_date = $data['state']['registration_date'] ?? null;
        if (!$registration_date) {
            return 0;
        }
        
        $registration = new DateTime('@' . intval($registration_date / 1000));
        $now = new DateTime();
        $age = $now->diff($registration)->y;
        
        // Улучшенная шкала оценки возраста
        if ($age >= 15) return 20; // Очень стабильные компании
        if ($age >= 10) return 18; // Стабильные компании
        if ($age >= 7) return 15;  // Зрелые компании
        if ($age >= 5) return 12;  // Развивающиеся компании
        if ($age >= 3) return 8;   // Молодые компании
        if ($age >= 1) return 4;   // Новые компании
        return 1; // Совсем новые
    }
    
    private function calculate_capital_score($data) {
        $capital = $data['capital'] ?? null;
        if (!$capital) {
            return 5;
        }
        
        $capital_value = intval($capital['value']);
        
        if ($capital_value >= 10000000) return 15;
        if ($capital_value >= 1000000) return 12;
        if ($capital_value >= 100000) return 8;
        if ($capital_value >= 10000) return 5;
        return 2;
    }
    
    private function calculate_employees_score($data) {
        $employees = $data['employee_count'] ?? null;
        if (!$employees) {
            return 5;
        }
        
        $count = intval($employees);
        
        if ($count >= 1000) return 10;
        if ($count >= 500) return 8;
        if ($count >= 100) return 6;
        if ($count >= 50) return 4;
        if ($count >= 10) return 2;
        return 1;
    }
    
    private function calculate_activity_score($data) {
        $okved = $data['okved'] ?? '';
        
        // Приоритетные отрасли (IT, строительство, торговля, производство)
        $priority_okveds = array('62', '41', '46', '47', '28', '10', '43', '45');
        
        // Высокоприоритетные отрасли
        $high_priority_okveds = array('62', '41', '28'); // IT, строительство, производство
        
        foreach ($high_priority_okveds as $okved_code) {
            if (strpos($okved, $okved_code) === 0) {
                return 8; // Максимальный балл для приоритетных
            }
        }
        
        foreach ($priority_okveds as $okved_code) {
            if (strpos($okved, $okved_code) === 0) {
                return 6; // Хороший балл для обычных приоритетных
            }
        }
        
        return 4; // Базовый балл для остальных
    }
    
    private function calculate_region_score($data) {
        $region = $data['address']['data']['region'] ?? '';
        
        // Высокоприоритетные регионы
        $high_priority_regions = array('Москва', 'Санкт-Петербург');
        
        // Приоритетные регионы
        $priority_regions = array('Московская', 'Ленинградская', 'Краснодарский', 'Свердловская', 'Новосибирская');
        
        foreach ($high_priority_regions as $priority_region) {
            if (strpos($region, $priority_region) !== false) {
                return 7; // Максимальный балл для столиц
            }
        }
        
        foreach ($priority_regions as $priority_region) {
            if (strpos($region, $priority_region) !== false) {
                return 5; // Хороший балл для развитых регионов
            }
        }
        
        return 3; // Базовый балл для остальных регионов
    }
    
    private function calculate_msp_score($data) {
        $msp_data = $data['msp'] ?? null;
        if (!$msp_data) {
            return 5; // Базовый балл если нет данных
        }
        
        $status = $msp_data['status'] ?? '';
        
        switch ($status) {
            case 'micro':
                return 8; // Микропредприятие
            case 'small':
                return 10; // Малое предприятие - максимальный балл
            case 'medium':
                return 9; // Среднее предприятие
            case 'not_msp':
                return 6; // Не МСП, но есть данные
            case 'not_found':
                return 4; // Не найден в реестре
            default:
                return 5;
        }
    }
    
    private function calculate_financial_score($data) {
        // Базовая оценка - в будущем можно добавить анализ финансовых показателей
        return 3; // Базовый балл
    }
    
    private function get_rating_level($score) {
        if ($score >= 90) return array('level' => 'AAA', 'name' => 'Отличный', 'color' => '#28a745');
        if ($score >= 80) return array('level' => 'AA', 'name' => 'Очень хороший', 'color' => '#20c997');
        if ($score >= 70) return array('level' => 'A', 'name' => 'Хороший', 'color' => '#17a2b8');
        if ($score >= 60) return array('level' => 'BBB', 'name' => 'Удовлетворительный', 'color' => '#ffc107');
        if ($score >= 50) return array('level' => 'BB', 'name' => 'Ниже среднего', 'color' => '#fd7e14');
        if ($score >= 40) return array('level' => 'B', 'name' => 'Плохой', 'color' => '#dc3545');
        return array('level' => 'CCC', 'name' => 'Очень плохой', 'color' => '#6c757d');
    }
    
    private function get_status_description($data) {
        $status = $data['state']['status'] ?? '';
        $map = array(
            'ACTIVE' => 'Компания действует',
            'LIQUIDATING' => 'В процессе ликвидации',
            'LIQUIDATED' => 'Ликвидирована'
        );
        return $map[$status] ?? 'Статус не определен';
    }
    
    private function get_age_description($data) {
        $registration_date = $data['state']['registration_date'] ?? null;
        if (!$registration_date) return 'Дата регистрации не указана';
        
        $registration = new DateTime('@' . intval($registration_date / 1000));
        $now = new DateTime();
        $age = $now->diff($registration)->y;
        
        return "Компания существует {$age} лет";
    }
    
    private function get_capital_description($data) {
        $capital = $data['capital'] ?? null;
        if (!$capital) return 'Уставный капитал не указан';
        
        $value = intval($capital['value']);
        return 'Уставный капитал: ' . number_format($value, 0, ',', ' ') . ' руб.';
    }
    
    private function get_employees_description($data) {
        $employees = $data['employee_count'] ?? null;
        if (!$employees) return 'Количество сотрудников не указано';
        
        return 'Сотрудников: ' . number_format(intval($employees), 0, ',', ' ');
    }
    
    private function get_activity_description($data) {
        $okved = $data['okved'] ?? 'Не указан';
        return 'ОКВЭД: ' . $okved;
    }
    
    private function get_region_description($data) {
        $region = $data['address']['data']['region'] ?? 'Не указан';
        return 'Регион: ' . $region;
    }
    
    private function get_msp_description($data) {
        $msp_data = $data['msp'] ?? null;
        if (!$msp_data) {
            return 'Данные МСП недоступны';
        }
        
        return $msp_data['category'] ?? 'Статус не определен';
    }
    
    private function get_financial_description($data) {
        return 'Базовая оценка (будет расширена)';
    }
    
    private function calculate_arbitration_score($data) {
        $arbitration_data = $data['arbitration'] ?? null;
        if (!$arbitration_data) {
            return 5; // Базовый балл если нет данных
        }
        
        $risk_level = $arbitration_data['risk_level'] ?? 'unknown';
        $risk_score = $arbitration_data['risk_score'] ?? 0;
        
        switch ($risk_level) {
            case 'low':
                return 10; // Максимальный балл для низкого риска
            case 'medium':
                return 6;  // Средний балл
            case 'high':
                return 2;  // Низкий балл для высокого риска
            default:
                return 5;  // Базовый балл для неизвестного риска
        }
    }
    
    private function get_arbitration_description($data) {
        $arbitration_data = $data['arbitration'] ?? null;
        if (!$arbitration_data) {
            return 'Данные об арбитражных делах недоступны';
        }
        
        $risk_level = $arbitration_data['risk_level'] ?? 'unknown';
        $recommendation = $arbitration_data['recommendation'] ?? '';
        $risk_score = $arbitration_data['risk_score'] ?? 0;
        
        $level_text = array(
            'low' => 'Низкий риск',
            'medium' => 'Средний риск', 
            'high' => 'Высокий риск',
            'unknown' => 'Неизвестный риск'
        );
        
        $level = $level_text[$risk_level] ?? 'Неизвестный риск';
        
        return "{$level} (балл: {$risk_score}/100). {$recommendation}";
    }
    
    private function calculate_zakupki_score($data) {
        $zakupki_data = $data['zakupki'] ?? null;
        if (!$zakupki_data) {
            return 5; // Базовый балл если нет данных
        }
        
        $reputation_score = $zakupki_data['reputation_score'] ?? 0;
        $reputation_level = $zakupki_data['summary']['reputation_level'] ?? 'unknown';
        
        // Конвертируем репутационный балл (0-100) в балл фактора (0-10)
        $score = intval($reputation_score / 10);
        
        // Дополнительные бонусы за высокую репутацию
        switch ($reputation_level) {
            case 'excellent':
                $score = min(10, $score + 2);
                break;
            case 'good':
                $score = min(10, $score + 1);
                break;
            case 'average':
                // Без изменений
                break;
            case 'poor':
            case 'very_poor':
                $score = max(1, $score - 1);
                break;
        }
        
        return $score;
    }
    
    private function get_zakupki_description($data) {
        $zakupki_data = $data['zakupki'] ?? null;
        if (!$zakupki_data) {
            return 'Данные о государственных закупках недоступны';
        }
        
        $reputation_level = $zakupki_data['summary']['reputation_level'] ?? 'unknown';
        $recommendation = $zakupki_data['summary']['recommendation'] ?? '';
        $reputation_score = $zakupki_data['reputation_score'] ?? 0;
        $contracts = $zakupki_data['total_contracts'] ?? 0;
        $amount = $zakupki_data['total_amount'] ?? 0;
        
        $level_text = array(
            'excellent' => 'Отличная репутация',
            'good' => 'Хорошая репутация',
            'average' => 'Средняя репутация',
            'poor' => 'Низкая репутация',
            'very_poor' => 'Очень низкая репутация',
            'unknown' => 'Неизвестная репутация'
        );
        
        $level = $level_text[$reputation_level] ?? 'Неизвестная репутация';
        
        $description = "{$level} (балл: {$reputation_score}/100)";
        
        if ($contracts > 0) {
            $description .= ". Контрактов: {$contracts}, сумма: " . number_format($amount, 0, ',', ' ') . " руб.";
        }
        
        return $description;
    }
    
    private function calculate_fns_score($data) {
        $fns_data = $data['fns'] ?? null;
        if (!$fns_data) {
            return 8; // Базовый балл если нет данных
        }
        
        $score = 0;
        
        // Анализ финансовых показателей
        if (isset($fns_data['revenue']) && $fns_data['revenue'] > 0) {
            $revenue = $fns_data['revenue'];
            if ($revenue > 100000000) { // Более 100 млн
                $score += 4;
            } elseif ($revenue > 10000000) { // Более 10 млн
                $score += 3;
            } elseif ($revenue > 1000000) { // Более 1 млн
                $score += 2;
            } else {
                $score += 1;
            }
        }
        
        // Анализ прибыльности
        if (isset($fns_data['profitability']) && $fns_data['profitability'] > 0) {
            $profitability = $fns_data['profitability'];
            if ($profitability > 20) {
                $score += 4;
            } elseif ($profitability > 10) {
                $score += 3;
            } elseif ($profitability > 5) {
                $score += 2;
            } else {
                $score += 1;
            }
        }
        
        // Анализ задолженности
        if (isset($fns_data['debt_ratio'])) {
            $debt_ratio = $fns_data['debt_ratio'];
            if ($debt_ratio < 10) {
                $score += 3;
            } elseif ($debt_ratio < 30) {
                $score += 2;
            } elseif ($debt_ratio < 50) {
                $score += 1;
            }
        }
        
        // Анализ риска банкротства
        if (isset($fns_data['bankruptcy_risk'])) {
            $bankruptcy_risk = $fns_data['bankruptcy_risk'];
            switch ($bankruptcy_risk) {
                case 'low':
                    $score += 4;
                    break;
                case 'medium':
                    $score += 2;
                    break;
                case 'high':
                    $score += 1;
                    break;
                case 'very_high':
                    $score += 0;
                    break;
            }
        }
        
        return min(15, $score);
    }
    
    private function get_fns_description($data) {
        $fns_data = $data['fns'] ?? null;
        if (!$fns_data) {
            return 'Данные ФНС недоступны';
        }
        
        $description = '';
        
        if (isset($fns_data['revenue'])) {
            $description .= "Выручка: " . number_format($fns_data['revenue'], 0, ',', ' ') . " руб. ";
        }
        
        if (isset($fns_data['profitability'])) {
            $description .= "Рентабельность: " . round($fns_data['profitability'], 2) . "%. ";
        }
        
        if (isset($fns_data['bankruptcy_risk'])) {
            $risk_levels = array(
                'low' => 'Низкий риск банкротства',
                'medium' => 'Средний риск банкротства',
                'high' => 'Высокий риск банкротства',
                'very_high' => 'Очень высокий риск банкротства'
            );
            $description .= $risk_levels[$fns_data['bankruptcy_risk']] ?? 'Риск не определен';
        }
        
        return trim($description);
    }
    
    private function calculate_rosstat_score($data) {
        $rosstat_data = $data['rosstat'] ?? null;
        if (!$rosstat_data) {
            return 5; // Базовый балл если нет данных
        }
        
        $score = 0;
        
        // Анализ региональных показателей
        if (isset($rosstat_data['region']['statistical_rating'])) {
            $region_rating = $rosstat_data['region']['statistical_rating'];
            $score += $region_rating * 3; // До 3 баллов за регион
        }
        
        // Анализ отраслевых показателей
        if (isset($rosstat_data['sector']['sector_rating'])) {
            $sector_rating = $rosstat_data['sector']['sector_rating'];
            $score += $sector_rating * 4; // До 4 баллов за отрасль
        }
        
        // Анализ размера предприятия
        if (isset($rosstat_data['enterprise_size']['size_category'])) {
            $size_category = $rosstat_data['enterprise_size']['size_category'];
            switch ($size_category) {
                case 'large':
                    $score += 2;
                    break;
                case 'medium':
                    $score += 2;
                    break;
                case 'small':
                    $score += 1;
                    break;
                case 'micro':
                    $score += 1;
                    break;
            }
        }
        
        // Анализ стабильности занятости
        if (isset($rosstat_data['employment']['employment_stability'])) {
            $stability = $rosstat_data['employment']['employment_stability'];
            $score += $stability * 1; // До 1 балла за стабильность
        }
        
        return min(10, $score);
    }
    
    private function get_rosstat_description($data) {
        $rosstat_data = $data['rosstat'] ?? null;
        if (!$rosstat_data) {
            return 'Данные Росстат недоступны';
        }
        
        $description = '';
        
        if (isset($rosstat_data['region']['region_name'])) {
            $description .= "Регион: " . $rosstat_data['region']['region_name'] . ". ";
        }
        
        if (isset($rosstat_data['sector']['sector_name'])) {
            $description .= "Отрасль: " . $rosstat_data['sector']['sector_name'] . ". ";
        }
        
        if (isset($rosstat_data['enterprise_size']['size_category'])) {
            $size_categories = array(
                'micro' => 'Микропредприятие',
                'small' => 'Малое предприятие',
                'medium' => 'Среднее предприятие',
                'large' => 'Крупное предприятие'
            );
            $description .= "Размер: " . ($size_categories[$rosstat_data['enterprise_size']['size_category']] ?? 'Неизвестно') . ". ";
        }
        
        if (isset($rosstat_data['employment']['employment_stability'])) {
            $stability = $rosstat_data['employment']['employment_stability'];
            if ($stability > 0.7) {
                $description .= "Стабильная занятость.";
            } elseif ($stability > 0.4) {
                $description .= "Средняя стабильность занятости.";
            } else {
                $description .= "Нестабильная занятость.";
            }
        }
        
        return trim($description);
    }
    
    private function calculate_efrsb_score($data) {
        $efrsb_data = $data['efrsb'] ?? null;
        if (!$efrsb_data) {
            return 10; // Базовый балл если нет данных
        }
        
        $score = 0;
        
        // Анализ статуса банкротства
        if (isset($efrsb_data['bankruptcy_status'])) {
            switch ($efrsb_data['bankruptcy_status']) {
                case 'no_bankruptcy':
                    $score += 20;
                    break;
                case 'observation':
                    $score += 5;
                    break;
                case 'financial_recovery':
                    $score += 10;
                    break;
                case 'external_management':
                    $score += 3;
                    break;
                case 'liquidation':
                    $score += 0;
                    break;
                default:
                    $score += 8;
            }
        }
        
        // Анализ риска банкротства
        if (isset($efrsb_data['bankruptcy_risk_level'])) {
            switch ($efrsb_data['bankruptcy_risk_level']) {
                case 'low':
                    $score += 5;
                    break;
                case 'medium':
                    $score += 3;
                    break;
                case 'high':
                    $score += 1;
                    break;
                case 'very_high':
                    $score += 0;
                    break;
            }
        }
        
        // Анализ количества дел о банкротстве
        if (isset($efrsb_data['bankruptcy_cases']) && is_array($efrsb_data['bankruptcy_cases'])) {
            $cases_count = count($efrsb_data['bankruptcy_cases']);
            if ($cases_count === 0) {
                $score += 3;
            } elseif ($cases_count === 1) {
                $score += 1;
            } else {
                $score += 0;
            }
        }
        
        return min(20, $score);
    }
    
    private function get_efrsb_description($data) {
        $efrsb_data = $data['efrsb'] ?? null;
        if (!$efrsb_data) {
            return 'Данные ЕФРСБ недоступны';
        }
        
        $description = '';
        
        if (isset($efrsb_data['bankruptcy_status'])) {
            $status_names = array(
                'no_bankruptcy' => 'Банкротство не обнаружено',
                'observation' => 'Процедура наблюдения',
                'financial_recovery' => 'Финансовое оздоровление',
                'external_management' => 'Внешнее управление',
                'liquidation' => 'Ликвидация'
            );
            $description .= "Статус: " . ($status_names[$efrsb_data['bankruptcy_status']] ?? 'Неизвестно') . ". ";
        }
        
        if (isset($efrsb_data['bankruptcy_risk_level'])) {
            $risk_levels = array(
                'low' => 'Низкий риск банкротства',
                'medium' => 'Средний риск банкротства',
                'high' => 'Высокий риск банкротства',
                'very_high' => 'Очень высокий риск банкротства'
            );
            $description .= $risk_levels[$efrsb_data['bankruptcy_risk_level']] ?? 'Риск не определен';
        }
        
        return trim($description);
    }
    
    private function calculate_rnp_score($data) {
        $rnp_data = $data['rnp'] ?? null;
        if (!$rnp_data) {
            return 8; // Базовый балл если нет данных
        }
        
        $score = 0;
        
        // Анализ статуса недобросовестного поставщика
        if (isset($rnp_data['is_dishonest_supplier'])) {
            if (!$rnp_data['is_dishonest_supplier']) {
                $score += 15; // Полный балл если не в реестре
            } else {
                $score += 0; // Нет баллов если в реестре
            }
        }
        
        // Анализ количества нарушений
        if (isset($rnp_data['violation_count'])) {
            $violation_count = $rnp_data['violation_count'];
            if ($violation_count === 0) {
                $score += 5;
            } elseif ($violation_count === 1) {
                $score += 2;
            } else {
                $score += 0;
            }
        }
        
        // Анализ репутационного воздействия
        if (isset($rnp_data['reputation_impact'])) {
            switch ($rnp_data['reputation_impact']) {
                case 'positive':
                    $score += 3;
                    break;
                case 'negative':
                    $score += 0;
                    break;
                case 'none':
                    $score += 2;
                    break;
            }
        }
        
        return min(15, $score);
    }
    
    private function get_rnp_description($data) {
        $rnp_data = $data['rnp'] ?? null;
        if (!$rnp_data) {
            return 'Данные РНП недоступны';
        }
        
        $description = '';
        
        if (isset($rnp_data['is_dishonest_supplier'])) {
            if ($rnp_data['is_dishonest_supplier']) {
                $description .= "В реестре недобросовестных поставщиков. ";
            } else {
                $description .= "Не в реестре недобросовестных поставщиков. ";
            }
        }
        
        if (isset($rnp_data['violation_count'])) {
            $violation_count = $rnp_data['violation_count'];
            if ($violation_count > 0) {
                $description .= "Нарушений: " . $violation_count . ". ";
            } else {
                $description .= "Нарушений не обнаружено. ";
            }
        }
        
        if (isset($rnp_data['reputation_impact'])) {
            switch ($rnp_data['reputation_impact']) {
                case 'positive':
                    $description .= "Положительная репутация.";
                    break;
                case 'negative':
                    $description .= "Отрицательная репутация.";
                    break;
                case 'none':
                    $description .= "Нейтральная репутация.";
                    break;
            }
        }
        
        return trim($description);
    }
    
    private function calculate_fssp_score($data) {
        $fssp_data = $data['fssp'] ?? null;
        if (!$fssp_data) {
            return 8; // Базовый балл если нет данных
        }
        
        $score = 0;
        
        // Анализ наличия исполнительных производств
        if (isset($fssp_data['has_enforcement_proceedings'])) {
            if (!$fssp_data['has_enforcement_proceedings']) {
                $score += 15; // Полный балл если нет производств
            } else {
                $score += 0; // Нет баллов если есть производства
            }
        }
        
        // Анализ финансового риска
        if (isset($fssp_data['financial_risk_level'])) {
            switch ($fssp_data['financial_risk_level']) {
                case 'low':
                    $score += 5;
                    break;
                case 'medium':
                    $score += 3;
                    break;
                case 'high':
                    $score += 1;
                    break;
                case 'very_high':
                    $score += 0;
                    break;
            }
        }
        
        // Анализ общей суммы задолженности
        if (isset($fssp_data['total_debt_amount'])) {
            $total_debt = $fssp_data['total_debt_amount'];
            if ($total_debt === 0) {
                $score += 3;
            } elseif ($total_debt < 100000) {
                $score += 2;
            } elseif ($total_debt < 500000) {
                $score += 1;
            } else {
                $score += 0;
            }
        }
        
        return min(15, $score);
    }
    
    private function get_fssp_description($data) {
        $fssp_data = $data['fssp'] ?? null;
        if (!$fssp_data) {
            return 'Данные ФССП недоступны';
        }
        
        $description = '';
        
        if (isset($fssp_data['has_enforcement_proceedings'])) {
            if ($fssp_data['has_enforcement_proceedings']) {
                $description .= "Есть исполнительные производства. ";
            } else {
                $description .= "Исполнительных производств нет. ";
            }
        }
        
        if (isset($fssp_data['total_debt_amount'])) {
            $total_debt = $fssp_data['total_debt_amount'];
            if ($total_debt > 0) {
                $description .= "Общая задолженность: " . number_format($total_debt, 0, ',', ' ') . " руб. ";
            } else {
                $description .= "Задолженностей нет. ";
            }
        }
        
        if (isset($fssp_data['financial_risk_level'])) {
            $risk_levels = array(
                'low' => 'Низкий финансовый риск',
                'medium' => 'Средний финансовый риск',
                'high' => 'Высокий финансовый риск',
                'very_high' => 'Очень высокий финансовый риск'
            );
            $description .= $risk_levels[$fssp_data['financial_risk_level']] ?? 'Риск не определен';
        }
        
        return trim($description);
    }
    
    private function get_advanced_analytics($company_data) {
        // Проверяем, включена ли расширенная аналитика
        if (!get_option('crc_advanced_analytics_enabled', 1)) {
            return null;
        }
        
        try {
            $analytics = new AdvancedAnalytics();
            $analysis = $analytics->comprehensive_analysis($company_data);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: Advanced analytics generated for company');
            }
            
            return $analysis;
        } catch (Exception $e) {
            error_log('Advanced analytics error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_fns_data($inn) {
        // Проверяем, включен ли анализ ФНС данных
        if (!get_option('crc_fns_enabled', 1)) {
            return null;
        }
        
        try {
            // Сначала пытаемся использовать улучшенную версию
            $fns_api = new FNSAPIImproved();
            $fns_info = $fns_api->get_financial_data($inn);
            
            // Если улучшенная версия не дала результатов, используем старую
            if (!$fns_info || is_wp_error($fns_info)) {
                $fns_api_old = new FNSAPI();
                $fns_info = $fns_api_old->get_financial_data($inn);
            }
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: FNS data retrieved for INN ' . $inn);
            }
            
            return $fns_info;
        } catch (Exception $e) {
            error_log('FNS data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_rosstat_data($inn) {
        // Проверяем, включен ли анализ Росстат данных
        if (!get_option('crc_rosstat_enabled', 1)) {
            return null;
        }
        
        try {
            $rosstat_api = new RosstatAPI();
            $rosstat_info = $rosstat_api->get_statistical_data($inn);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: Rosstat data retrieved for INN ' . $inn);
            }
            
            return $rosstat_info;
        } catch (Exception $e) {
            error_log('Rosstat data error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function ajax_clear_cache() {
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав доступа');
        }
        
        // Проверяем nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'crc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        global $crc_cache_manager;
        $result = $crc_cache_manager->clear_all();
        
        if ($result) {
            wp_send_json_success('Кэш успешно очищен');
        } else {
            wp_send_json_error('Ошибка при очистке кэша');
        }
    }
    
    public function ajax_cleanup_expired_cache() {
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав доступа');
        }
        
        // Проверяем nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'crc_admin_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        global $crc_cache_manager;
        $deleted_count = $crc_cache_manager->cleanup_expired();
        
        if ($deleted_count !== false) {
            wp_send_json_success($deleted_count);
        } else {
            wp_send_json_error('Ошибка при очистке истекших кэшей');
        }
    }
    
    private function get_efrsb_data($inn) {
        // Проверяем, включен ли анализ ЕФРСБ данных
        if (!get_option('crc_efrsb_enabled', 1)) {
            return null;
        }
        
        try {
            $efrsb_api = new EFRSBAPI();
            $efrsb_info = $efrsb_api->get_bankruptcy_data($inn);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: EFRSB data retrieved for INN ' . $inn);
            }
            
            return $efrsb_info;
        } catch (Exception $e) {
            error_log('EFRSB data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_rnp_data($inn) {
        // Проверяем, включен ли анализ РНП данных
        if (!get_option('crc_rnp_enabled', 1)) {
            return null;
        }
        
        try {
            $rnp_api = new RNPApi();
            $rnp_info = $rnp_api->get_dishonest_supplier_data($inn);
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: RNP data retrieved for INN ' . $inn);
            }
            
            return $rnp_info;
        } catch (Exception $e) {
            error_log('RNP data error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function get_fssp_data($inn) {
        // Проверяем, включен ли анализ ФССП данных
        if (!get_option('crc_fssp_enabled', 1)) {
            return null;
        }
        
        try {
            // Сначала пытаемся использовать улучшенную версию
            $fssp_api = new FSSPApiImproved();
            $fssp_info = $fssp_api->get_enforcement_data($inn);
            
            // Если улучшенная версия не дала результатов, используем старую
            if (!$fssp_info || is_wp_error($fssp_info)) {
                $fssp_api_old = new FSSPApi();
                $fssp_info = $fssp_api_old->get_enforcement_data($inn);
            }
            
            // Логируем в режиме отладки
            if (get_option('crc_debug_mode', 0)) {
                error_log('CRC Debug: FSSP data retrieved for INN ' . $inn);
            }
            
            return $fssp_info;
        } catch (Exception $e) {
            error_log('FSSP data error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function ajax_export_company() {
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав доступа');
        }
        
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'crc_nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        $inn = sanitize_text_field($_POST['inn']);
        $format = sanitize_text_field($_POST['format']);
        
        if (empty($inn) || empty($format)) {
            wp_send_json_error('Не указаны обязательные параметры');
        }
        
        // Получаем данные компании
        $company_data = $this->get_company_data($inn);
        if (!$company_data) {
            wp_send_json_error('Не удалось получить данные компании');
        }
        
        // Создаем экспорт
        $export = new DataExport();
        
        switch ($format) {
            case 'csv':
                $result = $export->export_company_csv($company_data);
                break;
            case 'excel':
                $result = $export->export_company_excel($company_data);
                break;
            case 'pdf':
                $result = $export->export_company_pdf($company_data);
                break;
            default:
                wp_send_json_error('Неподдерживаемый формат экспорта');
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    public function ajax_get_export_files() {
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав доступа');
        }
        
        $export = new DataExport();
        $files = $export->get_export_files();
        $stats = $export->get_export_stats();
        
        wp_send_json_success(array(
            'files' => $files,
            'stats' => $stats
        ));
    }
    
    public function ajax_delete_export_file() {
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав доступа');
        }
        
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'crc_nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        $filename = sanitize_text_field($_POST['filename']);
        
        if (empty($filename)) {
            wp_send_json_error('Не указано имя файла');
        }
        
        $export = new DataExport();
        $result = $export->delete_export_file($filename);
        
        if ($result) {
            wp_send_json_success('Файл удален');
        } else {
            wp_send_json_error('Не удалось удалить файл');
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Настройки рейтинга предприятий',
            'Рейтинг предприятий',
            'manage_options',
            'company-rating-checker',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('crc_settings', 'crc_dadata_token');
        register_setting('crc_settings', 'crc_dadata_secret');
        
        // Настройки для новых источников данных
        register_setting('crc_settings', 'crc_arbitration_enabled');
        register_setting('crc_settings', 'crc_zakupki_enabled');
        register_setting('crc_settings', 'crc_fns_enabled');
        register_setting('crc_settings', 'crc_fns_api_key');
        register_setting('crc_settings', 'crc_rosstat_enabled');
        register_setting('crc_settings', 'crc_advanced_analytics_enabled');
        register_setting('crc_settings', 'crc_efrsb_enabled');
        register_setting('crc_settings', 'crc_rnp_enabled');
        register_setting('crc_settings', 'crc_fssp_enabled');
        register_setting('crc_settings', 'crc_cache_duration');
        register_setting('crc_settings', 'crc_debug_mode');
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Настройки рейтинга предприятий</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('crc_settings');
                do_settings_sections('crc_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API ключ DaData</th>
                        <td>
                            <input type="text" 
                                   name="crc_dadata_token" 
                                   value="<?php echo esc_attr(get_option('crc_dadata_token')); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                Получите API ключ на <a href="https://dadata.ru/" target="_blank">dadata.ru</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Секретный ключ DaData</th>
                        <td>
                            <input type="text" 
                                   name="crc_dadata_secret" 
                                   value="<?php echo esc_attr(get_option('crc_dadata_secret')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <h2>Настройки дополнительных источников данных</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Арбитражные данные</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="crc_arbitration_enabled" 
                                       value="1" 
                                       <?php checked(get_option('crc_arbitration_enabled', 1), 1); ?> />
                                Включить анализ арбитражных рисков
                            </label>
                            <p class="description">
                                Анализ судебных рисков на основе статистических данных и структуры ИНН
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Государственные закупки</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="crc_zakupki_enabled" 
                                       value="1" 
                                       <?php checked(get_option('crc_zakupki_enabled', 1), 1); ?> />
                                Включить анализ репутации в госзакупках
                            </label>
                            <p class="description">
                                Оценка репутации поставщика в государственных закупках
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ФНС данные</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="crc_fns_enabled" 
                                       value="1" 
                                       <?php checked(get_option('crc_fns_enabled', 1), 1); ?> />
                                Включить анализ финансовых данных ФНС
                            </label>
                            <p class="description">
                                Получение финансовых данных, информации о банкротстве и налоговых задолженностях
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API ключ ФНС</th>
                        <td>
                            <input type="text" 
                                   name="crc_fns_api_key" 
                                   value="<?php echo esc_attr(get_option('crc_fns_api_key')); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                Получите API ключ на <a href="https://api-fns.ru/" target="_blank">api-fns.ru</a> (необязательно)
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Росстат данные</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="crc_rosstat_enabled" 
                                       value="1" 
                                       <?php checked(get_option('crc_rosstat_enabled', 1), 1); ?> />
                                Включить анализ статистических данных Росстат
                            </label>
                            <p class="description">
                                Получение региональной и отраслевой статистики, данных о занятости
                            </p>
                        </td>
                    </tr>
        <tr>
            <th scope="row">Расширенная аналитика</th>
            <td>
                <label>
                    <input type="checkbox"
                           name="crc_advanced_analytics_enabled"
                           value="1"
                           <?php checked(get_option('crc_advanced_analytics_enabled', 1), 1); ?> />
                    Включить расширенную аналитику и улучшенные алгоритмы
                </label>
                <p class="description">
                    Комплексный анализ финансового здоровья, операционной эффективности, рыночной позиции и рисков
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">ЕФРСБ данные</th>
            <td>
                <label>
                    <input type="checkbox"
                           name="crc_efrsb_enabled"
                           value="1"
                           <?php checked(get_option('crc_efrsb_enabled', 1), 1); ?> />
                    Включить анализ данных о банкротстве из ЕФРСБ
                </label>
                <p class="description">
                    Получение информации о процедурах банкротства и финансовых рисках
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">РНП данные</th>
            <td>
                <label>
                    <input type="checkbox"
                           name="crc_rnp_enabled"
                           value="1"
                           <?php checked(get_option('crc_rnp_enabled', 1), 1); ?> />
                    Включить анализ реестра недобросовестных поставщиков
                </label>
                <p class="description">
                    Проверка нарушений в государственных закупках и репутации поставщика
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">ФССП данные</th>
            <td>
                <label>
                    <input type="checkbox"
                           name="crc_fssp_enabled"
                           value="1"
                           <?php checked(get_option('crc_fssp_enabled', 1), 1); ?> />
                    Включить анализ исполнительных производств из ФССП
                </label>
                <p class="description">
                    Получение информации о задолженностях и исполнительных производствах
                </p>
            </td>
        </tr>
                    <tr>
                        <th scope="row">Время кэширования</th>
                        <td>
                            <select name="crc_cache_duration">
                                <option value="1" <?php selected(get_option('crc_cache_duration', 12), 1); ?>>1 час</option>
                                <option value="6" <?php selected(get_option('crc_cache_duration', 12), 6); ?>>6 часов</option>
                                <option value="12" <?php selected(get_option('crc_cache_duration', 12), 12); ?>>12 часов</option>
                                <option value="24" <?php selected(get_option('crc_cache_duration', 12), 24); ?>>24 часа</option>
                                <option value="168" <?php selected(get_option('crc_cache_duration', 12), 168); ?>>1 неделя</option>
                            </select>
                            <p class="description">
                                Время хранения результатов в кэше для повышения производительности
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Режим отладки</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="crc_debug_mode" 
                                       value="1" 
                                       <?php checked(get_option('crc_debug_mode', 0), 1); ?> />
                                Включить режим отладки
                            </label>
                            <p class="description">
                                Включает подробное логирование для диагностики проблем
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>Информация о подключенных источниках данных</h2>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3>✅ Активные источники данных:</h3>
                <ul>
                    <li><strong>DaData API</strong> - основная информация о компании</li>
                    <li><strong>ЕГРЮЛ (ФНС)</strong> - данные о руководителе и верификация</li>
                    <li><strong>Реестр МСП (ФНС)</strong> - категория предприятия</li>
                    <?php if (get_option('crc_arbitration_enabled', 1)): ?>
                    <li><strong>Арбитражные суды</strong> - анализ судебных рисков</li>
                    <?php endif; ?>
                    <?php if (get_option('crc_zakupki_enabled', 1)): ?>
                    <li><strong>Государственные закупки</strong> - репутация поставщика</li>
                    <?php endif; ?>
        <?php if (get_option('crc_fns_enabled', 1)): ?>
        <li><strong>ФНС данные</strong> - финансовые показатели и банкротство</li>
        <?php endif; ?>
        <?php if (get_option('crc_rosstat_enabled', 1)): ?>
        <li><strong>Росстат данные</strong> - региональная и отраслевая статистика</li>
        <?php endif; ?>
        <?php if (get_option('crc_efrsb_enabled', 1)): ?>
        <li><strong>ЕФРСБ данные</strong> - процедуры банкротства и финансовые риски</li>
        <?php endif; ?>
        <?php if (get_option('crc_rnp_enabled', 1)): ?>
        <li><strong>РНП данные</strong> - реестр недобросовестных поставщиков</li>
        <?php endif; ?>
        <?php if (get_option('crc_fssp_enabled', 1)): ?>
        <li><strong>ФССП данные</strong> - исполнительные производства и задолженности</li>
        <?php endif; ?>
                </ul>
                
                <h3>📊 Система рейтинга:</h3>
                <p><strong>Максимальный балл:</strong> 
                <?php 
                $max_score = 100;
                if (get_option('crc_arbitration_enabled', 1)) $max_score += 10;
                if (get_option('crc_zakupki_enabled', 1)) $max_score += 10;
        if (get_option('crc_fns_enabled', 1)) $max_score += 15;
        if (get_option('crc_rosstat_enabled', 1)) $max_score += 10;
        if (get_option('crc_efrsb_enabled', 1)) $max_score += 20;
        if (get_option('crc_rnp_enabled', 1)) $max_score += 15;
        if (get_option('crc_fssp_enabled', 1)) $max_score += 15;
                echo $max_score;
                ?>
                </p>
                <p><strong>Количество факторов:</strong> 
                <?php 
                $factors_count = 8;
                if (get_option('crc_arbitration_enabled', 1)) $factors_count++;
                if (get_option('crc_zakupki_enabled', 1)) $factors_count++;
        if (get_option('crc_fns_enabled', 1)) $factors_count++;
        if (get_option('crc_rosstat_enabled', 1)) $factors_count++;
        if (get_option('crc_efrsb_enabled', 1)) $factors_count++;
        if (get_option('crc_rnp_enabled', 1)) $factors_count++;
        if (get_option('crc_fssp_enabled', 1)) $factors_count++;
                echo $factors_count;
                ?>
                </p>
            </div>
            
            <h2>Управление кэшем</h2>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <?php
                global $crc_cache_manager;
                $cache_info = $crc_cache_manager->get_admin_info();
                ?>
                <h3>📊 Статистика кэша:</h3>
                <ul>
                    <li><strong>Активных кэшей:</strong> <?php echo $cache_info['stats']['active_count']; ?></li>
                    <li><strong>Истекших кэшей:</strong> <?php echo $cache_info['stats']['expired_count']; ?></li>
                    <li><strong>Размер кэша:</strong> <?php echo $cache_info['stats']['cache_size_mb']; ?> МБ</li>
                    <li><strong>Время кэширования:</strong> <?php echo $cache_info['settings']['cache_duration']; ?> часов</li>
                </ul>
                
                <h3>🔧 Управление:</h3>
                <p>
                    <button type="button" class="button" onclick="clearCache()">Очистить весь кэш</button>
                    <button type="button" class="button" onclick="cleanupExpired()">Очистить истекшие кэши</button>
                </p>
                
                <h3>💡 Рекомендации:</h3>
                <ul>
                    <?php foreach ($cache_info['recommendations'] as $recommendation): ?>
                        <li><?php echo esc_html($recommendation); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <h2>Экспорт данных</h2>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3>📤 Экспорт рейтинга компании:</h3>
                <p>
                    <label for="export_inn">ИНН компании:</label><br>
                    <input type="text" id="export_inn" name="export_inn" placeholder="Введите ИНН" style="width: 200px; margin-right: 10px;">
                    <select id="export_format" style="margin-right: 10px;">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel (XLSX)</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <button type="button" class="button button-primary" onclick="exportCompany()">Экспортировать</button>
                </p>
                
                <h3>📁 Файлы экспорта:</h3>
                <div id="export-files-list">
                    <p>Загрузка списка файлов...</p>
                </div>
                
                <h3>📊 Статистика экспорта:</h3>
                <div id="export-stats">
                    <p>Загрузка статистики...</p>
                </div>
            </div>
            
            <h2>Тестирование подключения</h2>
            <p>Введите ИНН для тестирования (например: 5260482041):</p>
            <input type="text" id="test-inn" class="regular-text" placeholder="ИНН" maxlength="12">
            <button type="button" class="button" onclick="testDaData()">Тестировать</button>
            <div id="test-result" style="margin-top: 15px;"></div>
            
            <script>
            function clearCache() {
                if (confirm('Вы уверены, что хотите очистить весь кэш?')) {
                    jQuery.post(ajaxurl, {
                        action: 'crc_clear_cache',
                        nonce: '<?php echo wp_create_nonce('crc_admin_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('Кэш успешно очищен!');
                            location.reload();
                        } else {
                            alert('Ошибка при очистке кэша: ' + response.data);
                        }
                    });
                }
            }
            
            function cleanupExpired() {
                jQuery.post(ajaxurl, {
                    action: 'crc_cleanup_expired_cache',
                    nonce: '<?php echo wp_create_nonce('crc_admin_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Истекшие кэши очищены! Удалено: ' + response.data + ' записей');
                        location.reload();
                    } else {
                        alert('Ошибка при очистке истекших кэшей: ' + response.data);
                    }
                });
            }
            
            function testDaData() {
                var inn = document.getElementById('test-inn').value;
                var result = document.getElementById('test-result');
                
                if (!inn) {
                    result.innerHTML = '<div class="error"><p>Введите ИНН</p></div>';
                    return;
                }
                
                result.innerHTML = '<p>Тестирование...</p>';
                
                jQuery.post(ajaxurl, {
                    action: 'crc_get_company_rating',
                    inn: inn,
                    nonce: '<?php echo wp_create_nonce('crc_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        result.innerHTML = '<div class="updated"><p><strong>Успешно!</strong> Компания: ' + 
                            (response.data.company.name.full_with_opf || response.data.company.name.full) + 
                            '<br>Рейтинг: ' + response.data.rating.total_score + '/100 (' + 
                            response.data.rating.rating.level + ')</p></div>';
                    } else {
                        result.innerHTML = '<div class="error"><p>Ошибка: ' + response.data + '</p></div>';
                    }
                });
            }
            
            // Функции для экспорта
            function exportCompany() {
                var inn = document.getElementById('export_inn').value;
                var format = document.getElementById('export_format').value;
                
                if (!inn) {
                    alert('Введите ИНН компании');
                    return;
                }
                
                if (!confirm('Экспортировать данные компании ' + inn + ' в формате ' + format.toUpperCase() + '?')) {
                    return;
                }
                
                jQuery.post(ajaxurl, {
                    action: 'crc_export_company',
                    inn: inn,
                    format: format,
                    nonce: '<?php echo wp_create_nonce('crc_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Файл успешно создан: ' + response.data.filename);
                        loadExportFiles();
                    } else {
                        alert('Ошибка экспорта: ' + response.data);
                    }
                });
            }
            
            function loadExportFiles() {
                jQuery.post(ajaxurl, {
                    action: 'crc_get_export_files',
                    nonce: '<?php echo wp_create_nonce('crc_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        var filesHtml = '';
                        if (response.data.files.length > 0) {
                            filesHtml = '<table class="widefat"><thead><tr><th>Файл</th><th>Размер</th><th>Создан</th><th>Действия</th></tr></thead><tbody>';
                            response.data.files.forEach(function(file) {
                                var size = (file.size / 1024).toFixed(1) + ' КБ';
                                var created = new Date(file.created * 1000).toLocaleString('ru-RU');
                                filesHtml += '<tr>';
                                filesHtml += '<td>' + file.filename + '</td>';
                                filesHtml += '<td>' + size + '</td>';
                                filesHtml += '<td>' + created + '</td>';
                                filesHtml += '<td><a href="' + file.download_url + '" class="button button-small">Скачать</a> ';
                                filesHtml += '<button class="button button-small" onclick="deleteExportFile(\'' + file.filename + '\')">Удалить</button></td>';
                                filesHtml += '</tr>';
                            });
                            filesHtml += '</tbody></table>';
                        } else {
                            filesHtml = '<p>Файлы экспорта не найдены</p>';
                        }
                        document.getElementById('export-files-list').innerHTML = filesHtml;
                        
                        // Обновляем статистику
                        var statsHtml = '<ul>';
                        statsHtml += '<li><strong>Всего файлов:</strong> ' + response.data.stats.total_files + '</li>';
                        statsHtml += '<li><strong>Общий размер:</strong> ' + response.data.stats.total_size_mb + ' МБ</li>';
                        if (Object.keys(response.data.stats.file_types).length > 0) {
                            statsHtml += '<li><strong>Типы файлов:</strong> ';
                            var types = [];
                            for (var type in response.data.stats.file_types) {
                                types.push(type.toUpperCase() + ': ' + response.data.stats.file_types[type]);
                            }
                            statsHtml += types.join(', ') + '</li>';
                        }
                        statsHtml += '</ul>';
                        document.getElementById('export-stats').innerHTML = statsHtml;
                    } else {
                        document.getElementById('export-files-list').innerHTML = '<p>Ошибка загрузки файлов: ' + response.data + '</p>';
                    }
                });
            }
            
            function deleteExportFile(filename) {
                if (!confirm('Удалить файл ' + filename + '?')) {
                    return;
                }
                
                jQuery.post(ajaxurl, {
                    action: 'crc_delete_export_file',
                    filename: filename,
                    nonce: '<?php echo wp_create_nonce('crc_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Файл удален');
                        loadExportFiles();
                    } else {
                        alert('Ошибка удаления: ' + response.data);
                    }
                });
            }
            
            // Загружаем файлы при загрузке страницы
            jQuery(document).ready(function() {
                loadExportFiles();
            });
            </script>
        </div>
        <?php
    }
}

new CompanyRatingChecker();

register_activation_hook(__FILE__, function() {
    add_option('crc_dadata_token', '');
    add_option('crc_dadata_secret', '');
    
    // Настройки для новых источников данных
    add_option('crc_arbitration_enabled', 1);
    add_option('crc_zakupki_enabled', 1);
        add_option('crc_fns_enabled', 1);
        add_option('crc_fns_api_key', '');
        add_option('crc_rosstat_enabled', 1);
        add_option('crc_advanced_analytics_enabled', 1);
        add_option('crc_efrsb_enabled', 1);
        add_option('crc_rnp_enabled', 1);
        add_option('crc_fssp_enabled', 1);
    add_option('crc_cache_duration', 12);
    add_option('crc_debug_mode', 0);
});

