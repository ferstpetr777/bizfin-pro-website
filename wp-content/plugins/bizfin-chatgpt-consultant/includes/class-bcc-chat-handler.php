<?php
/**
 * Класс для обработки чата
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Chat_Handler {
    
    private $logger;
    private $database;
    private $vector_db;
    private $openai_client;
    private $file_processor;
    
    public function __construct() {
        // Компоненты будут инициализированы в init()
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        $this->database = bizfin_chatgpt_consultant()->get_database();
        $this->vector_db = bizfin_chatgpt_consultant()->get_vector_db();
        $this->openai_client = bizfin_chatgpt_consultant()->get_openai_client();
        $this->file_processor = bizfin_chatgpt_consultant()->get_file_processor();
        
        $this->logger->info('BCC_Chat_Handler initialized');
    }
    
    /**
     * Обработка сообщения пользователя
     */
    public function process_message($message, $session_id = null) {
        try {
            $start_time = microtime(true);
            
            // Генерируем или получаем session_id
            if (!$session_id) {
                $session_id = $this->generate_session_id();
            }
            
            $this->logger->info('Processing message', [
                'session_id' => $session_id,
                'message_length' => strlen($message)
            ]);
            
            // Получаем или создаем сессию
            $session = $this->get_or_create_session($session_id);
            
            // Сохраняем сообщение пользователя
            $user_message_id = $this->database->save_message(
                $session_id,
                'user',
                $message,
                ['timestamp' => current_time('mysql')]
            );
            
            if (!$user_message_id) {
                throw new Exception('Не удалось сохранить сообщение пользователя');
            }
            
            // Сохраняем в векторную БД
            $this->vector_db->save_message_vector($session_id, $message, 'message', [
                'message_id' => $user_message_id,
                'timestamp' => current_time('mysql')
            ]);
            
            // Получаем контекст из истории
            $context = $this->get_chat_context($session_id, $message);
            
            // Формируем сообщения для OpenAI
            $openai_messages = $this->openai_client->build_message_context(
                $context['messages'],
                $context['vector_context']
            );
            
            // Добавляем текущее сообщение пользователя
            $openai_messages[] = [
                'role' => 'user',
                'content' => $message
            ];
            
            // Отправляем запрос в OpenAI
            $response = $this->openai_client->send_message($openai_messages);
            
            if (!$response['success']) {
                throw new Exception($response['error'] ?? 'Ошибка получения ответа от AI');
            }
            
            // Сохраняем ответ ассистента
            $assistant_message_id = $this->database->save_message(
                $session_id,
                'assistant',
                $response['content'],
                [
                    'model' => $response['model'],
                    'tokens_used' => $response['tokens_used'],
                    'processing_time' => $response['processing_time'],
                    'timestamp' => current_time('mysql')
                ],
                $response['tokens_used'],
                $response['model'],
                $response['processing_time']
            );
            
            // Сохраняем ответ в векторную БД
            $this->vector_db->save_message_vector($session_id, $response['content'], 'message', [
                'message_id' => $assistant_message_id,
                'model' => $response['model'],
                'tokens_used' => $response['tokens_used'],
                'timestamp' => current_time('mysql')
            ]);
            
            // Обновляем активность сессии
            $this->database->update_session_activity($session_id);
            
            // Сохраняем статистику
            $this->save_chat_statistics($session_id, $response);
            
            $processing_time = microtime(true) - $start_time;
            
            $this->logger->info('Message processed successfully', [
                'session_id' => $session_id,
                'processing_time' => round($processing_time, 3),
                'tokens_used' => $response['tokens_used']
            ]);
            
            return [
                'success' => true,
                'session_id' => $session_id,
                'response' => $response['content'],
                'model' => $response['model'],
                'tokens_used' => $response['tokens_used'],
                'processing_time' => $processing_time,
                'context_used' => !empty($context['vector_context']),
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Error processing message: ' . $e->getMessage(), [
                'session_id' => $session_id,
                'message' => $message
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'session_id' => $session_id,
                'response' => 'Извините, произошла ошибка при обработке вашего сообщения. Пожалуйста, попробуйте еще раз.',
            ];
        }
    }
    
    /**
     * Получение истории чата
     */
    public function get_chat_history($session_id, $limit = 50) {
        try {
            $this->logger->debug('Getting chat history', ['session_id' => $session_id, 'limit' => $limit]);
            
            $messages = $this->database->get_session_messages($session_id, $limit);
            
            $history = [];
            foreach ($messages as $message) {
                $history[] = [
                    'id' => $message->id,
                    'type' => $message->message_type,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'tokens_used' => $message->tokens_used,
                    'model_used' => $message->model_used,
                ];
            }
            
            $this->logger->debug('Chat history retrieved', [
                'session_id' => $session_id,
                'messages_count' => count($history)
            ]);
            
            return $history;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting chat history: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получение или создание сессии
     */
    private function get_or_create_session($session_id) {
        $session = $this->database->get_session($session_id);
        
        if (!$session) {
            // Создаем новую сессию
            $user_identifier = $this->get_user_identifier();
            
            $this->database->save_session($session_id, $user_identifier, [
                'created_at' => current_time('mysql'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $this->get_client_ip(),
            ]);
            
            $session = $this->database->get_session($session_id);
            
            $this->logger->info('New session created', ['session_id' => $session_id]);
        }
        
        return $session;
    }
    
    /**
     * Получение контекста чата
     */
    private function get_chat_context($session_id, $current_message) {
        $max_history = get_option('bcc_max_history_messages', 50);
        
        // Получаем историю сообщений
        $messages = $this->database->get_session_messages($session_id, $max_history);
        
        // Получаем контекст из векторной БД
        $vector_context = $this->vector_db->get_session_context($session_id, $current_message, 5);
        
        return [
            'messages' => $messages,
            'vector_context' => $vector_context,
        ];
    }
    
    /**
     * Генерация уникального ID сессии
     */
    private function generate_session_id() {
        $user_identifier = $this->get_user_identifier();
        $timestamp = time();
        $random = wp_generate_password(8, false);
        
        return 'bcc_' . md5($user_identifier . $timestamp . $random);
    }
    
    /**
     * Получение идентификатора пользователя
     */
    private function get_user_identifier() {
        // Пытаемся получить из cookie
        if (isset($_COOKIE['bcc_user_id'])) {
            return sanitize_text_field($_COOKIE['bcc_user_id']);
        }
        
        // Генерируем новый идентификатор
        $user_id = 'user_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
        
        // Устанавливаем cookie на 30 дней
        setcookie('bcc_user_id', $user_id, time() + (30 * 24 * 60 * 60), '/', '', is_ssl(), true);
        
        return $user_id;
    }
    
    /**
     * Получение IP адреса клиента
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Сохранение статистики чата
     */
    private function save_chat_statistics($session_id, $response) {
        try {
            $today = current_time('Y-m-d');
            
            // Увеличиваем счетчик сообщений
            $this->database->save_statistic($today, 'messages_sent', 1);
            
            // Увеличиваем счетчик токенов
            $this->database->save_statistic($today, 'tokens_used', $response['tokens_used']);
            
            // Увеличиваем счетчик сессий (только для новых сообщений в сессии)
            $session = $this->database->get_session($session_id);
            if ($session && $session->message_count == 1) {
                $this->database->save_statistic($today, 'new_sessions', 1);
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error saving chat statistics: ' . $e->getMessage());
        }
    }
    
    /**
     * Обработка загруженного файла
     */
    public function process_uploaded_file($file, $session_id) {
        try {
            $this->logger->info('Processing uploaded file', [
                'session_id' => $session_id,
                'filename' => $file['name']
            ]);
            
            // Обрабатываем файл
            $file_info = $this->file_processor->process_upload($file);
            
            if (!$file_info['success']) {
                throw new Exception('Ошибка обработки файла');
            }
            
            // Сохраняем в базу данных
            $file_id = $this->file_processor->save_file_to_database($session_id, $file_info);
            
            if (!$file_id) {
                throw new Exception('Не удалось сохранить информацию о файле');
            }
            
            // Обновляем активность сессии
            $this->database->update_session_activity($session_id);
            
            $this->logger->info('File processed successfully', [
                'session_id' => $session_id,
                'file_id' => $file_id,
                'filename' => $file_info['original_name']
            ]);
            
            return [
                'success' => true,
                'file_id' => $file_id,
                'file_info' => $file_info,
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Error processing uploaded file: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Получение файлов сессии
     */
    public function get_session_files($session_id) {
        try {
            $files = $this->file_processor->get_session_files($session_id);
            
            $file_list = [];
            foreach ($files as $file) {
                $file_list[] = [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'file_type' => $file->file_type,
                    'file_size' => $file->file_size,
                    'processing_status' => $file->processing_status,
                    'created_at' => $file->created_at,
                    'has_text' => !empty($file->extracted_text),
                ];
            }
            
            return $file_list;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting session files: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Очистка старых сессий
     */
    public function cleanup_old_sessions($days = 30) {
        try {
            $this->logger->info("Cleaning up sessions older than {$days} days");
            
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            // Получаем старые сессии
            global $wpdb;
            $sessions_table = $wpdb->prefix . 'bcc_sessions';
            $old_sessions = $wpdb->get_results($wpdb->prepare(
                "SELECT session_id FROM $sessions_table WHERE last_activity < %s",
                $cutoff_date
            ));
            
            $deleted_count = 0;
            foreach ($old_sessions as $session) {
                // Удаляем векторы сессии
                $this->vector_db->delete_session_vectors($session->session_id);
                
                // Удаляем файлы сессии
                $files = $this->file_processor->get_session_files($session->session_id);
                foreach ($files as $file) {
                    $this->file_processor->delete_file($file->id);
                }
                
                $deleted_count++;
            }
            
            // Очищаем данные в основной БД
            $this->database->cleanup_old_data($days);
            
            $this->logger->info("Cleanup completed", ['deleted_sessions' => $deleted_count]);
            
            return $deleted_count;
            
        } catch (Exception $e) {
            $this->logger->error('Error cleaning up old sessions: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение статистики чата
     */
    public function get_chat_statistics($days = 30) {
        try {
            $start_date = date('Y-m-d', strtotime("-{$days} days"));
            $end_date = current_time('Y-m-d');
            
            $stats = [];
            
            // Статистика сообщений
            $messages_stats = $this->database->get_statistics('messages_sent', $start_date, $end_date);
            $stats['total_messages'] = array_sum(array_column($messages_stats, 'metric_value'));
            
            // Статистика токенов
            $tokens_stats = $this->database->get_statistics('tokens_used', $start_date, $end_date);
            $stats['total_tokens'] = array_sum(array_column($tokens_stats, 'metric_value'));
            
            // Статистика сессий
            $sessions_stats = $this->database->get_statistics('new_sessions', $start_date, $end_date);
            $stats['total_sessions'] = array_sum(array_column($sessions_stats, 'metric_value'));
            
            // Статистика файлов
            $file_stats = $this->file_processor->get_file_stats();
            $stats['total_files'] = $file_stats['total_files'];
            $stats['files_by_type'] = $file_stats['by_type'];
            
            // Статистика векторов
            $vector_stats = $this->vector_db->get_vector_stats();
            $stats['total_vectors'] = $vector_stats['total_vectors'];
            $stats['vectors_by_type'] = $vector_stats['by_type'];
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting chat statistics: ' . $e->getMessage());
            return [];
        }
    }
}
