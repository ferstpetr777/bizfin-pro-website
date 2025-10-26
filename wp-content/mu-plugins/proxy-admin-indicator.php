<?php
/*
Plugin Name: Proxy Admin Indicator
Description: Индикатор мониторинга прокси в верхней панели админки WordPress
Version: 1.0
Author: System Admin
*/

if (!defined('ABSPATH')) exit;

class Proxy_Admin_Indicator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_bar_menu', [$this, 'add_proxy_indicator'], 999);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_proxy_get_status', [$this, 'ajax_get_status']);
    }
    
    /**
     * Добавление индикатора в админ-бар
     */
    public function add_proxy_indicator($wp_admin_bar) {
        // Показываем только для администраторов
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $status = $this->get_proxy_status();
        $status_info = $this->get_status_info($status);
        
        $wp_admin_bar->add_node([
            'id' => 'proxy-monitor',
            'title' => $this->get_indicator_html($status, $status_info),
            'meta' => [
                'class' => 'proxy-monitor-indicator',
                'title' => $status_info['tooltip']
            ]
        ]);
        
        // Добавляем подменю с детальной информацией
        $wp_admin_bar->add_node([
            'id' => 'proxy-monitor-details',
            'parent' => 'proxy-monitor',
            'title' => 'Детали мониторинга',
            'href' => admin_url('admin.php?page=openai-api-manager')
        ]);
        
        $wp_admin_bar->add_node([
            'id' => 'proxy-monitor-refresh',
            'parent' => 'proxy-monitor',
            'title' => 'Обновить статус',
            'href' => '#',
            'meta' => [
                'onclick' => 'proxyIndicatorRefresh(); return false;'
            ]
        ]);
    }
    
    /**
     * Получение статуса прокси в реальном времени
     */
    private function get_proxy_status() {
        // Сначала проверяем последний статус из файла
        $upload_dir = wp_upload_dir();
        $stats_file = $upload_dir['basedir'] . '/proxy-monitor/stats.json';
        
        $last_status = 'unknown';
        $last_check_time = 0;
        
        if (file_exists($stats_file)) {
            $stats = json_decode(file_get_contents($stats_file), true);
            if (!empty($stats)) {
                $last_stat = end($stats);
                $last_status = $last_stat['overall_status'] ?? 'unknown';
                $last_check_time = $last_stat['unix_timestamp'] ?? 0;
            }
        }
        
        // Если последняя проверка была более 10 минут назад, делаем быструю проверку
        if ((time() - $last_check_time) > 600) {
            return $this->quick_proxy_check();
        }
        
        return $last_status;
    }
    
    /**
     * Быстрая проверка прокси
     */
    private function quick_proxy_check() {
        $start_time = microtime(true);
        
        // Быстрый тест подключения
        $response = wp_remote_get('https://api.openai.com/v1/models', [
            'proxy' => 'http://89.110.80.198:8889',
            'timeout' => 5,
            'headers' => [
                'Authorization' => 'Bearer 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'
            ]
        ]);
        
        $response_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            return 'critical';
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            if ($response_time < 3) {
                return 'healthy';
            } else {
                return 'degraded';
            }
        } else {
            return 'critical';
        }
    }
    
    /**
     * Получение информации о статусе
     */
    private function get_status_info($status) {
        $statuses = [
            'healthy' => [
                'label' => 'Прокси OK',
                'color' => '#46b450',
                'icon' => '✓',
                'tooltip' => 'Прокси работает стабильно'
            ],
            'degraded' => [
                'label' => 'Прокси ⚠',
                'color' => '#ffb900',
                'icon' => '⚠',
                'tooltip' => 'Прокси работает с перебоями'
            ],
            'critical' => [
                'label' => 'Прокси ✗',
                'color' => '#dc3232',
                'icon' => '✗',
                'tooltip' => 'Критические проблемы с прокси'
            ],
            'unknown' => [
                'label' => 'Прокси ?',
                'color' => '#666666',
                'icon' => '?',
                'tooltip' => 'Статус прокси неизвестен'
            ]
        ];
        
        return $statuses[$status] ?? $statuses['unknown'];
    }
    
    /**
     * Генерация HTML индикатора
     */
    private function get_indicator_html($status, $status_info) {
        return sprintf(
            '<span class="proxy-indicator" style="color: %s; font-weight: bold;">%s %s</span>',
            $status_info['color'],
            $status_info['icon'],
            $status_info['label']
        );
    }
    
    /**
     * Подключение скриптов и стилей
     */
    public function enqueue_scripts() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        wp_add_inline_style('admin-bar', $this->get_indicator_css());
        wp_add_inline_script('admin-bar', $this->get_indicator_js());
    }
    
    /**
     * CSS стили для индикатора
     */
    private function get_indicator_css() {
        return '
        .proxy-monitor-indicator {
            background: rgba(255, 255, 255, 0.1) !important;
            border-radius: 3px !important;
            padding: 2px 8px !important;
            margin: 0 5px !important;
            transition: all 0.3s ease !important;
        }
        
        .proxy-monitor-indicator:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        
        .proxy-indicator {
            font-size: 12px !important;
            line-height: 1 !important;
            text-shadow: none !important;
        }
        
        .proxy-indicator.healthy {
            animation: pulse-green 2s infinite;
        }
        
        .proxy-indicator.degraded {
            animation: pulse-yellow 1s infinite;
        }
        
        .proxy-indicator.critical {
            animation: pulse-red 0.5s infinite;
        }
        
        @keyframes pulse-green {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes pulse-yellow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes pulse-red {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        #wp-admin-bar-proxy-monitor .ab-item {
            display: flex !important;
            align-items: center !important;
        }
        
        #wp-admin-bar-proxy-monitor .ab-item:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }
        ';
    }
    
    /**
     * JavaScript для индикатора
     */
    private function get_indicator_js() {
        return '
        function proxyIndicatorRefresh() {
            var indicator = document.getElementById("wp-admin-bar-proxy-monitor");
            if (!indicator) return;
            
            // Показываем загрузку
            var title = indicator.querySelector(".ab-item");
            if (title) {
                title.innerHTML = "<span style=\"color: #666;\">⟳ Обновление...</span>";
            }
            
            // Запрашиваем новый статус
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "proxy_get_status",
                    nonce: "' . wp_create_nonce('proxy_status_nonce') . '"
                },
                success: function(response) {
                    if (response.success) {
                        var status = response.data.status;
                        var statusInfo = response.data.status_info;
                        
                        // Обновляем индикатор
                        if (title) {
                            var color = statusInfo.color;
                            var icon = statusInfo.icon;
                            var label = statusInfo.label;
                            
                            title.innerHTML = "<span class=\"proxy-indicator " + status + "\" style=\"color: " + color + "; font-weight: bold;\">" + icon + " " + label + "</span>";
                            title.title = statusInfo.tooltip;
                        }
                        
                        // Показываем уведомление
                        if (status === "critical") {
                            proxyShowNotification("Критические проблемы с прокси!", "error");
                        } else if (status === "degraded") {
                            proxyShowNotification("Прокси работает с перебоями", "warning");
                        }
                    }
                },
                error: function() {
                    if (title) {
                        title.innerHTML = "<span style=\"color: #dc3232;\">✗ Ошибка</span>";
                    }
                }
            });
        }
        
        function proxyShowNotification(message, type) {
            // Создаем уведомление
            var notification = document.createElement("div");
            notification.style.cssText = "position: fixed; top: 32px; right: 20px; background: " + (type === "error" ? "#dc3232" : "#ffb900") + "; color: white; padding: 10px 15px; border-radius: 4px; z-index: 999999; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);";
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Удаляем через 5 секунд
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
        
        // Автообновление каждые 30 секунд для реального времени
        setInterval(function() {
            proxyIndicatorRefresh();
        }, 30000);
        
        // Обновляем при загрузке страницы
        jQuery(document).ready(function() {
            setTimeout(proxyIndicatorRefresh, 1000);
        });
        
        // Обновляем при фокусе на окне (когда пользователь возвращается)
        jQuery(window).on("focus", function() {
            setTimeout(proxyIndicatorRefresh, 500);
        });
        ';
    }
    
    /**
     * AJAX получение статуса в реальном времени
     */
    public function ajax_get_status() {
        check_ajax_referer('proxy_status_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав');
        }
        
        // Всегда делаем реальную проверку при AJAX запросе
        $status = $this->real_time_proxy_check();
        $status_info = $this->get_status_info($status);
        
        // Получаем дополнительную информацию
        $upload_dir = wp_upload_dir();
        $stats_file = $upload_dir['basedir'] . '/proxy-monitor/stats.json';
        $alerts_file = $upload_dir['basedir'] . '/proxy-monitor/alerts.json';
        
        $additional_info = [
            'check_time' => current_time('mysql'),
            'response_time' => 0,
            'last_check' => '',
            'recent_alerts' => 0
        ];
        
        if (file_exists($stats_file)) {
            $stats = json_decode(file_get_contents($stats_file), true);
            if (!empty($stats)) {
                $last_stat = end($stats);
                $additional_info['last_check'] = $last_stat['timestamp'] ?? '';
                $additional_info['response_time'] = $last_stat['total_time'] ?? 0;
            }
        }
        
        if (file_exists($alerts_file)) {
            $alerts = json_decode(file_get_contents($alerts_file), true);
            $recent_alerts = array_filter($alerts, function($alert) {
                return strtotime($alert['timestamp']) > (time() - 3600); // Последний час
            });
            $additional_info['recent_alerts'] = count($recent_alerts);
        }
        
        wp_send_json_success([
            'status' => $status,
            'status_info' => $status_info,
            'additional_info' => $additional_info
        ]);
    }
    
    /**
     * Реальная проверка прокси в реальном времени
     */
    private function real_time_proxy_check() {
        $start_time = microtime(true);
        
        // Тест 1: Простое подключение
        $connection_test = $this->test_connection();
        
        // Тест 2: OpenAI API
        $api_test = $this->test_openai_api();
        
        $total_time = microtime(true) - $start_time;
        
        // Определяем общий статус
        $success_count = 0;
        $total_tests = 2;
        
        if ($connection_test['status'] === 'success') $success_count++;
        if ($api_test['status'] === 'success') $success_count++;
        
        $success_rate = $success_count / $total_tests;
        
        if ($success_rate >= 0.8) {
            $status = 'healthy';
        } elseif ($success_rate >= 0.5) {
            $status = 'degraded';
        } else {
            $status = 'critical';
        }
        
        // Логируем результат
        error_log("Proxy Indicator Real-time Check: $status (success rate: " . round($success_rate * 100) . "%, time: " . round($total_time, 2) . "s)");
        
        return $status;
    }
    
    /**
     * Тест подключения к прокси
     */
    private function test_connection() {
        $start_time = microtime(true);
        
        $response = wp_remote_get('https://api.openai.com/v1/models', [
            'proxy' => 'http://89.110.80.198:8889',
            'timeout' => 8,
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
            'proxy' => 'http://89.110.80.198:8889',
            'timeout' => 15,
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
}

// Инициализация
Proxy_Admin_Indicator::get_instance();
?>
