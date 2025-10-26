<?php
/**
 * Улучшенная версия API для определения статуса МСП
 * Company Rating Checker - Improved MSP API
 */

if (!defined('ABSPATH')) { exit; }

class MSPApiImproved {
    
    private $base_url = 'https://rmsp.nalog.ru/';
    
    public function __construct() {
        // МСП API не требует ключа
    }
    
    /**
     * Получение статуса МСП с учетом финансовых данных
     */
    public function get_msp_data($inn, $fns_data = null) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        try {
            // Метод 1: Попытка получить данные через официальный API
            $official_data = $this->get_official_msp_data($inn);
            
            if ($official_data && !is_wp_error($official_data)) {
                // Если есть данные ФНС, корректируем статус МСП
                if ($fns_data && isset($fns_data['revenue'])) {
                    $corrected_data = $this->correct_msp_status_with_fns($official_data, $fns_data);
                    return $corrected_data;
                }
                return $official_data;
            }
            
            // Метод 2: Если официальный API не работает, используем эвристический анализ
            $heuristic_data = $this->get_heuristic_msp_data($inn, $fns_data);
            
            return $heuristic_data;
            
        } catch (Exception $e) {
            error_log('MSP API error: ' . $e->getMessage());
            return $this->get_heuristic_msp_data($inn, $fns_data);
        }
    }
    
    /**
     * Получение данных через официальный API МСП
     */
    private function get_official_msp_data($inn) {
        $url = $this->base_url . 'search.html';
        
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        );
        
        $body = http_build_query(array('query' => $inn));
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('msp_request_error', 'Ошибка запроса к МСП API: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('msp_response_error', 'Некорректный код ответа: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Парсим ответ для определения статуса МСП
        $msp_data = $this->parse_msp_response($body);
        
        return $msp_data;
    }
    
    /**
     * Парсинг ответа МСП
     */
    private function parse_msp_response($html) {
        $msp_data = array(
            'status' => 'unknown',
            'category' => 'Не определен',
            'source' => 'official_msp',
            'last_updated' => current_time('mysql')
        );
        
        // Проверяем различные статусы в HTML
        if (strpos($html, 'Не является субъектом МСП') !== false) {
            $msp_data['status'] = 'not_msp';
            $msp_data['category'] = 'Не является субъектом МСП';
        } elseif (strpos($html, 'Микропредприятие') !== false) {
            $msp_data['status'] = 'micro';
            $msp_data['category'] = 'Микропредприятие';
        } elseif (strpos($html, 'Малое предприятие') !== false) {
            $msp_data['status'] = 'small';
            $msp_data['category'] = 'Малое предприятие';
        } elseif (strpos($html, 'Среднее предприятие') !== false) {
            $msp_data['status'] = 'medium';
            $msp_data['category'] = 'Среднее предприятие';
        } else {
            $msp_data['status'] = 'unknown';
            $msp_data['category'] = 'Статус не определен';
        }
        
        return $msp_data;
    }
    
    /**
     * Коррекция статуса МСП с учетом данных ФНС
     */
    private function correct_msp_status_with_fns($msp_data, $fns_data) {
        $revenue = $fns_data['revenue'] ?? 0;
        
        // Критерии МСП по выручке (на 2024 год)
        $msp_limits = array(
            'micro' => 120000000,    // до 120 млн руб.
            'small' => 800000000,    // до 800 млн руб.
            'medium' => 2000000000   // до 2 млрд руб.
        );
        
        // Определяем правильный статус МСП на основе выручки
        if ($revenue <= $msp_limits['micro']) {
            $corrected_status = 'micro';
            $corrected_category = 'Микропредприятие';
        } elseif ($revenue <= $msp_limits['small']) {
            $corrected_status = 'small';
            $corrected_category = 'Малое предприятие';
        } elseif ($revenue <= $msp_limits['medium']) {
            $corrected_status = 'medium';
            $corrected_category = 'Среднее предприятие';
        } else {
            $corrected_status = 'not_msp';
            $corrected_category = 'Не является субъектом МСП';
        }
        
        // Если статус изменился, обновляем данные
        if ($msp_data['status'] !== $corrected_status) {
            $msp_data['status'] = $corrected_status;
            $msp_data['category'] = $corrected_category;
            $msp_data['source'] = 'corrected_with_fns';
            $msp_data['correction_applied'] = true;
            $msp_data['original_status'] = $msp_data['status'];
            $msp_data['revenue_used'] = $revenue;
        }
        
        return $msp_data;
    }
    
    /**
     * Эвристический анализ статуса МСП
     */
    private function get_heuristic_msp_data($inn, $fns_data = null) {
        $msp_factors = array();
        
        // Если есть данные ФНС, используем их для определения статуса
        if ($fns_data && isset($fns_data['revenue'])) {
            $revenue = $fns_data['revenue'];
            $msp_factors[] = "Анализ на основе выручки ФНС: " . number_format($revenue, 0, ',', ' ') . " руб.";
            
            // Определяем статус на основе выручки
            if ($revenue <= 120000000) {
                $status = 'micro';
                $category = 'Микропредприятие';
                $msp_factors[] = "Выручка до 120 млн руб. = микропредприятие";
            } elseif ($revenue <= 800000000) {
                $status = 'small';
                $category = 'Малое предприятие';
                $msp_factors[] = "Выручка до 800 млн руб. = малое предприятие";
            } elseif ($revenue <= 2000000000) {
                $status = 'medium';
                $category = 'Среднее предприятие';
                $msp_factors[] = "Выручка до 2 млрд руб. = среднее предприятие";
            } else {
                $status = 'not_msp';
                $category = 'Не является субъектом МСП';
                $msp_factors[] = "Выручка свыше 2 млрд руб. = не МСП";
            }
        } else {
            // Эвристический анализ на основе ИНН
            $msp_factors[] = "Эвристический анализ на основе ИНН";
            
            // Анализ структуры ИНН
            $inn_length = strlen($inn);
            if ($inn_length === 10) { // Юридическое лицо
                $msp_factors[] = "Юридическое лицо";
                
                // Анализ региона (первые две цифры)
                $region_code = substr($inn, 0, 2);
                $msp_factors[] = "Регион: {$region_code}";
                
                // Анализ возраста компании (эвристический)
                $first_digits = intval(substr($inn, 0, 4));
                $estimated_age_group = $this->estimate_company_age($first_digits);
                $msp_factors[] = "Оценочный возраст: {$estimated_age_group}";
                
                // По умолчанию считаем малым предприятием
                $status = 'small';
                $category = 'Малое предприятие (эвристическая оценка)';
                $msp_factors[] = "Эвристическая оценка: малое предприятие";
                
            } elseif ($inn_length === 12) { // ИП
                $status = 'micro';
                $category = 'Индивидуальный предприниматель';
                $msp_factors[] = "Индивидуальный предприниматель";
            } else {
                $status = 'unknown';
                $category = 'Статус не определен';
                $msp_factors[] = "Некорректный ИНН";
            }
        }
        
        $result = array(
            'inn' => $inn,
            'status' => $status,
            'category' => $category,
            'last_updated' => current_time('mysql'),
            'source' => 'heuristic_analysis',
            'heuristic_analysis' => true,
            'msp_factors' => $msp_factors
        );
        
        // Добавляем информацию о коррекции, если была применена
        if ($fns_data && isset($fns_data['revenue'])) {
            $result['correction_applied'] = true;
            $result['revenue_used'] = $fns_data['revenue'];
        }
        
        return $result;
    }
    
    /**
     * Оценка возраста компании
     */
    private function estimate_company_age($first_digits) {
        if ($first_digits < 5000) return '1990-2000';
        if ($first_digits < 6000) return '2000-2010';
        if ($first_digits < 7000) return '2010-2015';
        if ($first_digits < 8000) return '2015-2020';
        return '2020-2025';
    }
    
    /**
     * Проверка доступности источников
     */
    public function check_sources() {
        $results = array();
        
        // Проверка основного сайта МСП
        $response = wp_remote_get($this->base_url, array('timeout' => 5, 'sslverify' => false));
        $results['msp'] = array(
            'url' => $this->base_url,
            'name' => 'Реестр МСП ФНС России',
            'available' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        );
        
        return $results;
    }
}
?>
