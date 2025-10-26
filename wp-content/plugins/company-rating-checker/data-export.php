<?php
/**
 * Система экспорта данных в различных форматах
 * Company Rating Checker - Data Export System
 */

if (!defined('ABSPATH')) { exit; }

class DataExport {
    
    private $export_dir;
    private $max_file_size = 10485760; // 10 MB
    
    public function __construct() {
        $this->export_dir = wp_upload_dir()['basedir'] . '/company-rating-exports/';
        $this->ensure_export_directory();
    }
    
    /**
     * Создание директории для экспорта
     */
    private function ensure_export_directory() {
        if (!file_exists($this->export_dir)) {
            wp_mkdir_p($this->export_dir);
            
            // Создаем .htaccess для защиты
            $htaccess_content = "Order Deny,Allow\nDeny from all\n";
            file_put_contents($this->export_dir . '.htaccess', $htaccess_content);
        }
    }
    
    /**
     * Экспорт данных компании в CSV
     */
    public function export_company_csv($company_data, $filename = null) {
        if (!$filename) {
            $filename = 'company_rating_' . date('Y-m-d_H-i-s') . '.csv';
        }
        
        $filepath = $this->export_dir . $filename;
        
        $csv_data = $this->prepare_csv_data($company_data);
        
        $file = fopen($filepath, 'w');
        if (!$file) {
            return new WP_Error('file_error', 'Не удалось создать файл для экспорта');
        }
        
        // Добавляем BOM для корректного отображения в Excel
        fwrite($file, "\xEF\xBB\xBF");
        
        foreach ($csv_data as $row) {
            fputcsv($file, $row, ';', '"');
        }
        
        fclose($file);
        
        return array(
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'download_url' => $this->get_download_url($filename),
            'size' => filesize($filepath)
        );
    }
    
    /**
     * Экспорт данных компании в Excel (XLSX)
     */
    public function export_company_excel($company_data, $filename = null) {
        if (!$filename) {
            $filename = 'company_rating_' . date('Y-m-d_H-i-s') . '.xlsx';
        }
        
        $filepath = $this->export_dir . $filename;
        
        // Создаем простой Excel файл в формате XML
        $excel_content = $this->generate_excel_content($company_data);
        
        if (file_put_contents($filepath, $excel_content) === false) {
            return new WP_Error('file_error', 'Не удалось создать Excel файл');
        }
        
        return array(
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'download_url' => $this->get_download_url($filename),
            'size' => filesize($filepath)
        );
    }
    
    /**
     * Экспорт данных компании в PDF
     */
    public function export_company_pdf($company_data, $filename = null) {
        if (!$filename) {
            $filename = 'company_rating_' . date('Y-m-d_H-i-s') . '.pdf';
        }
        
        $filepath = $this->export_dir . $filename;
        
        // Создаем простой PDF файл
        $pdf_content = $this->generate_pdf_content($company_data);
        
        if (file_put_contents($filepath, $pdf_content) === false) {
            return new WP_Error('file_error', 'Не удалось создать PDF файл');
        }
        
        return array(
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'download_url' => $this->get_download_url($filename),
            'size' => filesize($filepath)
        );
    }
    
    /**
     * Подготовка данных для CSV
     */
    private function prepare_csv_data($company_data) {
        $data = array();
        
        // Заголовки
        $data[] = array(
            'Параметр',
            'Значение',
            'Описание'
        );
        
        // Основная информация о компании
        $data[] = array('=== ОСНОВНАЯ ИНФОРМАЦИЯ ===', '', '');
        $data[] = array('Название', $company_data['company']['name']['full'] ?? 'Не указано', 'Полное наименование компании');
        $data[] = array('ИНН', $company_data['company']['inn'] ?? 'Не указан', 'Идентификационный номер налогоплательщика');
        $data[] = array('ОГРН', $company_data['company']['ogrn'] ?? 'Не указан', 'Основной государственный регистрационный номер');
        $data[] = array('Статус', $company_data['company']['state']['status'] ?? 'Не указан', 'Статус компании');
        
        // Рейтинг
        $data[] = array('=== РЕЙТИНГ ===', '', '');
        $data[] = array('Общий балл', $company_data['rating']['total_score'] ?? 0, 'Общий балл рейтинга');
        $data[] = array('Максимальный балл', $company_data['rating']['max_score'] ?? 0, 'Максимально возможный балл');
        $data[] = array('Уровень рейтинга', $company_data['rating']['rating']['name'] ?? 'Не определен', 'Уровень рейтинга компании');
        
        // Факторы оценки
        $data[] = array('=== ФАКТОРЫ ОЦЕНКИ ===', '', '');
        if (isset($company_data['rating']['factors'])) {
            foreach ($company_data['rating']['factors'] as $factor_key => $factor) {
                $data[] = array(
                    $factor['name'],
                    $factor['score'] . '/' . $factor['max_score'],
                    $factor['description']
                );
            }
        }
        
        // ФНС данные
        if (isset($company_data['company']['fns'])) {
            $data[] = array('=== ФНС ДАННЫЕ ===', '', '');
            $fns_data = $company_data['company']['fns'];
            
            if (isset($fns_data['revenue'])) {
                $data[] = array('Выручка', number_format($fns_data['revenue'], 0, ',', ' ') . ' руб.', 'Годовая выручка');
            }
            if (isset($fns_data['profit'])) {
                $data[] = array('Прибыль', number_format($fns_data['profit'], 0, ',', ' ') . ' руб.', 'Годовая прибыль');
            }
            if (isset($fns_data['profitability'])) {
                $data[] = array('Рентабельность', round($fns_data['profitability'], 2) . '%', 'Рентабельность продаж');
            }
            if (isset($fns_data['debt_ratio'])) {
                $data[] = array('Доля задолженности', round($fns_data['debt_ratio'], 2) . '%', 'Доля задолженности в выручке');
            }
            if (isset($fns_data['bankruptcy_risk'])) {
                $data[] = array('Риск банкротства', $fns_data['bankruptcy_risk'], 'Уровень риска банкротства');
            }
        }
        
        // Росстат данные
        if (isset($company_data['company']['rosstat'])) {
            $data[] = array('=== РОССТАТ ДАННЫЕ ===', '', '');
            $rosstat_data = $company_data['company']['rosstat'];
            
            if (isset($rosstat_data['region']['region_name'])) {
                $data[] = array('Регион', $rosstat_data['region']['region_name'], 'Регион регистрации');
            }
            if (isset($rosstat_data['sector']['sector_name'])) {
                $data[] = array('Отрасль', $rosstat_data['sector']['sector_name'], 'Основная отрасль деятельности');
            }
            if (isset($rosstat_data['enterprise_size']['size_category'])) {
                $data[] = array('Размер предприятия', $rosstat_data['enterprise_size']['size_category'], 'Категория размера предприятия');
            }
            if (isset($rosstat_data['employment']['employment_stability'])) {
                $data[] = array('Стабильность занятости', round($rosstat_data['employment']['employment_stability'] * 100, 1) . '%', 'Уровень стабильности занятости');
            }
        }
        
        // Арбитражные данные
        if (isset($company_data['company']['arbitration'])) {
            $data[] = array('=== АРБИТРАЖНЫЕ ДАННЫЕ ===', '', '');
            $arbitration_data = $company_data['company']['arbitration'];
            
            if (isset($arbitration_data['risk_level'])) {
                $data[] = array('Уровень арбитражного риска', $arbitration_data['risk_level'], 'Уровень судебных рисков');
            }
            if (isset($arbitration_data['total_cases'])) {
                $data[] = array('Количество дел', $arbitration_data['total_cases'], 'Общее количество арбитражных дел');
            }
        }
        
        // Данные о закупках
        if (isset($company_data['company']['zakupki'])) {
            $data[] = array('=== ДАННЫЕ О ЗАКУПКАХ ===', '', '');
            $zakupki_data = $company_data['company']['zakupki'];
            
            if (isset($zakupki_data['total_contracts'])) {
                $data[] = array('Количество контрактов', $zakupki_data['total_contracts'], 'Общее количество контрактов');
            }
            if (isset($zakupki_data['total_amount'])) {
                $data[] = array('Общая сумма контрактов', number_format($zakupki_data['total_amount'], 0, ',', ' ') . ' руб.', 'Общая сумма всех контрактов');
            }
            if (isset($zakupki_data['summary']['reputation_level'])) {
                $data[] = array('Уровень репутации', $zakupki_data['summary']['reputation_level'], 'Репутация в госзакупках');
            }
        }
        
        // Расширенная аналитика
        if (isset($company_data['rating']['advanced_analytics'])) {
            $data[] = array('=== РАСШИРЕННАЯ АНАЛИТИКА ===', '', '');
            $analytics = $company_data['rating']['advanced_analytics'];
            
            $data[] = array('Общий балл аналитики', round($analytics['overall_score'], 1) . '/100', 'Общий балл расширенной аналитики');
            
            if (isset($analytics['financial_health'])) {
                $data[] = array('Финансовое здоровье', $analytics['financial_health']['score'] . '/' . $analytics['financial_health']['max_score'] . ' (' . $analytics['financial_health']['level'] . ')', 'Оценка финансового здоровья');
            }
            
            if (isset($analytics['risk_assessment']['total_risk'])) {
                $data[] = array('Общий риск', round($analytics['risk_assessment']['total_risk'] * 100, 1) . '% (' . $analytics['risk_assessment']['risk_level'] . ')', 'Общая оценка рисков');
            }
            
            if (!empty($analytics['recommendations'])) {
                $data[] = array('Рекомендации', implode('; ', $analytics['recommendations']), 'Рекомендации по улучшению');
            }
        }
        
        // Метаданные
        $data[] = array('=== МЕТАДАННЫЕ ===', '', '');
        $data[] = array('Дата экспорта', current_time('Y-m-d H:i:s'), 'Дата и время создания отчета');
        $data[] = array('Версия плагина', CRC_VERSION, 'Версия плагина Company Rating Checker');
        
        return $data;
    }
    
    /**
     * Генерация Excel контента
     */
    private function generate_excel_content($company_data) {
        $csv_data = $this->prepare_csv_data($company_data);
        
        // Создаем простой XML Excel файл
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        $xml .= '<Worksheet ss:Name="Рейтинг компании">' . "\n";
        $xml .= '<Table>' . "\n";
        
        foreach ($csv_data as $row) {
            $xml .= '<Row>' . "\n";
            foreach ($row as $cell) {
                $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($cell) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>' . "\n";
        
        return $xml;
    }
    
    /**
     * Генерация PDF контента (простой HTML-подобный формат)
     */
    private function generate_pdf_content($company_data) {
        $content = '';
        
        // Заголовок
        $content .= "ОТЧЕТ О РЕЙТИНГЕ КОМПАНИИ\n";
        $content .= "================================\n\n";
        
        // Основная информация
        $content .= "ОСНОВНАЯ ИНФОРМАЦИЯ:\n";
        $content .= "Название: " . ($company_data['company']['name']['full'] ?? 'Не указано') . "\n";
        $content .= "ИНН: " . ($company_data['company']['inn'] ?? 'Не указан') . "\n";
        $content .= "ОГРН: " . ($company_data['company']['ogrn'] ?? 'Не указан') . "\n";
        $content .= "Статус: " . ($company_data['company']['state']['status'] ?? 'Не указан') . "\n\n";
        
        // Рейтинг
        $content .= "РЕЙТИНГ:\n";
        $content .= "Общий балл: " . ($company_data['rating']['total_score'] ?? 0) . "\n";
        $content .= "Максимальный балл: " . ($company_data['rating']['max_score'] ?? 0) . "\n";
        $content .= "Уровень рейтинга: " . ($company_data['rating']['rating']['name'] ?? 'Не определен') . "\n\n";
        
        // Факторы оценки
        $content .= "ФАКТОРЫ ОЦЕНКИ:\n";
        if (isset($company_data['rating']['factors'])) {
            foreach ($company_data['rating']['factors'] as $factor) {
                $content .= "- " . $factor['name'] . ": " . $factor['score'] . "/" . $factor['max_score'] . " - " . $factor['description'] . "\n";
            }
        }
        $content .= "\n";
        
        // ФНС данные
        if (isset($company_data['company']['fns'])) {
            $content .= "ФНС ДАННЫЕ:\n";
            $fns_data = $company_data['company']['fns'];
            
            if (isset($fns_data['revenue'])) {
                $content .= "Выручка: " . number_format($fns_data['revenue'], 0, ',', ' ') . " руб.\n";
            }
            if (isset($fns_data['profit'])) {
                $content .= "Прибыль: " . number_format($fns_data['profit'], 0, ',', ' ') . " руб.\n";
            }
            if (isset($fns_data['profitability'])) {
                $content .= "Рентабельность: " . round($fns_data['profitability'], 2) . "%\n";
            }
            if (isset($fns_data['bankruptcy_risk'])) {
                $content .= "Риск банкротства: " . $fns_data['bankruptcy_risk'] . "\n";
            }
            $content .= "\n";
        }
        
        // Росстат данные
        if (isset($company_data['company']['rosstat'])) {
            $content .= "РОССТАТ ДАННЫЕ:\n";
            $rosstat_data = $company_data['company']['rosstat'];
            
            if (isset($rosstat_data['region']['region_name'])) {
                $content .= "Регион: " . $rosstat_data['region']['region_name'] . "\n";
            }
            if (isset($rosstat_data['sector']['sector_name'])) {
                $content .= "Отрасль: " . $rosstat_data['sector']['sector_name'] . "\n";
            }
            if (isset($rosstat_data['enterprise_size']['size_category'])) {
                $content .= "Размер предприятия: " . $rosstat_data['enterprise_size']['size_category'] . "\n";
            }
            $content .= "\n";
        }
        
        // Расширенная аналитика
        if (isset($company_data['rating']['advanced_analytics'])) {
            $content .= "РАСШИРЕННАЯ АНАЛИТИКА:\n";
            $analytics = $company_data['rating']['advanced_analytics'];
            
            $content .= "Общий балл аналитики: " . round($analytics['overall_score'], 1) . "/100\n";
            
            if (isset($analytics['risk_assessment']['total_risk'])) {
                $content .= "Общий риск: " . round($analytics['risk_assessment']['total_risk'] * 100, 1) . "% (" . $analytics['risk_assessment']['risk_level'] . ")\n";
            }
            
            if (!empty($analytics['recommendations'])) {
                $content .= "Рекомендации:\n";
                foreach ($analytics['recommendations'] as $recommendation) {
                    $content .= "- " . $recommendation . "\n";
                }
            }
            $content .= "\n";
        }
        
        // Метаданные
        $content .= "МЕТАДАННЫЕ:\n";
        $content .= "Дата экспорта: " . current_time('Y-m-d H:i:s') . "\n";
        $content .= "Версия плагина: " . CRC_VERSION . "\n";
        
        return $content;
    }
    
    /**
     * Получение URL для скачивания файла
     */
    private function get_download_url($filename) {
        return wp_upload_dir()['baseurl'] . '/company-rating-exports/' . $filename;
    }
    
    /**
     * Очистка старых файлов экспорта
     */
    public function cleanup_old_exports($days = 7) {
        $files = glob($this->export_dir . '*');
        $deleted_count = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > ($days * 24 * 3600)) {
                if (unlink($file)) {
                    $deleted_count++;
                }
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Получение списка файлов экспорта
     */
    public function get_export_files() {
        $files = glob($this->export_dir . '*');
        $export_files = array();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $export_files[] = array(
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created' => filemtime($file),
                    'download_url' => $this->get_download_url(basename($file))
                );
            }
        }
        
        // Сортируем по дате создания (новые сверху)
        usort($export_files, function($a, $b) {
            return $b['created'] - $a['created'];
        });
        
        return $export_files;
    }
    
    /**
     * Удаление файла экспорта
     */
    public function delete_export_file($filename) {
        $filepath = $this->export_dir . $filename;
        
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Получение статистики экспорта
     */
    public function get_export_stats() {
        $files = $this->get_export_files();
        $total_size = 0;
        $file_types = array();
        
        foreach ($files as $file) {
            $total_size += $file['size'];
            $extension = pathinfo($file['filename'], PATHINFO_EXTENSION);
            $file_types[$extension] = ($file_types[$extension] ?? 0) + 1;
        }
        
        return array(
            'total_files' => count($files),
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / (1024 * 1024), 2),
            'file_types' => $file_types,
            'oldest_file' => !empty($files) ? min(array_column($files, 'created')) : null,
            'newest_file' => !empty($files) ? max(array_column($files, 'created')) : null
        );
    }
}
?>
