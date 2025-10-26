<?php
/**
 * Менеджер кэширования для Company Rating Checker
 */

class CRCCacheManager {
    
    private $cache_prefix = 'crc_';
    private $default_duration = 12; // часов
    
    /**
     * Получение данных из кэша
     */
    public function get($key) {
        $cache_key = $this->cache_prefix . md5($key);
        return get_transient($cache_key);
    }
    
    /**
     * Сохранение данных в кэш
     */
    public function set($key, $data, $duration = null) {
        if ($duration === null) {
            $duration = get_option('crc_cache_duration', $this->default_duration);
        }
        
        $cache_key = $this->cache_prefix . md5($key);
        $expiration = $duration * HOUR_IN_SECONDS;
        
        return set_transient($cache_key, $data, $expiration);
    }
    
    /**
     * Удаление данных из кэша
     */
    public function delete($key) {
        $cache_key = $this->cache_prefix . md5($key);
        return delete_transient($cache_key);
    }
    
    /**
     * Очистка всего кэша плагина
     */
    public function clear_all() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        $sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        );
        
        $result1 = $wpdb->query($sql);
        
        $sql = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . $pattern
        );
        
        $result2 = $wpdb->query($sql);
        
        return ($result1 !== false && $result2 !== false);
    }
    
    /**
     * Получение статистики кэша
     */
    public function get_stats() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        
        // Подсчет активных кэшей
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern
        );
        
        $active_count = $wpdb->get_var($sql);
        
        // Подсчет истекших кэшей
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value < %d",
            '_transient_timeout_' . $pattern,
            time()
        );
        
        $expired_count = $wpdb->get_var($sql);
        
        // Размер кэша
        $sql = $wpdb->prepare(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
             WHERE option_name LIKE %s",
            '_transient_' . $pattern
        );
        
        $cache_size = $wpdb->get_var($sql) ?: 0;
        
        return array(
            'active_count' => $active_count,
            'expired_count' => $expired_count,
            'cache_size_bytes' => $cache_size,
            'cache_size_mb' => round($cache_size / 1024 / 1024, 2)
        );
    }
    
    /**
     * Очистка истекших кэшей
     */
    public function cleanup_expired() {
        global $wpdb;
        
        $pattern = $this->cache_prefix . '%';
        
        // Удаляем истекшие кэши
        $sql = $wpdb->prepare(
            "DELETE t1, t2 FROM {$wpdb->options} t1
             LEFT JOIN {$wpdb->options} t2 ON t1.option_name = REPLACE(t2.option_name, '_transient_', '_transient_timeout_')
             WHERE t1.option_name LIKE %s
             AND t1.option_name LIKE '%_transient_timeout_%'
             AND t1.option_value < %d",
            $pattern,
            time()
        );
        
        return $wpdb->query($sql);
    }
    
    /**
     * Получение ключа кэша для компании
     */
    public function get_company_cache_key($inn, $include_sources = true) {
        $key = 'company_' . $inn;
        
        if ($include_sources) {
            $sources = array();
            if (get_option('crc_arbitration_enabled', 1)) {
                $sources[] = 'arbitration';
            }
            if (get_option('crc_zakupki_enabled', 1)) {
                $sources[] = 'zakupki';
            }
            $key .= '_' . implode('_', $sources);
        }
        
        return $key;
    }
    
    /**
     * Получение ключа кэша для арбитражных данных
     */
    public function get_arbitration_cache_key($inn) {
        return 'arbitration_' . $inn;
    }
    
    /**
     * Получение ключа кэша для данных о закупках
     */
    public function get_zakupki_cache_key($inn) {
        return 'zakupki_' . $inn;
    }
    
    /**
     * Проверка, нужно ли обновить кэш
     */
    public function should_refresh($key, $max_age_hours = null) {
        if ($max_age_hours === null) {
            $max_age_hours = get_option('crc_cache_duration', $this->default_duration);
        }
        
        $cache_key = $this->cache_prefix . md5($key);
        $timeout_key = '_transient_timeout_' . $cache_key;
        
        $expiration = get_option($timeout_key);
        if (!$expiration) {
            return true; // Кэш не существует
        }
        
        $age_hours = (time() - ($expiration - (get_option('crc_cache_duration', $this->default_duration) * HOUR_IN_SECONDS))) / 3600;
        
        return $age_hours >= $max_age_hours;
    }
    
    /**
     * Логирование операций кэширования
     */
    private function log($message) {
        if (get_option('crc_debug_mode', 0)) {
            error_log('CRC Cache: ' . $message);
        }
    }
    
    /**
     * Получение информации о кэше для админ-панели
     */
    public function get_admin_info() {
        $stats = $this->get_stats();
        
        return array(
            'stats' => $stats,
            'settings' => array(
                'cache_duration' => get_option('crc_cache_duration', $this->default_duration),
                'debug_mode' => get_option('crc_debug_mode', 0)
            ),
            'recommendations' => $this->get_recommendations($stats)
        );
    }
    
    /**
     * Получение рекомендаций по оптимизации
     */
    private function get_recommendations($stats) {
        $recommendations = array();
        
        if ($stats['expired_count'] > 10) {
            $recommendations[] = 'Рекомендуется очистить истекшие кэши для освобождения места';
        }
        
        if ($stats['cache_size_mb'] > 50) {
            $recommendations[] = 'Размер кэша превышает 50 МБ. Рассмотрите уменьшение времени кэширования';
        }
        
        if ($stats['active_count'] > 1000) {
            $recommendations[] = 'Большое количество активных кэшей. Проверьте настройки кэширования';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Кэш работает оптимально';
        }
        
        return $recommendations;
    }
}

// Глобальный экземпляр менеджера кэша
global $crc_cache_manager;
$crc_cache_manager = new CRCCacheManager();
?>
