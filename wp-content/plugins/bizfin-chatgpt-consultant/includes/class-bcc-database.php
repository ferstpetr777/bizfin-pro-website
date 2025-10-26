<?php
/**
 * Класс для работы с базой данных
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Database {
    
    private $logger;
    private $table_prefix;
    
    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'bcc_';
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        $this->logger->info('BCC_Database initialized');
    }
    
    /**
     * Создание таблиц базы данных
     */
    public function create_tables() {
        global $wpdb;
        
        // Инициализируем логгер если он не инициализирован
        if (!$this->logger) {
            $this->logger = bizfin_chatgpt_consultant()->get_logger();
        }
        
        $this->logger->info('Creating database tables');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Таблица для хранения сессий чата
        $sessions_table = $this->table_prefix . 'sessions';
        $sessions_sql = "CREATE TABLE $sessions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_identifier varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_activity datetime DEFAULT CURRENT_TIMESTAMP,
            message_count int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            metadata longtext,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_identifier (user_identifier),
            KEY status (status),
            KEY last_activity (last_activity)
        ) $charset_collate;";
        
        // Таблица для хранения сообщений
        $messages_table = $this->table_prefix . 'messages';
        $messages_sql = "CREATE TABLE $messages_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            message_type enum('user','assistant','system') NOT NULL,
            content longtext NOT NULL,
            metadata longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            tokens_used int(11) DEFAULT 0,
            model_used varchar(100),
            processing_time decimal(10,3) DEFAULT 0,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY message_type (message_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Таблица для хранения загруженных файлов
        $files_table = $this->table_prefix . 'files';
        $files_sql = "CREATE TABLE $files_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            original_name varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_type varchar(100) NOT NULL,
            file_size bigint(20) NOT NULL,
            mime_type varchar(100) NOT NULL,
            processing_status varchar(20) DEFAULT 'pending',
            extracted_text longtext,
            vector_id varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            metadata longtext,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY file_type (file_type),
            KEY processing_status (processing_status),
            KEY vector_id (vector_id)
        ) $charset_collate;";
        
        // Таблица для настроек плагина
        $settings_table = $this->table_prefix . 'settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext NOT NULL,
            setting_type varchar(50) DEFAULT 'string',
            description text,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        // Таблица для статистики
        $stats_table = $this->table_prefix . 'statistics';
        $stats_sql = "CREATE TABLE $stats_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            metric_name varchar(100) NOT NULL,
            metric_value bigint(20) NOT NULL,
            metadata longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_metric (date, metric_name),
            KEY metric_name (metric_name),
            KEY date (date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sessions_sql);
        dbDelta($messages_sql);
        dbDelta($files_sql);
        dbDelta($settings_sql);
        dbDelta($stats_sql);
        
        $this->logger->info('Database tables created successfully');
    }
    
    /**
     * Сохранить сессию
     */
    public function save_session($session_id, $user_identifier, $metadata = []) {
        global $wpdb;
        
        $table = $this->table_prefix . 'sessions';
        
        $data = [
            'session_id' => $session_id,
            'user_identifier' => $user_identifier,
            'metadata' => json_encode($metadata),
            'last_activity' => current_time('mysql'),
        ];
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE session_id = %s",
            $session_id
        ));
        
        if ($existing) {
            $result = $wpdb->update($table, $data, ['session_id' => $session_id]);
        } else {
            $result = $wpdb->insert($table, $data);
        }
        
        if ($result === false) {
            $this->logger->error('Failed to save session', ['session_id' => $session_id, 'error' => $wpdb->last_error]);
            return false;
        }
        
        $this->logger->debug('Session saved', ['session_id' => $session_id]);
        return true;
    }
    
    /**
     * Получить сессию
     */
    public function get_session($session_id) {
        global $wpdb;
        
        $table = $this->table_prefix . 'sessions';
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s",
            $session_id
        ));
        
        if ($session && $session->metadata) {
            $session->metadata = json_decode($session->metadata, true);
        }
        
        return $session;
    }
    
    /**
     * Обновить активность сессии
     */
    public function update_session_activity($session_id) {
        global $wpdb;
        
        $table = $this->table_prefix . 'sessions';
        
        $result = $wpdb->update(
            $table,
            ['last_activity' => current_time('mysql')],
            ['session_id' => $session_id]
        );
        
        return $result !== false;
    }
    
    /**
     * Сохранить сообщение
     */
    public function save_message($session_id, $message_type, $content, $metadata = [], $tokens_used = 0, $model_used = '', $processing_time = 0) {
        global $wpdb;
        
        $table = $this->table_prefix . 'messages';
        
        $data = [
            'session_id' => $session_id,
            'message_type' => $message_type,
            'content' => $content,
            'metadata' => json_encode($metadata),
            'tokens_used' => $tokens_used,
            'model_used' => $model_used,
            'processing_time' => $processing_time,
        ];
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            $this->logger->error('Failed to save message', ['session_id' => $session_id, 'error' => $wpdb->last_error]);
            return false;
        }
        
        // Обновляем счетчик сообщений в сессии
        $this->increment_message_count($session_id);
        
        $this->logger->debug('Message saved', ['session_id' => $session_id, 'type' => $message_type]);
        return $wpdb->insert_id;
    }
    
    /**
     * Получить сообщения сессии
     */
    public function get_session_messages($session_id, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $table = $this->table_prefix . 'messages';
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s ORDER BY created_at ASC LIMIT %d OFFSET %d",
            $session_id, $limit, $offset
        ));
        
        foreach ($messages as $message) {
            if ($message->metadata) {
                $message->metadata = json_decode($message->metadata, true);
            }
        }
        
        return $messages;
    }
    
    /**
     * Увеличить счетчик сообщений
     */
    private function increment_message_count($session_id) {
        global $wpdb;
        
        $table = $this->table_prefix . 'sessions';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET message_count = message_count + 1 WHERE session_id = %s",
            $session_id
        ));
    }
    
    /**
     * Сохранить информацию о файле
     */
    public function save_file($session_id, $original_name, $file_path, $file_type, $file_size, $mime_type, $metadata = []) {
        global $wpdb;
        
        $table = $this->table_prefix . 'files';
        
        $data = [
            'session_id' => $session_id,
            'original_name' => $original_name,
            'file_path' => $file_path,
            'file_type' => $file_type,
            'file_size' => $file_size,
            'mime_type' => $mime_type,
            'metadata' => json_encode($metadata),
        ];
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            $this->logger->error('Failed to save file info', ['session_id' => $session_id, 'error' => $wpdb->last_error]);
            return false;
        }
        
        $this->logger->debug('File info saved', ['session_id' => $session_id, 'file' => $original_name]);
        return $wpdb->insert_id;
    }
    
    /**
     * Обновить статус обработки файла
     */
    public function update_file_processing_status($file_id, $status, $extracted_text = '', $vector_id = '') {
        global $wpdb;
        
        $table = $this->table_prefix . 'files';
        
        $data = ['processing_status' => $status];
        
        if ($extracted_text) {
            $data['extracted_text'] = $extracted_text;
        }
        
        if ($vector_id) {
            $data['vector_id'] = $vector_id;
        }
        
        $result = $wpdb->update($table, $data, ['id' => $file_id]);
        
        return $result !== false;
    }
    
    /**
     * Получить файлы сессии
     */
    public function get_session_files($session_id) {
        global $wpdb;
        
        $table = $this->table_prefix . 'files';
        
        $files = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s ORDER BY created_at DESC",
            $session_id
        ));
        
        foreach ($files as $file) {
            if ($file->metadata) {
                $file->metadata = json_decode($file->metadata, true);
            }
        }
        
        return $files;
    }
    
    /**
     * Сохранить настройку
     */
    public function save_setting($key, $value, $type = 'string', $description = '') {
        global $wpdb;
        
        $table = $this->table_prefix . 'settings';
        
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'description' => $description,
        ];
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE setting_key = %s",
            $key
        ));
        
        if ($existing) {
            $result = $wpdb->update($table, $data, ['setting_key' => $key]);
        } else {
            $result = $wpdb->insert($table, $data);
        }
        
        return $result !== false;
    }
    
    /**
     * Получить настройку
     */
    public function get_setting($key, $default = null) {
        global $wpdb;
        
        $table = $this->table_prefix . 'settings';
        
        $setting = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE setting_key = %s",
            $key
        ));
        
        if (!$setting) {
            return $default;
        }
        
        // Преобразуем значение в нужный тип
        switch ($setting->setting_type) {
            case 'boolean':
                return (bool) $setting->setting_value;
            case 'integer':
                return (int) $setting->setting_value;
            case 'float':
                return (float) $setting->setting_value;
            case 'json':
                return json_decode($setting->setting_value, true);
            default:
                return $setting->setting_value;
        }
    }
    
    /**
     * Сохранить статистику
     */
    public function save_statistic($date, $metric_name, $metric_value, $metadata = []) {
        global $wpdb;
        $table = $this->table_prefix . 'statistics';

        // Пытаемся выполнить атомарный UPSERT с инкрементом
        $sql = $wpdb->prepare(
            "INSERT INTO $table (date, metric_name, metric_value, metadata) VALUES (%s, %s, %d, %s)
             ON DUPLICATE KEY UPDATE metric_value = metric_value + VALUES(metric_value), metadata = VALUES(metadata)",
            $date,
            $metric_name,
            (int) $metric_value,
            json_encode($metadata)
        );

        $result = $wpdb->query($sql);
        if ($result === false) {
            // Fallback: ручной инкремент, если не поддерживается ON DUPLICATE KEY (на всякий случай)
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id, metric_value FROM $table WHERE date = %s AND metric_name = %s",
                $date,
                $metric_name
            ));
            if ($existing) {
                $result = $wpdb->update(
                    $table,
                    [
                        'metric_value' => (int) $existing->metric_value + (int) $metric_value,
                        'metadata' => json_encode($metadata),
                    ],
                    ['id' => $existing->id]
                );
            } else {
                $result = $wpdb->insert($table, [
                    'date' => $date,
                    'metric_name' => $metric_name,
                    'metric_value' => (int) $metric_value,
                    'metadata' => json_encode($metadata),
                ]);
            }
        }

        return $result !== false;
    }
    
    /**
     * Получить статистику
     */
    public function get_statistics($metric_name, $start_date = null, $end_date = null) {
        global $wpdb;
        
        $table = $this->table_prefix . 'statistics';
        
        $where = "metric_name = %s";
        $params = [$metric_name];
        
        if ($start_date) {
            $where .= " AND date >= %s";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where .= " AND date <= %s";
            $params[] = $end_date;
        }
        
        $sql = "SELECT * FROM $table WHERE $where ORDER BY date ASC";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));
        
        foreach ($results as $result) {
            if ($result->metadata) {
                $result->metadata = json_decode($result->metadata, true);
            }
        }
        
        return $results;
    }
    
    /**
     * Очистить старые данные
     */
    public function cleanup_old_data($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Очищаем старые сессии
        $sessions_table = $this->table_prefix . 'sessions';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $sessions_table WHERE last_activity < %s",
            $cutoff_date
        ));
        
        // Очищаем старые сообщения
        $messages_table = $this->table_prefix . 'messages';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $messages_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        $this->logger->info("Cleaned up data older than {$days} days");
    }
}
