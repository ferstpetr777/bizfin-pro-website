<?php
/*
Plugin Name: Proxy Monitor
Description: Система мониторинга стабильности голландского прокси
Version: 1.0
Author: System Admin
*/

if (!defined('ABSPATH')) exit;

class Proxy_Monitor {
    
    const PROXY_HOST = '89.110.80.198';
    const PROXY_PORT = '8889';
    const PROXY_URL = 'http://89.110.80.198:8889';
    const MONITOR_INTERVAL = 300; // 5 минут
    const ALERT_THRESHOLD = 3; // 3 ошибки подряд
    
    private $stats_file;
    private $alerts_file;
    
    public function __construct() {
        $this->stats_file = WP_CONTENT_DIR . '/uploads/proxy-stats.json';
        $this->alerts_file = WP_CONTENT_DIR . '/uploads/proxy-alerts.json';
        
        add_action('init', [$this, 'init']);
        add_action('wp_ajax_proxy_monitor_test', [$this, 'ajax_test_proxy']);
        add_action('wp_ajax_proxy_monitor_stats', [$this, 'ajax_get_stats']);
        add_action('wp_ajax_proxy_monitor_alerts', [$this, 'ajax_get_alerts']);
        
        // Планируем мониторинг
        if (!wp_next_scheduled('proxy_monitor_check')) {
            wp_schedule_event(time(), 'every_5_minutes', 'proxy_monitor_check');
        }
        add_action('proxy_monitor_check', [$this, 'perform_monitoring']);
        
        // Добавляем интервал для cron
        add_filter('cron_schedules', [$this, 'add_cron_interval']);
    }
    
    public function init() {
        // Создаем директории если нужно
        $upload_dir = wp_upload_dir();
        $monitor_dir = $upload_dir['basedir'] . '/proxy-monitor';
        if (!file_exists($monitor_dir)) {
            wp_mkdir_p($monitor_dir);
        }
        
        $this->stats_file = $monitor_dir . '/stats.json';
        $this->alerts_file = $monitor_dir . '/alerts.json';
    }
    
    public function add_cron_interval($schedules) {
        $schedules['every_5_minutes'] = [
            'interval' => 300,
            'display' => 'Every 5 Minutes'
        ];
        return $schedules;
    }
    
    /**
     * Основной метод мониторинга
     */
    public function perform_monitoring() {
        $start_time = microtime(true);
        
        // Тест 1: Простое подключение
        $connection_test = $this->test_connection();
        
        // Тест 2: OpenAI API через прокси
        $api_test = $this->test_openai_api();
        
        // Тест 3: DALL-E API через прокси
        $dalle_test = $this->test_dalle_api();
        
        $total_time = microtime(true) - $start_time;
        
        // Сохраняем результаты
        $result = [
            'timestamp' => current_time('mysql'),
            'unix_timestamp' => time(),
            'connection_test' => $connection_test,
            'api_test' => $api_test,
            'dalle_test' => $dalle_test,
            'total_time' => round($total_time, 2),
            'overall_status' => $this->calculate_overall_status($connection_test, $api_test, $dalle_test)
        ];
        
        $this->save_stats($result);
        $this->check_alerts($result);
        
        error_log("Proxy Monitor: Проверка завершена. Статус: " . $result['overall_status']);
    }
    
    /**
     * Тест подключения к прокси
     */
    private function test_connection() {
        $start_time = microtime(true);
        
        $response = wp_remote_get('https://api.openai.com/v1/models', [
            'proxy' => self::PROXY_URL,
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer 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'
            ]
        ]);
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        
        if (is_wp_error($response)) {
            return [
                'status' => 'error',
                'error' => $response->get_error_message(),
                'response_time' => $response_time
            ];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        return [
            'status' => $code === 200 ? 'success' : 'error',
            'http_code' => $code,
            'response_time' => $response_time
        ];
    }
    
    /**
     * Тест OpenAI API
     */
    private function test_openai_api() {
        $start_time = microtime(true);
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'proxy' => self::PROXY_URL,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer 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',
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'gpt-4o',
                'messages' => [['role' => 'user', 'content' => 'Test']],
                'max_tokens' => 5
            ])
        ]);
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        
        if (is_wp_error($response)) {
            return [
                'status' => 'error',
                'error' => $response->get_error_message(),
                'response_time' => $response_time
            ];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return [
            'status' => $code === 200 ? 'success' : 'error',
            'http_code' => $code,
            'response_time' => $response_time,
            'tokens_used' => $data['usage']['total_tokens'] ?? 0
        ];
    }
    
    /**
     * Тест DALL-E API
     */
    private function test_dalle_api() {
        $start_time = microtime(true);
        
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'proxy' => self::PROXY_URL,
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer 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',
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'dall-e-2',
                'prompt' => 'test',
                'size' => '256x256',
                'n' => 1
            ])
        ]);
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        
        if (is_wp_error($response)) {
            return [
                'status' => 'error',
                'error' => $response->get_error_message(),
                'response_time' => $response_time
            ];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return [
            'status' => $code === 200 ? 'success' : 'error',
            'http_code' => $code,
            'response_time' => $response_time,
            'image_generated' => isset($data['data'][0]['url'])
        ];
    }
    
    /**
     * Расчет общего статуса
     */
    private function calculate_overall_status($connection, $api, $dalle) {
        $success_count = 0;
        $total_tests = 3;
        
        if ($connection['status'] === 'success') $success_count++;
        if ($api['status'] === 'success') $success_count++;
        if ($dalle['status'] === 'success') $success_count++;
        
        $success_rate = $success_count / $total_tests;
        
        if ($success_rate >= 0.8) return 'healthy';
        if ($success_rate >= 0.5) return 'degraded';
        return 'critical';
    }
    
    /**
     * Сохранение статистики
     */
    private function save_stats($result) {
        $stats = $this->load_stats();
        $stats[] = $result;
        
        // Оставляем только последние 288 записей (24 часа при проверке каждые 5 минут)
        if (count($stats) > 288) {
            $stats = array_slice($stats, -288);
        }
        
        file_put_contents($this->stats_file, json_encode($stats, JSON_PRETTY_PRINT));
    }
    
    /**
     * Загрузка статистики
     */
    private function load_stats() {
        if (!file_exists($this->stats_file)) {
            return [];
        }
        
        $content = file_get_contents($this->stats_file);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Проверка алертов
     */
    private function check_alerts($result) {
        $stats = $this->load_stats();
        $recent_stats = array_slice($stats, -self::ALERT_THRESHOLD);
        
        $error_count = 0;
        foreach ($recent_stats as $stat) {
            if ($stat['overall_status'] !== 'healthy') {
                $error_count++;
            }
        }
        
        if ($error_count >= self::ALERT_THRESHOLD) {
            $this->create_alert($result, $error_count);
        }
    }
    
    /**
     * Создание алерта
     */
    private function create_alert($result, $error_count) {
        $alerts = $this->load_alerts();
        
        $alert = [
            'timestamp' => current_time('mysql'),
            'unix_timestamp' => time(),
            'type' => 'proxy_degradation',
            'message' => "Прокси показывает нестабильность: $error_count ошибок подряд",
            'status' => $result['overall_status'],
            'details' => $result
        ];
        
        $alerts[] = $alert;
        
        // Оставляем только последние 100 алертов
        if (count($alerts) > 100) {
            $alerts = array_slice($alerts, -100);
        }
        
        file_put_contents($this->alerts_file, json_encode($alerts, JSON_PRETTY_PRINT));
        
        // Логируем алерт
        error_log("Proxy Monitor ALERT: " . $alert['message']);
    }
    
    /**
     * Загрузка алертов
     */
    private function load_alerts() {
        if (!file_exists($this->alerts_file)) {
            return [];
        }
        
        $content = file_get_contents($this->alerts_file);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * AJAX тест прокси
     */
    public function ajax_test_proxy() {
        check_ajax_referer('proxy_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $this->perform_monitoring();
        
        wp_send_json_success(['message' => 'Тест прокси выполнен']);
    }
    
    /**
     * AJAX получение статистики
     */
    public function ajax_get_stats() {
        check_ajax_referer('proxy_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $stats = $this->load_stats();
        $alerts = $this->load_alerts();
        
        // Расчет метрик
        $total_checks = count($stats);
        $healthy_checks = count(array_filter($stats, function($s) { return $s['overall_status'] === 'healthy'; }));
        $degraded_checks = count(array_filter($stats, function($s) { return $s['overall_status'] === 'degraded'; }));
        $critical_checks = count(array_filter($stats, function($s) { return $s['overall_status'] === 'critical'; }));
        
        $uptime = $total_checks > 0 ? round(($healthy_checks / $total_checks) * 100, 2) : 0;
        
        wp_send_json_success([
            'stats' => $stats,
            'alerts' => $alerts,
            'metrics' => [
                'total_checks' => $total_checks,
                'healthy_checks' => $healthy_checks,
                'degraded_checks' => $degraded_checks,
                'critical_checks' => $critical_checks,
                'uptime_percentage' => $uptime
            ]
        ]);
    }
    
    /**
     * AJAX получение алертов
     */
    public function ajax_get_alerts() {
        check_ajax_referer('proxy_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        $alerts = $this->load_alerts();
        wp_send_json_success($alerts);
    }
}

// Инициализация
new Proxy_Monitor();
?>
