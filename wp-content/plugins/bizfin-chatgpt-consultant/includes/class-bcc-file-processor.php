<?php
/**
 * Класс для обработки файлов
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_File_Processor {
    
    private $logger;
    private $database;
    private $vector_db;
    private $upload_dir;
    private $allowed_types;
    private $max_file_size;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/bizfin-chatgpt';
        
        $this->allowed_types = explode(',', get_option('bcc_allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'));
        $this->max_file_size = get_option('bcc_max_file_size', 10485760); // 10MB
    }
    
    /**
     * Инициализация
     */
    public function init() {
        $this->logger = bizfin_chatgpt_consultant()->get_logger();
        $this->database = bizfin_chatgpt_consultant()->get_database();
        $this->vector_db = bizfin_chatgpt_consultant()->get_vector_db();
        
        $this->logger->info('BCC_File_Processor initialized');
    }
    
    /**
     * Обработка загруженного файла
     */
    public function process_upload($file) {
        try {
            $this->logger->info('Processing file upload', ['filename' => $file['name']]);
            
            // Проверяем ошибки загрузки
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Ошибка загрузки файла: ' . $this->get_upload_error_message($file['error']));
            }
            
            // Проверяем размер файла
            if ($file['size'] > $this->max_file_size) {
                throw new Exception('Файл слишком большой. Максимальный размер: ' . $this->format_file_size($this->max_file_size));
            }
            
            // Проверяем тип файла
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $this->allowed_types)) {
                throw new Exception('Неподдерживаемый тип файла. Разрешены: ' . implode(', ', $this->allowed_types));
            }
            
            // Определяем MIME тип
            $mime_type = $file['type'];
            if (!$mime_type) {
                $mime_type = mime_content_type($file['tmp_name']);
            }
            
            // Создаем уникальное имя файла
            $unique_filename = $this->generate_unique_filename($file['name']);
            
            // Определяем директорию для сохранения
            $subdir = $this->get_file_subdirectory($file_extension);
            $target_dir = $this->upload_dir . '/' . $subdir;
            
            if (!file_exists($target_dir)) {
                wp_mkdir_p($target_dir);
            }
            
            $target_path = $target_dir . '/' . $unique_filename;
            
            // Перемещаем файл
            if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                throw new Exception('Не удалось сохранить файл');
            }
            
            // Обрабатываем файл в зависимости от типа
            $extracted_text = $this->extract_text_from_file($target_path, $file_extension, $mime_type);
            
            $this->logger->info('File processed successfully', [
                'original_name' => $file['name'],
                'saved_path' => $target_path,
                'extracted_text_length' => strlen($extracted_text)
            ]);
            
            return [
                'success' => true,
                'original_name' => $file['name'],
                'saved_path' => $target_path,
                'file_size' => $file['size'],
                'mime_type' => $mime_type,
                'file_extension' => $file_extension,
                'extracted_text' => $extracted_text,
                'unique_filename' => $unique_filename,
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Error processing file upload: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Извлечение текста из файла
     */
    private function extract_text_from_file($file_path, $extension, $mime_type) {
        try {
            $this->logger->debug('Extracting text from file', ['path' => $file_path, 'extension' => $extension]);
            
            switch ($extension) {
                case 'pdf':
                    return $this->extract_text_from_pdf($file_path);
                    
                case 'doc':
                case 'docx':
                    return $this->extract_text_from_word($file_path);
                    
                case 'xls':
                case 'xlsx':
                    return $this->extract_text_from_excel($file_path);
                    
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    return $this->extract_text_from_image($file_path);
                    
                case 'txt':
                    return file_get_contents($file_path);
                    
                default:
                    $this->logger->warning('Unsupported file type for text extraction', ['extension' => $extension]);
                    return '';
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error extracting text from file: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Извлечение текста из PDF
     */
    private function extract_text_from_pdf($file_path) {
        // Простое извлечение текста из PDF (можно улучшить с помощью библиотек)
        $content = '';
        
        // Попытка использовать pdftotext если доступен
        if (function_exists('shell_exec') && shell_exec('which pdftotext')) {
            $temp_file = tempnam(sys_get_temp_dir(), 'pdf_text_');
            $command = "pdftotext -layout '{$file_path}' '{$temp_file}' 2>/dev/null";
            shell_exec($command);
            
            if (file_exists($temp_file)) {
                $content = file_get_contents($temp_file);
                unlink($temp_file);
            }
        }
        
        // Если pdftotext недоступен, возвращаем базовую информацию
        if (empty($content)) {
            $content = "PDF файл: " . basename($file_path) . "\n";
            $content .= "Размер: " . $this->format_file_size(filesize($file_path)) . "\n";
            $content .= "Тип: PDF документ\n";
            $content .= "Для полного извлечения текста требуется дополнительная обработка.";
        }
        
        return $content;
    }
    
    /**
     * Извлечение текста из Word документа
     */
    private function extract_text_from_word($file_path) {
        $content = '';
        
        // Попытка использовать antiword для .doc файлов
        if (function_exists('shell_exec') && shell_exec('which antiword')) {
            $command = "antiword '{$file_path}' 2>/dev/null";
            $content = shell_exec($command);
        }
        
        // Для .docx файлов можно использовать zip архивирование
        if (empty($content) && pathinfo($file_path, PATHINFO_EXTENSION) === 'docx') {
            $content = $this->extract_text_from_docx($file_path);
        }
        
        // Если извлечение не удалось, возвращаем базовую информацию
        if (empty($content)) {
            $content = "Word документ: " . basename($file_path) . "\n";
            $content .= "Размер: " . $this->format_file_size(filesize($file_path)) . "\n";
            $content .= "Тип: Microsoft Word документ\n";
            $content .= "Для полного извлечения текста требуется дополнительная обработка.";
        }
        
        return $content;
    }
    
    /**
     * Извлечение текста из DOCX файла
     */
    private function extract_text_from_docx($file_path) {
        try {
            $zip = new ZipArchive();
            if ($zip->open($file_path) === TRUE) {
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                
                if ($content) {
                    // Удаляем XML теги и получаем чистый текст
                    $content = strip_tags($content);
                    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                    $content = preg_replace('/\s+/', ' ', $content);
                    return trim($content);
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Error extracting text from DOCX: ' . $e->getMessage());
        }
        
        return '';
    }
    
    /**
     * Извлечение текста из Excel файла
     */
    private function extract_text_from_excel($file_path) {
        $content = '';
        
        // Попытка использовать xls2csv если доступен
        if (function_exists('shell_exec') && shell_exec('which xls2csv')) {
            $command = "xls2csv '{$file_path}' 2>/dev/null";
            $content = shell_exec($command);
        }
        
        // Если извлечение не удалось, возвращаем базовую информацию
        if (empty($content)) {
            $content = "Excel файл: " . basename($file_path) . "\n";
            $content .= "Размер: " . $this->format_file_size(filesize($file_path)) . "\n";
            $content .= "Тип: Microsoft Excel таблица\n";
            $content .= "Для полного извлечения данных требуется дополнительная обработка.";
        }
        
        return $content;
    }
    
    /**
     * Извлечение текста из изображения (OCR)
     */
    private function extract_text_from_image($file_path) {
        $content = '';
        
        // Попытка использовать tesseract для OCR
        if (function_exists('shell_exec') && shell_exec('which tesseract')) {
            $temp_file = tempnam(sys_get_temp_dir(), 'ocr_text_');
            $command = "tesseract '{$file_path}' '{$temp_file}' -l rus+eng 2>/dev/null";
            shell_exec($command);
            
            if (file_exists($temp_file . '.txt')) {
                $content = file_get_contents($temp_file . '.txt');
                unlink($temp_file . '.txt');
            }
            unlink($temp_file);
        }
        
        // Если OCR недоступен, возвращаем базовую информацию
        if (empty($content)) {
            $content = "Изображение: " . basename($file_path) . "\n";
            $content .= "Размер: " . $this->format_file_size(filesize($file_path)) . "\n";
            $content .= "Тип: Изображение\n";
            $content .= "Для извлечения текста из изображения требуется OCR обработка.";
        }
        
        return $content;
    }
    
    /**
     * Сохранение информации о файле в базу данных
     */
    public function save_file_to_database($session_id, $file_info) {
        try {
            $this->logger->debug('Saving file info to database', ['session_id' => $session_id]);
            
            $file_id = $this->database->save_file(
                $session_id,
                $file_info['original_name'],
                $file_info['saved_path'],
                $file_info['file_extension'],
                $file_info['file_size'],
                $file_info['mime_type'],
                [
                    'unique_filename' => $file_info['unique_filename'],
                    'upload_time' => current_time('mysql'),
                ]
            );
            
            if ($file_id) {
                // Сохраняем в векторную БД если есть извлеченный текст
                if (!empty($file_info['extracted_text'])) {
                    $vector_id = $this->vector_db->save_file_vector(
                        $session_id,
                        $file_info['extracted_text'],
                        [
                            'file_id' => $file_id,
                            'original_name' => $file_info['original_name'],
                            'file_type' => $file_info['file_extension'],
                        ]
                    );
                    
                    if ($vector_id) {
                        $this->database->update_file_processing_status($file_id, 'processed', $file_info['extracted_text'], $vector_id);
                    } else {
                        $this->database->update_file_processing_status($file_id, 'error', $file_info['extracted_text']);
                    }
                } else {
                    $this->database->update_file_processing_status($file_id, 'no_text');
                }
                
                $this->logger->info('File saved to database', ['file_id' => $file_id]);
                return $file_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->error('Error saving file to database: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить файлы сессии
     */
    public function get_session_files($session_id) {
        return $this->database->get_session_files($session_id);
    }
    
    /**
     * Удалить файл
     */
    public function delete_file($file_id) {
        try {
            $files = $this->database->get_session_files(''); // Получаем все файлы для поиска нужного
            $file_to_delete = null;
            
            foreach ($files as $file) {
                if ($file->id == $file_id) {
                    $file_to_delete = $file;
                    break;
                }
            }
            
            if (!$file_to_delete) {
                throw new Exception('Файл не найден');
            }
            
            // Удаляем физический файл
            if (file_exists($file_to_delete->file_path)) {
                unlink($file_to_delete->file_path);
            }
            
            // Удаляем из базы данных
            global $wpdb;
            $table = $wpdb->prefix . 'bcc_files';
            $result = $wpdb->delete($table, ['id' => $file_id]);
            
            if ($result) {
                $this->logger->info('File deleted', ['file_id' => $file_id]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->error('Error deleting file: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить сообщение об ошибке загрузки
     */
    private function get_upload_error_message($error_code) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер, разрешенный сервером',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер, указанный в форме',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная директория',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла была остановлена расширением',
        ];
        
        return $messages[$error_code] ?? 'Неизвестная ошибка загрузки';
    }
    
    /**
     * Генерация уникального имени файла
     */
    private function generate_unique_filename($original_name) {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $name = pathinfo($original_name, PATHINFO_FILENAME);
        $name = sanitize_file_name($name);
        $name = substr($name, 0, 50); // Ограничиваем длину имени
        
        return $name . '_' . uniqid() . '.' . $extension;
    }
    
    /**
     * Получить поддиректорию для файла
     */
    private function get_file_subdirectory($extension) {
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $document_extensions = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        $spreadsheet_extensions = ['xls', 'xlsx', 'csv'];
        
        if (in_array($extension, $image_extensions)) {
            return 'images';
        } elseif (in_array($extension, $document_extensions)) {
            return 'documents';
        } elseif (in_array($extension, $spreadsheet_extensions)) {
            return 'spreadsheets';
        } else {
            return 'other';
        }
    }
    
    /**
     * Форматирование размера файла
     */
    private function format_file_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Получить статистику файлов
     */
    public function get_file_stats() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'bcc_files';
        
        $stats = [];
        
        // Общее количество файлов
        $stats['total_files'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        // Количество по типам
        $type_stats = $wpdb->get_results(
            "SELECT file_type, COUNT(*) as count FROM $table GROUP BY file_type"
        );
        
        $stats['by_type'] = [];
        foreach ($type_stats as $stat) {
            $stats['by_type'][$stat->file_type] = $stat->count;
        }
        
        // Общий размер файлов
        $total_size = $wpdb->get_var("SELECT SUM(file_size) FROM $table");
        $stats['total_size'] = $total_size ?: 0;
        $stats['total_size_formatted'] = $this->format_file_size($stats['total_size']);
        
        // Количество по статусу обработки
        $status_stats = $wpdb->get_results(
            "SELECT processing_status, COUNT(*) as count FROM $table GROUP BY processing_status"
        );
        
        $stats['by_status'] = [];
        foreach ($status_stats as $stat) {
            $stats['by_status'][$stat->processing_status] = $stat->count;
        }
        
        return $stats;
    }
}
