<?php
/**
 * Страница настроек ABP Image Generator
 */

if (!defined('ABSPATH')) exit;

$settings = get_option('abp_image_generator_settings', []);
$default_settings = [
    'auto_generate' => true,
    'model' => 'dall-e-2',
    'size' => '1024x1024',
    'quality' => 'standard',
    'style' => 'natural',
    'max_attempts' => 3,
    'retry_delay' => 5,
    'log_level' => 'info',
    'enable_seo_optimization' => true,
    'auto_alt_text' => true,
    'auto_description' => true
];

$settings = array_merge($default_settings, $settings);
?>

<div class="wrap abp-image-generator">
    <h1>⚙️ Настройки ABP Image Generator</h1>
    
    <form method="post" class="abp-settings-form">
        <?php wp_nonce_field('abp_image_generator_settings', 'abp_settings_nonce'); ?>
        
        <div class="abp-settings-grid">
            
            <!-- Основные настройки -->
            <div class="abp-settings-section">
                <h3>🔧 Основные настройки</h3>
                
                <div class="abp-form-group">
                    <label>
                        <input type="checkbox" name="auto_generate" <?php checked($settings['auto_generate']); ?>>
                        Автоматическая генерация изображений
                    </label>
                    <div class="description">
                        Автоматически генерировать изображения при сохранении новых постов
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="log_level">Уровень логирования</label>
                    <select name="log_level" id="log_level">
                        <option value="debug" <?php selected($settings['log_level'], 'debug'); ?>>Debug</option>
                        <option value="info" <?php selected($settings['log_level'], 'info'); ?>>Info</option>
                        <option value="warning" <?php selected($settings['log_level'], 'warning'); ?>>Warning</option>
                        <option value="error" <?php selected($settings['log_level'], 'error'); ?>>Error</option>
                    </select>
                    <div class="description">
                        Уровень детализации логов
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="max_attempts">Максимальное количество попыток</label>
                    <input type="number" name="max_attempts" id="max_attempts" 
                           value="<?php echo esc_attr($settings['max_attempts']); ?>" min="1" max="10">
                    <div class="description">
                        Количество попыток генерации изображения при ошибке
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="retry_delay">Задержка между попытками (секунды)</label>
                    <input type="number" name="retry_delay" id="retry_delay" 
                           value="<?php echo esc_attr($settings['retry_delay']); ?>" min="1" max="60">
                    <div class="description">
                        Задержка между повторными попытками генерации
                    </div>
                </div>
            </div>

            <!-- Настройки OpenAI -->
            <div class="abp-settings-section">
                <h3>🤖 Настройки OpenAI</h3>
                
                <div class="abp-form-group">
                    <label for="model">Модель генерации</label>
                    <select name="model" id="model">
                        <option value="dall-e-2" <?php selected($settings['model'], 'dall-e-2'); ?>>
                            DALL-E 2 (Дешевле, быстрее)
                        </option>
                        <option value="dall-e-3" <?php selected($settings['model'], 'dall-e-3'); ?>>
                            DALL-E 3 (Качественнее, дороже)
                        </option>
                    </select>
                    <div class="description">
                        Модель для генерации изображений. DALL-E 3 обеспечивает лучшее качество.
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="size">Размер изображения</label>
                    <select name="size" id="size">
                        <option value="1024x1024" <?php selected($settings['size'], '1024x1024'); ?>>1024x1024 (Квадрат)</option>
                        <option value="1792x1024" <?php selected($settings['size'], '1792x1024'); ?>>1792x1024 (Широкий)</option>
                        <option value="1024x1792" <?php selected($settings['size'], '1024x1792'); ?>>1024x1792 (Высокий)</option>
                    </select>
                    <div class="description">
                        Размер генерируемых изображений
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="quality">Качество изображения</label>
                    <select name="quality" id="quality">
                        <option value="standard" <?php selected($settings['quality'], 'standard'); ?>>Standard</option>
                        <option value="hd" <?php selected($settings['quality'], 'hd'); ?>>HD (только для DALL-E 3)</option>
                    </select>
                    <div class="description">
                        Качество генерируемых изображений
                    </div>
                </div>

                <div class="abp-form-group">
                    <label for="style">Стиль изображения</label>
                    <select name="style" id="style">
                        <option value="natural" <?php selected($settings['style'], 'natural'); ?>>Natural</option>
                        <option value="vivid" <?php selected($settings['style'], 'vivid'); ?>>Vivid</option>
                    </select>
                    <div class="description">
                        Стиль генерируемых изображений
                    </div>
                </div>

                <div class="abp-form-group">
                    <div class="abp-notice abp-notice-info">
                        <strong>API ключ OpenAI:</strong> <?php echo substr(ABP_Image_Generator::OPENAI_API_KEY, 0, 20) . '...'; ?>
                        <br>Ключ встроен в код плагина и используется автоматически.
                    </div>
                </div>
            </div>

            <!-- SEO настройки -->
            <div class="abp-settings-section">
                <h3>🔍 SEO настройки</h3>
                
                <div class="abp-form-group">
                    <label>
                        <input type="checkbox" name="enable_seo_optimization" <?php checked($settings['enable_seo_optimization']); ?>>
                        Включить SEO-оптимизацию изображений
                    </label>
                    <div class="description">
                        Автоматически оптимизировать изображения для SEO
                    </div>
                </div>

                <div class="abp-form-group">
                    <label>
                        <input type="checkbox" name="auto_alt_text" <?php checked($settings['auto_alt_text']); ?>>
                        Автоматический alt текст
                    </label>
                    <div class="description">
                        Автоматически создавать alt текст из названия статьи
                    </div>
                </div>

                <div class="abp-form-group">
                    <label>
                        <input type="checkbox" name="auto_description" <?php checked($settings['auto_description']); ?>>
                        Автоматическое описание изображения
                    </label>
                    <div class="description">
                        Автоматически создавать описание изображения
                    </div>
                </div>

                <div class="abp-form-group">
                    <div class="abp-notice abp-notice-success">
                        <strong>SEO функции:</strong>
                        <ul>
                            <li>Автоматическое создание alt текста</li>
                            <li>Оптимизация названия файла</li>
                            <li>Создание описания изображения</li>
                            <li>Проверка SEO соответствия</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- Кнопки действий -->
        <div class="abp-settings-actions">
            <button type="submit" name="save_settings" class="abp-btn abp-btn-success">
                💾 Сохранить настройки
            </button>
            
            <button type="button" class="abp-btn abp-btn-secondary abp-test-settings">
                🧪 Тестировать настройки
            </button>
            
            <button type="button" class="abp-btn abp-btn-secondary abp-reset-settings">
                🔄 Сбросить к умолчанию
            </button>
        </div>

    </form>

    <!-- Информация о настройках -->
    <div class="abp-settings-info">
        <h3>📋 Информация о настройках</h3>
        
        <div class="abp-info-grid">
            <div class="abp-info-card">
                <h4>🎯 Автоматическая генерация</h4>
                <p>При включении плагин будет автоматически генерировать изображения для всех новых постов при их публикации.</p>
            </div>
            
            <div class="abp-info-card">
                <h4>🤖 Модели DALL-E</h4>
                <p><strong>DALL-E 2:</strong> Быстрее и дешевле, подходит для большинства задач.<br>
                <strong>DALL-E 3:</strong> Лучшее качество, но дороже и медленнее.</p>
            </div>
            
            <div class="abp-info-card">
                <h4>📐 Размеры изображений</h4>
                <p><strong>1024x1024:</strong> Квадратные изображения, универсальные.<br>
                <strong>1792x1024:</strong> Широкие изображения для лендингов.<br>
                <strong>1024x1792:</strong> Высокие изображения для мобильных.</p>
            </div>
            
            <div class="abp-info-card">
                <h4>🔍 SEO оптимизация</h4>
                <p>Плагин автоматически создает SEO-оптимизированные изображения с правильными alt текстами и описаниями.</p>
            </div>
        </div>

        <!-- Статистика использования -->
        <div class="abp-usage-stats">
            <h3>📊 Статистика использования</h3>
            
            <?php
            global $wpdb;
            $usage_stats = $wpdb->get_results("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count
                FROM {$wpdb->prefix}abp_image_generations 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 7
            ", ARRAY_A);
            ?>
            
            <?php if (!empty($usage_stats)): ?>
            <table class="abp-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Всего попыток</th>
                        <th>Успешных</th>
                        <th>Успешность</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usage_stats as $stat): ?>
                    <tr>
                        <td><?php echo esc_html(date('d.m.Y', strtotime($stat['date']))); ?></td>
                        <td><?php echo intval($stat['count']); ?></td>
                        <td><?php echo intval($stat['success_count']); ?></td>
                        <td>
                            <?php 
                            $percentage = $stat['count'] > 0 ? round(($stat['success_count'] / $stat['count']) * 100, 1) : 0;
                            echo $percentage . '%';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="abp-notice abp-notice-info">
                Статистика использования пока недоступна. Генерируйте изображения для накопления данных.
            </div>
            <?php endif; ?>
        </div>

        <!-- Рекомендации -->
        <div class="abp-recommendations">
            <h3>💡 Рекомендации</h3>
            
            <div class="abp-recommendation-grid">
                <div class="abp-recommendation">
                    <h4>🎨 Для лучшего качества</h4>
                    <ul>
                        <li>Используйте DALL-E 3 для важных постов</li>
                        <li>Включайте HD качество для премиум контента</li>
                        <li>Выбирайте подходящий размер изображения</li>
                    </ul>
                </div>
                
                <div class="abp-recommendation">
                    <h4>💰 Для экономии</h4>
                    <ul>
                        <li>Используйте DALL-E 2 для массовой генерации</li>
                        <li>Настройте автоматическую генерацию</li>
                        <li>Мониторьте статистику использования</li>
                    </ul>
                </div>
                
                <div class="abp-recommendation">
                    <h4>🔍 Для SEO</h4>
                    <ul>
                        <li>Всегда включайте SEO оптимизацию</li>
                        <li>Проверяйте alt тексты изображений</li>
                        <li>Используйте описательные названия статей</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Тестирование настроек
    $('.abp-test-settings').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="abp-loader"></span>Тестируем...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'abp_test_openai_api',
                nonce: '<?php echo wp_create_nonce('abp_image_generator'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    ABPImageGeneratorAdmin.showNotice('success', 'Настройки работают корректно!');
                } else {
                    ABPImageGeneratorAdmin.showNotice('error', 'Ошибка в настройках: ' + (response.data?.message || 'Неизвестная ошибка'));
                }
            },
            error: function() {
                ABPImageGeneratorAdmin.showNotice('error', 'Ошибка AJAX запроса при тестировании');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Сброс настроек
    $('.abp-reset-settings').on('click', function() {
        if (confirm('Вы уверены, что хотите сбросить все настройки к значениям по умолчанию?')) {
            // Сбрасываем все чекбоксы
            $('input[type="checkbox"]').prop('checked', false);
            
            // Устанавливаем значения по умолчанию
            $('#model').val('dall-e-2');
            $('#size').val('1024x1024');
            $('#quality').val('standard');
            $('#style').val('natural');
            $('#log_level').val('info');
            $('#max_attempts').val('3');
            $('#retry_delay').val('5');
            
            // Включаем основные настройки
            $('input[name="auto_generate"]').prop('checked', true);
            $('input[name="enable_seo_optimization"]').prop('checked', true);
            $('input[name="auto_alt_text"]').prop('checked', true);
            $('input[name="auto_description"]').prop('checked', true);
            
            ABPImageGeneratorAdmin.showNotice('info', 'Настройки сброшены к значениям по умолчанию');
        }
    });

    // Валидация настроек
    $('.abp-settings-form').on('submit', function(e) {
        const maxAttempts = parseInt($('#max_attempts').val());
        const retryDelay = parseInt($('#retry_delay').val());
        
        if (maxAttempts < 1 || maxAttempts > 10) {
            e.preventDefault();
            ABPImageGeneratorAdmin.showNotice('error', 'Максимальное количество попыток должно быть от 1 до 10');
            return false;
        }
        
        if (retryDelay < 1 || retryDelay > 60) {
            e.preventDefault();
            ABPImageGeneratorAdmin.showNotice('error', 'Задержка между попытками должна быть от 1 до 60 секунд');
            return false;
        }
        
        ABPImageGeneratorAdmin.showNotice('info', 'Сохранение настроек...');
    });
});
</script>



