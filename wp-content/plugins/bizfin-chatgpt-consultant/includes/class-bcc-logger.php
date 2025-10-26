<?php
/**
 * Класс для логирования
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Logger {
    
    private $log_file;
    private $max_log_size = 10485760; // 10MB
    private $max_log_files = 5;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/bizfin-chatgpt/logs/chatgpt-consultant.log';
        
        // Создаем директорию для логов если её нет
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    /**
     * Записать сообщение в лог
     */
    public function log($level, $message, $context = []) {
        if (!get_option('bcc_enable_logging', true)) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        // Записываем в файл
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Также записываем в WordPress debug.log если включен
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log("BCC [{$level}] {$message}{$context_str}");
        }
        
        // Ротируем лог если он стал слишком большим
        $this->rotate_log_if_needed();
    }
    
    /**
     * Логирование информации
     */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Логирование предупреждений
     */
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Логирование ошибок
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Логирование отладки
     */
    public function debug($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Ротация лог файла
     */
    private function rotate_log_if_needed() {
        if (!file_exists($this->log_file)) {
            return;
        }
        
        if (filesize($this->log_file) > $this->max_log_size) {
            $log_dir = dirname($this->log_file);
            $log_name = basename($this->log_file, '.log');
            
            // Переименовываем существующие файлы
            for ($i = $this->max_log_files - 1; $i > 0; $i--) {
                $old_file = "{$log_dir}/{$log_name}.{$i}.log";
                $new_file = "{$log_dir}/{$log_name}." . ($i + 1) . ".log";
                
                if (file_exists($old_file)) {
                    if ($i === $this->max_log_files - 1) {
                        unlink($old_file); // Удаляем самый старый
                    } else {
                        rename($old_file, $new_file);
                    }
                }
            }
            
            // Переименовываем текущий файл
            rename($this->log_file, "{$log_dir}/{$log_name}.1.log");
        }
    }
    
    /**
     * Получить содержимое лога
     */
    public function get_log_content($lines = 100) {
        if (!file_exists($this->log_file)) {
            return '';
        }
        
        $content = file_get_contents($this->log_file);
        $log_lines = explode(PHP_EOL, $content);
        
        if (count($log_lines) > $lines) {
            $log_lines = array_slice($log_lines, -$lines);
        }
        
        return implode(PHP_EOL, $log_lines);
    }
    
    /**
     * Очистить лог
     */
    public function clear_log() {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }
    
    /**
     * Получить размер лог файла
     */
    public function get_log_size() {
        if (!file_exists($this->log_file)) {
            return 0;
        }
        
        return filesize($this->log_file);
    }
    
    /**
     * Получить статистику логов
     */
    public function get_log_stats() {
        $stats = [
            'current_size' => $this->get_log_size(),
            'max_size' => $this->max_log_size,
            'max_files' => $this->max_log_files,
            'log_file' => $this->log_file,
            'exists' => file_exists($this->log_file),
        ];
        
        // Подсчитываем количество строк в логе
        if ($stats['exists']) {
            $content = file_get_contents($this->log_file);
            $stats['lines_count'] = substr_count($content, PHP_EOL);
        } else {
            $stats['lines_count'] = 0;
        }
        
        return $stats;
    }
}
