<?php
/**
 * Реальная интеграция с госзакупками
 * Company Rating Checker - Zakupki API Real
 */

if (!defined('ABSPATH')) { exit; }

class ZakupkiApiReal {
    
    private $base_urls = array(
        'zakupki_gov' => 'https://zakupki.gov.ru/',
        'clearspending' => 'https://clearspending.ru/',
        'goszakupki' => 'https://goszakupki.ru/'
    );
    
    private $api_key = '';
    private $timeout = 30;
    private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    
    public function __construct() {
        $this->api_key = get_option('crc_zakupki_api_key', '');
    }
    
    /**
     * Получение данных о госзакупках по ИНН
     */
    public function get_zakupki_info($inn) {
        $inn = preg_replace('/[^0-9]/', '', $inn);
        if (empty($inn)) {
            return new WP_Error('invalid_inn', 'ИНН не может быть пустым.');
        }
        
        // 1. Пытаемся получить данные через официальные API
        $api_data = $this->get_official_zakupki_data($inn);
        if ($api_data && !is_wp_error($api_data)) {
            return $api_data;
        }
        
        // 2. Пытаемся получить данные через публичные источники
        $public_data = $this->get_public_zakupki_data($inn);
        if ($public_data && !is_wp_error($public_data)) {
            return $public_data;
        }
        
        // 3. Если реальные данные не получены, возвращаем null вместо фиктивных
        error_log("ZakupkiApiReal: Не удалось получить реальные данные госзакупок для ИНН {$inn}");
        return null;
    }
    
    /**
     * Получение данных через официальные API
     */
    private function get_official_zakupki_data($inn) {
        try {
            // Пытаемся получить данные с ClearSpending
            $clearspending_data = $this->get_clearspending_data($inn);
            if ($clearspending_data) {
                return $clearspending_data;
            }
            
            // Пытаемся получить данные с zakupki.gov.ru
            $zakupki_data = $this->get_zakupki_gov_data($inn);
            if ($zakupki_data) {
                return $zakupki_data;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('ZakupkiApiReal: Exception in official API: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с ClearSpending
     */
    private function get_clearspending_data($inn) {
        try {
            $url = 'https://clearspending.ru/api/contractor/' . $inn;
            $headers = array(
                'User-Agent' => $this->user_agent,
                'Accept' => 'application/json'
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
            $data = json_decode($body, true);
            
            if (isset($data['contracts']) && !empty($data['contracts'])) {
                return $this->parse_clearspending_response($data, $inn);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('ZakupkiApiReal: Error getting ClearSpending data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с zakupki.gov.ru
     */
    private function get_zakupki_gov_data($inn) {
        try {
            $url = 'https://zakupki.gov.ru/epz/contract/search/results.html';
            $headers = array(
                'User-Agent' => $this->user_agent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Content-Type' => 'application/x-www-form-urlencoded'
            );
            
            $body = http_build_query(array(
                'searchString' => $inn,
                'morphology' => 'on',
                'search-filter' => 'Дате+размещения',
                'pageNumber' => '1',
                'sortDirection' => 'false',
                'recordsPerPage' => '_10',
                'showLotsInfoHidden' => 'false'
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
            $contracts = $this->parse_zakupki_gov_html($body, $inn);
            
            if (!empty($contracts)) {
                return $this->format_zakupki_response($contracts, $inn);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('ZakupkiApiReal: Error getting zakupki.gov.ru data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных через публичные источники
     */
    private function get_public_zakupki_data($inn) {
        try {
            // Пытаемся получить данные с goszakupki.ru
            $goszakupki_data = $this->get_goszakupki_data($inn);
            if ($goszakupki_data) {
                return $goszakupki_data;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('ZakupkiApiReal: Exception in public data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение данных с goszakupki.ru
     */
    private function get_goszakupki_data($inn) {
        try {
            $url = 'https://goszakupki.ru/search';
            $headers = array(
                'User-Agent' => $this->user_agent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            );
            
            $body = http_build_query(array(
                'query' => $inn,
                'type' => 'contractor'
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
            $contracts = $this->parse_goszakupki_html($body, $inn);
            
            if (!empty($contracts)) {
                return $this->format_zakupki_response($contracts, $inn);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('ZakupkiApiReal: Error getting goszakupki.ru data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Парсинг ответа ClearSpending
     */
    private function parse_clearspending_response($data, $inn) {
        $contracts = array();
        $total_amount = 0;
        $active_count = 0;
        $completed_count = 0;
        
        foreach ($data['contracts'] as $contract) {
            $contracts[] = array(
                'contract_id' => $contract['id'] ?? '',
                'contract_number' => $contract['number'] ?? '',
                'amount' => floatval($contract['amount'] ?? 0),
                'status' => $contract['status'] ?? 'unknown',
                'start_date' => $contract['start_date'] ?? '',
                'end_date' => $contract['end_date'] ?? '',
                'customer' => $contract['customer'] ?? '',
                'supplier' => $contract['supplier'] ?? ''
            );
            
            $total_amount += floatval($contract['amount'] ?? 0);
            
            if (($contract['status'] ?? '') === 'active') {
                $active_count++;
            } elseif (($contract['status'] ?? '') === 'completed') {
                $completed_count++;
            }
        }
        
        return array(
            'total_contracts' => count($contracts),
            'total_amount' => $total_amount,
            'active_contracts' => $active_count,
            'completed_contracts' => $completed_count,
            'avg_contract_amount' => count($contracts) > 0 ? $total_amount / count($contracts) : 0,
            'reputation_score' => $this->calculate_reputation_score($contracts),
            'contracts' => $contracts,
            'summary' => array(
                'reputation_level' => $this->get_reputation_level($contracts),
                'recommendation' => $this->get_recommendation($contracts)
            ),
            'sources_checked' => array(
                'clearspending' => array(
                    'url' => 'https://clearspending.ru/',
                    'name' => 'ClearSpending - Аналитика госзакупок',
                    'available' => true
                )
            ),
            'source' => 'clearspending_api',
            'last_updated' => current_time('mysql'),
            'api_used' => true,
            'heuristic_analysis' => false
        );
    }
    
    /**
     * Парсинг HTML ответа zakupki.gov.ru
     */
    private function parse_zakupki_gov_html($html, $inn) {
        $contracts = array();
        
        // Простой парсинг HTML (в реальной реализации нужен более сложный парсер)
        if (strpos($html, 'По Вашему запросу ничего не найдено') !== false) {
            return array(); // Нет контрактов
        }
        
        // Здесь должна быть логика парсинга HTML таблицы с контрактами
        // Пока возвращаем пустой массив
        return array();
    }
    
    /**
     * Парсинг HTML ответа goszakupki.ru
     */
    private function parse_goszakupki_html($html, $inn) {
        $contracts = array();
        
        // Простой парсинг HTML (в реальной реализации нужен более сложный парсер)
        if (strpos($html, 'Результаты поиска не найдены') !== false) {
            return array(); // Нет контрактов
        }
        
        // Здесь должна быть логика парсинга HTML таблицы с контрактами
        // Пока возвращаем пустой массив
        return array();
    }
    
    /**
     * Форматирование ответа госзакупок
     */
    private function format_zakupki_response($contracts, $inn) {
        $total_amount = 0;
        $active_count = 0;
        $completed_count = 0;
        
        foreach ($contracts as $contract) {
            $total_amount += $contract['amount'] ?? 0;
            
            if (($contract['status'] ?? '') === 'active') {
                $active_count++;
            } elseif (($contract['status'] ?? '') === 'completed') {
                $completed_count++;
            }
        }
        
        return array(
            'total_contracts' => count($contracts),
            'total_amount' => $total_amount,
            'active_contracts' => $active_count,
            'completed_contracts' => $completed_count,
            'avg_contract_amount' => count($contracts) > 0 ? $total_amount / count($contracts) : 0,
            'reputation_score' => $this->calculate_reputation_score($contracts),
            'contracts' => $contracts,
            'summary' => array(
                'reputation_level' => $this->get_reputation_level($contracts),
                'recommendation' => $this->get_recommendation($contracts)
            ),
            'sources_checked' => array(
                'zakupki_gov' => array(
                    'url' => 'https://zakupki.gov.ru/',
                    'name' => 'Единая информационная система в сфере закупок',
                    'available' => true
                )
            ),
            'source' => 'zakupki_website',
            'last_updated' => current_time('mysql'),
            'api_used' => false,
            'heuristic_analysis' => false
        );
    }
    
    /**
     * Расчет репутационного скора
     */
    private function calculate_reputation_score($contracts) {
        if (empty($contracts)) {
            return 0;
        }
        
        $score = 50; // Базовый балл
        
        // Бонус за количество контрактов
        $count = count($contracts);
        if ($count >= 10) {
            $score += 30;
        } elseif ($count >= 5) {
            $score += 20;
        } elseif ($count >= 1) {
            $score += 10;
        }
        
        // Бонус за выполнение контрактов
        $completed_ratio = 0;
        foreach ($contracts as $contract) {
            if (($contract['status'] ?? '') === 'completed') {
                $completed_ratio += 1;
            }
        }
        $completed_ratio = $completed_ratio / $count;
        
        if ($completed_ratio >= 0.9) {
            $score += 20;
        } elseif ($completed_ratio >= 0.7) {
            $score += 10;
        }
        
        return min(100, $score);
    }
    
    /**
     * Определение уровня репутации
     */
    private function get_reputation_level($contracts) {
        if (empty($contracts)) {
            return 'unknown';
        }
        
        $score = $this->calculate_reputation_score($contracts);
        
        if ($score >= 80) {
            return 'excellent';
        } elseif ($score >= 60) {
            return 'good';
        } elseif ($score >= 40) {
            return 'average';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Получение рекомендации
     */
    private function get_recommendation($contracts) {
        if (empty($contracts)) {
            return 'Участие в госзакупках не обнаружено';
        }
        
        $level = $this->get_reputation_level($contracts);
        
        switch ($level) {
            case 'excellent':
                return 'Отличная репутация в сфере госзакупок';
            case 'good':
                return 'Хорошая репутация в сфере госзакупок';
            case 'average':
                return 'Средняя репутация в сфере госзакупок';
            case 'poor':
                return 'Низкая репутация в сфере госзакупок';
            default:
                return 'Репутация не определена';
        }
    }
}
?>
