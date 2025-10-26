<?php
// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('sic_options', array());
$default_options = array(
    'max_file_size' => 400,
    'quality' => 85,
    'format' => 'webp',
    'auto_compress' => true,
    'preserve_metadata' => true,
    'backup_originals' => false
);

$options = wp_parse_args($options, $default_options);

if (isset($_POST['submit'])) {
    $new_options = array(
        'max_file_size' => intval($_POST['max_file_size']),
        'quality' => intval($_POST['quality']),
        'format' => sanitize_text_field($_POST['format']),
        'auto_compress' => isset($_POST['auto_compress']),
        'preserve_metadata' => isset($_POST['preserve_metadata']),
        'backup_originals' => isset($_POST['backup_originals'])
    );
    
    update_option('sic_options', $new_options);
    $options = $new_options;
    
    echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
}
?>

<div class="wrap">
    <h1>Настройки Smart Image Compressor</h1>
    
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">Максимальный размер файла (KB)</th>
                <td>
                    <input type="number" name="max_file_size" value="<?php echo esc_attr($options['max_file_size']); ?>" 
                           min="50" max="2000" class="regular-text" />
                    <p class="description">Изображения больше этого размера будут сжаты</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Качество сжатия</th>
                <td>
                    <input type="range" name="quality" value="<?php echo esc_attr($options['quality']); ?>" 
                           min="10" max="100" class="regular-text" id="quality-slider" />
                    <span id="quality-value"><?php echo esc_attr($options['quality']); ?>%</span>
                    <p class="description">Чем выше качество, тем больше размер файла</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Формат сжатия</th>
                <td>
                    <select name="format" class="regular-text">
                        <option value="webp" <?php selected($options['format'], 'webp'); ?>>WebP (рекомендуется)</option>
                        <option value="jpeg" <?php selected($options['format'], 'jpeg'); ?>>JPEG</option>
                        <option value="png" <?php selected($options['format'], 'png'); ?>>PNG</option>
                    </select>
                    <p class="description">WebP обеспечивает лучшее сжатие при сохранении качества</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Автоматическое сжатие</th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_compress" value="1" 
                               <?php checked($options['auto_compress']); ?> />
                        Сжимать изображения автоматически при загрузке
                    </label>
                    <p class="description">Включите для автоматической обработки новых изображений</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Сохранять метаданные</th>
                <td>
                    <label>
                        <input type="checkbox" name="preserve_metadata" value="1" 
                               <?php checked($options['preserve_metadata']); ?> />
                        Сохранять EXIF данные и метаданные
                    </label>
                    <p class="description">Рекомендуется для SEO-оптимизации</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Резервные копии</th>
                <td>
                    <label>
                        <input type="checkbox" name="backup_originals" value="1" 
                               <?php checked($options['backup_originals']); ?> />
                        Создавать резервные копии оригинальных файлов
                    </label>
                    <p class="description">Оригиналы будут сохранены с расширением .backup</p>
                </td>
            </tr>
        </table>
        
        <h2>Дополнительные настройки</h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">Обработка при загрузке</th>
                <td>
                    <label>
                        <input type="checkbox" name="process_on_upload" value="1" 
                               <?php checked(get_option('sic_process_on_upload', true)); ?> />
                        Обрабатывать изображения сразу после загрузки
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Размеры для обработки</th>
                <td>
                    <label>
                        <input type="checkbox" name="process_thumbnails" value="1" 
                               <?php checked(get_option('sic_process_thumbnails', false)); ?> />
                        Обрабатывать также миниатюры и дополнительные размеры
                    </label>
                    <p class="description">Внимание: может значительно увеличить время обработки</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Уведомления</th>
                <td>
                    <label>
                        <input type="checkbox" name="show_notifications" value="1" 
                               <?php checked(get_option('sic_show_notifications', true)); ?> />
                        Показывать уведомления о результатах сжатия
                    </label>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Сохранить настройки'); ?>
    </form>
    
    <div class="sic-settings-info">
        <h2>Информация о плагине</h2>
        <div class="sic-info-grid">
            <div class="sic-info-item">
                <h3>Поддерживаемые форматы</h3>
                <ul>
                    <li>JPEG (.jpg, .jpeg)</li>
                    <li>PNG (.png)</li>
                    <li>GIF (.gif)</li>
                    <li>WebP (.webp)</li>
                </ul>
            </div>
            
            <div class="sic-info-item">
                <h3>Функции</h3>
                <ul>
                    <li>Автоматическое сжатие при загрузке</li>
                    <li>Пакетная обработка существующих изображений</li>
                    <li>Сохранение SEO-атрибутов</li>
                    <li>Поддержка WebP формата</li>
                    <li>Настраиваемое качество сжатия</li>
                </ul>
            </div>
            
            <div class="sic-info-item">
                <h3>Рекомендации</h3>
                <ul>
                    <li>Используйте WebP для лучшего сжатия</li>
                    <li>Качество 80-90% оптимально для большинства случаев</li>
                    <li>Включите резервные копии для важных изображений</li>
                    <li>Регулярно проверяйте результаты сжатия</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Обновление значения слайдера качества
    $('#quality-slider').on('input', function() {
        $('#quality-value').text($(this).val() + '%');
    });
    
    // Предупреждение о резервных копиях
    $('input[name="backup_originals"]').on('change', function() {
        if ($(this).is(':checked')) {
            if (!confirm('Включение резервных копий может значительно увеличить использование дискового пространства. Продолжить?')) {
                $(this).prop('checked', false);
            }
        }
    });
    
    // Предупреждение о обработке миниатюр
    $('input[name="process_thumbnails"]').on('change', function() {
        if ($(this).is(':checked')) {
            if (!confirm('Обработка миниатюр может значительно увеличить время обработки и использование ресурсов сервера. Продолжить?')) {
                $(this).prop('checked', false);
            }
        }
    });
});
</script>

