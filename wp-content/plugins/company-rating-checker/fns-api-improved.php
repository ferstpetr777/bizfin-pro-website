<?php
/**
 * Улучшенная интеграция с ФНС для получения реальных данных
 * Company Rating Checker - FNS API Improved
 */

if (!defined('ABSPATH')) { exit; }

class FNSAPIImproved {
    
    private $base_urls = array(
        'api_fns' => 'https://api-fns.ru/api/',
        'nalog_ru' => 'https://egrul.nalog.ru/',
        'service_nalog' => 'https://service.nalog.ru/'
    );
    
    private $api_key = '';
    private $timeout = 30;
    private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    
    public function __construct() {
        $this->api_key = get_option('crc_fns_api_key', '');
    }
    
    /**
     * Получение финансовых данных компании по ИНН
     */
    public function get_financial_data($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        // 1. Пытаемся получить данные через API ФНС (если есть ключ)
        if (!empty($this->api_key)) {
            $api_data = $this->get_fns_api_data($inn);
            if ($api_data && !is_wp_error($api_data)) {
                return $api_data;
            }
        }
        
        // 2. Пытаемся получить данные через публичные источники
        $public_data = $this->get_public_fns_data($inn);
        if ($public_data && !is_wp_error($public_data)) {
            return $public_data;
        }
        
        // 3. Если реальные данные не получены, возвращаем null вместо фиктивных
        error_log("FNSAPIImproved: Не удалось получить реальные данные ФНС для ИНН {$inn}");
        return null;
    }
    
    /**
     * Получение данных через официальный API ФНС
     */
    private function get_fns_api_data($inn) {
        try {
            $url = $this->base_urls['api_fns'] . 'egr';
            $headers = array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            );
            
            $body = json_encode(array(
                'req' => $inn,
                'key' => $this->api_key
            ));
            
            $response = wp_remote_post($url, array(
                'headers' => $headers,
                'body' => $body,
                'timeout' => $this->timeout
            ));
            
            if (is_wp_error($response)) {
                error_log('FNSAPIImproved: API error: ' . $response->get_error_message());
                return null;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log('FNSAPIImproved: Non-200 response: ' . $response_code);
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['items']) && !empty($data['items'])) {
                return $this->parse_fns_api_response($data['items'][0]);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FNSAPIImproved: Exception in API call: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных через публичные источники
     */
    private function get_public_fns_data($inn) {
        try {
            // Пытаемся получить данные с сайта nalog.ru
            $nalog_data = $this->get_nalog_ru_data($inn);
            if ($nalog_data) {
                return $nalog_data;
            }
            
            // Пытаемся получить данные с service.nalog.ru
            $service_data = $this->get_service_nalog_data($inn);
            if ($service_data) {
                return $service_data;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FNSAPIImproved: Exception in public data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с nalog.ru
     */
    private function get_nalog_ru_data($inn) {
        try {
            $url = 'https://egrul.nalog.ru/';
            $headers = array(
                'User-Agent' => $this->user_agent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8'
            );
            
            $response = wp_remote_get($url, array(
                'headers' => $headers,
                'timeout' => $this->timeout
            ));
            
            if (is_wp_error($response)) {
                return null;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            
            // Здесь должна быть логика парсинга HTML страницы
            // Пока возвращаем null, так как это сложная задача
            return null;
            
        } catch (Exception $e) {
            error_log('FNSAPIImproved: Error getting nalog.ru data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с service.nalog.ru
     */
    private function get_service_nalog_data($inn) {
        try {
            $url = 'https://service.nalog.ru/inn.do';
            $headers = array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => $this->user_agent
            );
            
            $body = http_build_query(array(
                'inn' => $inn,
                'captcha' => '',
                'captchaToken' => ''
            ));
            
            $response = wp_remote_post($url, array(
                'headers' => $headers,
                'body' => $body,
                'timeout' => $this->timeout
            ));
            
            if (is_wp_error($response)) {
                return null;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['t']) && !empty($data['t'])) {
                return $this->parse_service_nalog_response($data);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FNSAPIImproved: Error getting service.nalog.ru data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Парсинг ответа API ФНС
     */
    private function parse_fns_api_response($data) {
        return array(
            'revenue' => isset($data['revenue']) ? intval($data['revenue']) : 0,
            'profit' => isset($data['profit']) ? intval($data['profit']) : 0,
            'debt' => isset($data['debt']) ? intval($data['debt']) : 0,
            'profitability' => isset($data['profitability']) ? floatval($data['profitability']) : 0,
            'debt_ratio' => isset($data['debt_ratio']) ? floatval($data['debt_ratio']) : 0,
            'bankruptcy_risk' => isset($data['bankruptcy_risk']) ? $data['bankruptcy_risk'] : 'unknown',
            'tax_debt' => isset($data['tax_debt']) ? intval($data['tax_debt']) : 0,
            'risk_score' => isset($data['risk_score']) ? intval($data['risk_score']) : 0,
            'last_updated' => current_time('mysql'),
            'source' => 'fns_api',
            'api_used' => true,
            'heuristic_analysis' => false
        );
    }
    
    /**
     * Парсинг ответа service.nalog.ru
     */
    private function parse_service_nalog_response($data) {
        // Здесь должна быть логика парсинга ответа service.nalog.ru
        // Пока возвращаем базовую структуру
        return array(
            'revenue' => 0,
            'profit' => 0,
            'debt' => 0,
            'profitability' => 0,
            'debt_ratio' => 0,
            'bankruptcy_risk' => 'unknown',
            'tax_debt' => 0,
            'risk_score' => 0,
            'last_updated' => current_time('mysql'),
            'source' => 'service_nalog',
            'api_used' => false,
            'heuristic_analysis' => false
        );
    }
}
?>
