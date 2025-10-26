<?php
/**
 * Класс для работы с векторной базой данных
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Vector_DB {
    
    private $logger;
    private $database;
    private $openai_client;
    private $vector_table;
    
    public function __construct() {
        global $wpdb;
        $this->vector_table = $wpdb->prefix . 'bcc_vectors';
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        $this->database = bizfin_chatgpt_consultant()->get_database();
        $this->openai_client = bizfin_chatgpt_consultant()->get_openai_client();
        
        $this->create_vector_table();
        $this->logger->info('BCC_Vector_DB initialized');
    }
    
    /**
     * Создание таблицы для векторов
     */
    private function create_vector_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $this->vector_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vector_id varchar(255) NOT NULL,
            session_id varchar(255) NOT NULL,
            content_type enum('message','file','context') NOT NULL,
            content longtext NOT NULL,
            embedding longtext NOT NULL,
            metadata longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY vector_id (vector_id),
            KEY session_id (session_id),
            KEY content_type (content_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Создать векторное представление текста
     */
    public function create_embedding($text, $session_id, $content_type = 'message', $metadata = []) {
        try {
            $this->logger->debug('Creating embedding for text', ['session_id' => $session_id, 'content_type' => $content_type]);
            
            // Получаем embedding от OpenAI
            $embedding = $this->openai_client->create_embedding($text);
            
            if (!$embedding) {
                $this->logger->error('Failed to create embedding');
                return false;
            }
            
            // Генерируем уникальный ID для вектора
            $vector_id = $this->generate_vector_id();
            
            // Сохраняем в базу данных
            $result = $this->save_vector($vector_id, $session_id, $content_type, $text, $embedding, $metadata);
            
            if ($result) {
                $this->logger->debug('Vector saved successfully', ['vector_id' => $vector_id]);
                return $vector_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->error('Error creating embedding: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Сохранить вектор в базу данных
     */
    private function save_vector($vector_id, $session_id, $content_type, $content, $embedding, $metadata = []) {
        global $wpdb;
        
        $data = [
            'vector_id' => $vector_id,
            'session_id' => $session_id,
            'content_type' => $content_type,
            'content' => $content,
            'embedding' => json_encode($embedding),
            'metadata' => json_encode($metadata),
        ];
        
        $result = $wpdb->insert($this->vector_table, $data);
        
        if ($result === false) {
            $this->logger->error('Failed to save vector', ['vector_id' => $vector_id, 'error' => $wpdb->last_error]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Поиск похожих векторов
     */
    public function search_similar($query_text, $session_id = null, $content_type = null, $limit = 10) {
        try {
            $this->logger->debug('Searching similar vectors', ['session_id' => $session_id, 'content_type' => $content_type]);
            
            // Создаем embedding для запроса
            $query_embedding = $this->openai_client->create_embedding($query_text);
            
            if (!$query_embedding) {
                $this->logger->error('Failed to create query embedding');
                return [];
            }
            
            // Получаем все векторы для поиска
            $vectors = $this->get_vectors_for_search($session_id, $content_type);
            
            if (empty($vectors)) {
                return [];
            }
            
            // Вычисляем косинусное сходство
            $similarities = [];
            foreach ($vectors as $vector) {
                $embedding = json_decode($vector->embedding, true);
                $similarity = $this->cosine_similarity($query_embedding, $embedding);
                
                $similarities[] = [
                    'vector_id' => $vector->vector_id,
                    'session_id' => $vector->session_id,
                    'content_type' => $vector->content_type,
                    'content' => $vector->content,
                    'metadata' => json_decode($vector->metadata, true),
                    'similarity' => $similarity,
                    'created_at' => $vector->created_at,
                ];
            }
            
            // Сортируем по сходству
            usort($similarities, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            // Возвращаем топ результатов
            $results = array_slice($similarities, 0, $limit);
            
            $this->logger->debug('Found similar vectors', ['count' => count($results)]);
            
            return $results;
            
        } catch (Exception $e) {
            $this->logger->error('Error searching similar vectors: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить векторы для поиска
     */
    private function get_vectors_for_search($session_id = null, $content_type = null) {
        global $wpdb;
        
        $where_conditions = [];
        $params = [];
        
        if ($session_id) {
            $where_conditions[] = "session_id = %s";
            $params[] = $session_id;
        }
        
        if ($content_type) {
            $where_conditions[] = "content_type = %s";
            $params[] = $content_type;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT * FROM $this->vector_table $where_clause ORDER BY created_at DESC";
        
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Вычисление косинусного сходства
     */
    private function cosine_similarity($vector_a, $vector_b) {
        if (count($vector_a) !== count($vector_b)) {
            return 0;
        }
        
        $dot_product = 0;
        $norm_a = 0;
        $norm_b = 0;
        
        for ($i = 0; $i < count($vector_a); $i++) {
            $dot_product += $vector_a[$i] * $vector_b[$i];
            $norm_a += $vector_a[$i] * $vector_a[$i];
            $norm_b += $vector_b[$i] * $vector_b[$i];
        }
        
        $norm_a = sqrt($norm_a);
        $norm_b = sqrt($norm_b);
        
        if ($norm_a == 0 || $norm_b == 0) {
            return 0;
        }
        
        return $dot_product / ($norm_a * $norm_b);
    }
    
    /**
     * Получить контекст для сессии
     */
    public function get_session_context($session_id, $query_text = '', $limit = 5) {
        try {
            $this->logger->debug('Getting session context', ['session_id' => $session_id]);
            
            // Если есть запрос, ищем похожие сообщения
            if ($query_text) {
                $similar_vectors = $this->search_similar($query_text, $session_id, 'message', $limit);
            } else {
                // Иначе получаем последние сообщения
                $similar_vectors = $this->get_recent_vectors($session_id, 'message', $limit);
            }
            
            // Формируем контекст
            $context = [];
            foreach ($similar_vectors as $vector) {
                $context[] = [
                    'content' => $vector['content'],
                    'similarity' => $vector['similarity'] ?? 1.0,
                    'created_at' => $vector['created_at'],
                ];
            }
            
            $this->logger->debug('Session context retrieved', ['context_count' => count($context)]);
            
            return $context;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting session context: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить последние векторы
     */
    private function get_recent_vectors($session_id, $content_type, $limit) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $this->vector_table WHERE session_id = %s AND content_type = %s ORDER BY created_at DESC LIMIT %d",
            $session_id, $content_type, $limit
        );
        
        $vectors = $wpdb->get_results($sql);
        
        $results = [];
        foreach ($vectors as $vector) {
            $results[] = [
                'vector_id' => $vector->vector_id,
                'session_id' => $vector->session_id,
                'content_type' => $vector->content_type,
                'content' => $vector->content,
                'metadata' => json_decode($vector->metadata, true),
                'created_at' => $vector->created_at,
            ];
        }
        
        return $results;
    }
    
    /**
     * Сохранить сообщение в векторную БД
     */
    public function save_message_vector($session_id, $message, $message_type = 'message', $metadata = []) {
        try {
            $this->logger->debug('Saving message vector', ['session_id' => $session_id, 'type' => $message_type]);
            
            $vector_id = $this->create_embedding($message, $session_id, $message_type, $metadata);
            
            if ($vector_id) {
                $this->logger->debug('Message vector saved', ['vector_id' => $vector_id]);
                return $vector_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->error('Error saving message vector: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Сохранить файл в векторную БД
     */
    public function save_file_vector($session_id, $file_content, $file_metadata = []) {
        try {
            $this->logger->debug('Saving file vector', ['session_id' => $session_id]);
            
            $vector_id = $this->create_embedding($file_content, $session_id, 'file', $file_metadata);
            
            if ($vector_id) {
                $this->logger->debug('File vector saved', ['vector_id' => $vector_id]);
                return $vector_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->error('Error saving file vector: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удалить векторы сессии
     */
    public function delete_session_vectors($session_id) {
        global $wpdb;
        
        $result = $wpdb->delete($this->vector_table, ['session_id' => $session_id]);
        
        if ($result !== false) {
            $this->logger->info('Session vectors deleted', ['session_id' => $session_id, 'count' => $result]);
            return true;
        }
        
        $this->logger->error('Failed to delete session vectors', ['session_id' => $session_id]);
        return false;
    }
    
    /**
     * Очистить старые векторы
     */
    public function cleanup_old_vectors($days = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $this->vector_table WHERE created_at < %s",
            $cutoff_date
        ));
        
        if ($result !== false) {
            $this->logger->info("Cleaned up vectors older than {$days} days", ['deleted_count' => $result]);
            return $result;
        }
        
        return false;
    }
    
    /**
     * Получить статистику векторов
     */
    public function get_vector_stats() {
        global $wpdb;
        
        $stats = [];
        
        // Общее количество векторов
        $stats['total_vectors'] = $wpdb->get_var("SELECT COUNT(*) FROM $this->vector_table");
        
        // Количество по типам
        $type_stats = $wpdb->get_results(
            "SELECT content_type, COUNT(*) as count FROM $this->vector_table GROUP BY content_type"
        );
        
        $stats['by_type'] = [];
        foreach ($type_stats as $stat) {
            $stats['by_type'][$stat->content_type] = $stat->count;
        }
        
        // Количество уникальных сессий
        $stats['unique_sessions'] = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $this->vector_table");
        
        // Размер таблицы
        $table_size = $wpdb->get_var($wpdb->prepare(
            "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'DB Size in MB' 
             FROM information_schema.tables 
             WHERE table_schema = %s AND table_name = %s",
            DB_NAME, $this->vector_table
        ));
        
        $stats['table_size_mb'] = $table_size ?: 0;
        
        return $stats;
    }
    
    /**
     * Генерация уникального ID для вектора
     */
    private function generate_vector_id() {
        return 'vec_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
    }
}
