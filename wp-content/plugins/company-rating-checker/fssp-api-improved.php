<?php
/**
 * Улучшенная интеграция с ФССП для получения реальных данных
 * Company Rating Checker - FSSP API Improved
 */

if (!defined('ABSPATH')) { exit; }

class FSSPApiImproved {
    
    private $base_urls = array(
        'fssp_main' => 'https://fssp.gov.ru/',
        'fssp_database' => 'https://fssp.gov.ru/iss/ip/',
        'fssp_search' => 'https://fssp.gov.ru/iss/ip_search/'
    );
    
    private $api_key = '';
    private $timeout = 30;
    private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    
    public function __construct() {
        $this->api_key = get_option('crc_fssp_api_key', '');
    }
    
    /**
     * Получение данных об исполнительных производствах по ИНН
     */
    public function get_enforcement_data($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        // 1. Пытаемся получить данные через официальный API ФССП
        if (!empty($this->api_key)) {
            $api_data = $this->get_fssp_api_data($inn);
            if ($api_data && !is_wp_error($api_data)) {
                return $api_data;
            }
        }
        
        // 2. Пытаемся получить данные через публичные источники
        $public_data = $this->get_public_fssp_data($inn);
        if ($public_data && !is_wp_error($public_data)) {
            return $public_data;
        }
        
        // 3. Если реальные данные не получены, возвращаем null вместо фиктивных
        error_log("FSSPApiImproved: Не удалось получить реальные данные ФССП для ИНН {$inn}");
        return null;
    }
    
    /**
     * Получение данных через официальный API ФССП
     */
    private function get_fssp_api_data($inn) {
        try {
            $url = $this->base_urls['fssp_database'] . 'api/search';
            $headers = array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            );
            
            $body = json_encode(array(
                'inn' => $inn,
                'type' => 'legal'
            ));
            
            $response = wp_remote_post($url, array(
                'headers' => $headers,
                'body' => $body,
                'timeout' => $this->timeout
            ));
            
            if (is_wp_error($response)) {
                error_log('FSSPApiImproved: API error: ' . $response->get_error_message());
                return null;
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log('FSSPApiImproved: Non-200 response: ' . $response_code);
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['data']) && !empty($data['data'])) {
                return $this->parse_fssp_api_response($data['data']);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FSSPApiImproved: Exception in API call: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных через публичные источники
     */
    private function get_public_fssp_data($inn) {
        try {
            // Пытаемся получить данные с сайта ФССП
            $fssp_data = $this->get_fssp_website_data($inn);
            if ($fssp_data) {
                return $fssp_data;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FSSPApiImproved: Exception in public data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с сайта ФССП
     */
    private function get_fssp_website_data($inn) {
        try {
            $url = $this->base_urls['fssp_search'];
            $headers = array(
                'User-Agent' => $this->user_agent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8'
            );
            
            $body = http_build_query(array(
                'inn' => $inn,
                'type' => 'legal'
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
            
            // Парсим HTML ответ
            $proceedings = $this->parse_fssp_html_response($body, $inn);
            
            if (!empty($proceedings)) {
                return $this->format_fssp_response($proceedings, $inn);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('FSSPApiImproved: Error getting FSSP website data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Парсинг HTML ответа ФССП
     */
    private function parse_fssp_html_response($html, $inn) {
        $proceedings = array();
        
        // Простой парсинг HTML (в реальной реализации нужен более сложный парсер)
        if (strpos($html, 'Исполнительных производств не найдено') !== false) {
            return array(); // Нет производств
        }
        
        // Здесь должна быть логика парсинга HTML таблицы с производствами
        // Пока возвращаем пустой массив
        return array();
    }
    
    /**
     * Форматирование ответа ФССП
     */
    private function format_fssp_response($proceedings, $inn) {
        $total_debt = 0;
        $active_count = 0;
        
        foreach ($proceedings as $proceeding) {
            $total_debt += $proceeding['debt_amount'] ?? 0;
            if (($proceeding['status'] ?? '') === 'active') {
                $active_count++;
            }
        }
        
        return array(
            'inn' => $inn,
            'has_enforcement_proceedings' => !empty($proceedings),
            'proceedings' => $proceedings,
            'proceedings_count' => count($proceedings),
            'total_debt_amount' => $total_debt,
            'active_proceedings_count' => $active_count,
            'financial_risk_level' => $this->calculate_risk_level($proceedings, $total_debt),
            'proceeding_probability' => $this->calculate_probability($proceedings),
            'last_updated' => current_time('mysql'),
            'source' => 'fssp_website',
            'api_used' => false,
            'heuristic_analysis' => false
        );
    }
    
    /**
     * Парсинг ответа API ФССП
     */
    private function parse_fssp_api_response($data) {
        $proceedings = array();
        
        foreach ($data as $item) {
            $proceedings[] = array(
                'proceeding_id' => $item['id'] ?? '',
                'type' => $item['type'] ?? '',
                'description' => $item['description'] ?? '',
                'debt_amount' => intval($item['debt_amount'] ?? 0),
                'priority' => $item['priority'] ?? 'medium',
                'initiation_date' => $item['initiation_date'] ?? '',
                'bailiff' => $item['bailiff'] ?? '',
                'creditor' => $item['creditor'] ?? '',
                'status' => $item['status'] ?? 'unknown',
                'execution_percentage' => intval($item['execution_percentage'] ?? 0)
            );
        }
        
        return $this->format_fssp_response($proceedings, $data[0]['inn'] ?? '');
    }
    
    /**
     * Расчет уровня риска
     */
    private function calculate_risk_level($proceedings, $total_debt) {
        if (empty($proceedings)) {
            return 'low';
        }
        
        if ($total_debt > 1000000) { // Более 1 млн руб.
            return 'high';
        } elseif ($total_debt > 100000) { // Более 100 тыс. руб.
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Расчет вероятности производств
     */
    private function calculate_probability($proceedings) {
        if (empty($proceedings)) {
            return 0.0;
        }
        
        // Простая логика: чем больше производств, тем выше вероятность
        $count = count($proceedings);
        if ($count >= 5) {
            return 0.8;
        } elseif ($count >= 3) {
            return 0.6;
        } elseif ($count >= 1) {
            return 0.3;
        }
        
        return 0.0;
    }
}
?>
