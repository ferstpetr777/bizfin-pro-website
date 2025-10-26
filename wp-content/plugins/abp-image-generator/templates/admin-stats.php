<?php
/**
 * Страница статистики ABP Image Generator
 */

if (!defined('ABSPATH')) exit;

// Получаем статистику из базы данных
global $wpdb;

// Общая статистика
$total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'");
$posts_with_images = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts} p 
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
    WHERE p.post_type = 'post' AND p.post_status = 'publish' 
    AND pm.meta_key = '_thumbnail_id' AND pm.meta_value != ''
");
$posts_without_images = $total_posts - $posts_with_images;

// Статистика генераций
$generation_stats = $wpdb->get_results("
    SELECT status, COUNT(*) as count 
    FROM {$wpdb->prefix}abp_image_generations 
    GROUP BY status
", ARRAY_A);

// Статистика по дням за последние 30 дней
$daily_stats = $wpdb->get_results("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_attempts,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
    FROM {$wpdb->prefix}abp_image_generations 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
", ARRAY_A);

// Статистика по моделям
$model_stats = $wpdb->get_results("
    SELECT 
        SUBSTRING_INDEX(SUBSTRING_INDEX(prompt, 'model:', -1), ' ', 1) as model_type,
        COUNT(*) as count,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
    FROM {$wpdb->prefix}abp_image_generations 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY model_type
", ARRAY_A);

// Топ ошибок
$error_stats = $wpdb->get_results("
    SELECT 
        error_message,
        COUNT(*) as count
    FROM {$wpdb->prefix}abp_image_generations 
    WHERE status = 'error' 
    AND error_message IS NOT NULL 
    AND error_message != ''
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY error_message
    ORDER BY count DESC
    LIMIT 10
", ARRAY_A);

// Последние генерации
$recent_generations = $wpdb->get_results("
    SELECT 
        ig.*,
        p.post_title,
        p.post_date
    FROM {$wpdb->prefix}abp_image_generations ig
    LEFT JOIN {$wpdb->posts} p ON ig.post_id = p.ID
    ORDER BY ig.created_at DESC
    LIMIT 20
", ARRAY_A);
?>

<div class="wrap abp-image-generator">
    <h1>📊 Статистика ABP Image Generator</h1>
    
    <!-- Навигация -->
    <nav class="nav-tab-wrapper abp-nav-tabs">
        <a href="#overview" class="nav-tab nav-tab-active abp-nav-tab">Обзор</a>
        <a href="#daily" class="nav-tab abp-nav-tab">По дням</a>
        <a href="#models" class="nav-tab abp-nav-tab">Модели</a>
        <a href="#errors" class="nav-tab abp-nav-tab">Ошибки</a>
        <a href="#recent" class="nav-tab abp-nav-tab">Последние</a>
    </nav>

    <!-- Общая статистика -->
    <div id="overview" class="abp-tab-content">
        
        <!-- Основные метрики -->
        <div class="abp-stats-grid">
            <div class="abp-stat-card">
                <h3>Всего постов</h3>
                <div class="stat-number"><?php echo intval($total_posts); ?></div>
                <div class="stat-label">Опубликованных статей</div>
            </div>
            
            <div class="abp-stat-card">
                <h3>С изображениями</h3>
                <div class="stat-number"><?php echo intval($posts_with_images); ?></div>
                <div class="stat-label">Постов с главными изображениями</div>
            </div>
            
            <div class="abp-stat-card">
                <h3>Без изображений</h3>
                <div class="stat-number"><?php echo intval($posts_without_images); ?></div>
                <div class="stat-label">Постов без главных изображений</div>
            </div>
            
            <div class="abp-stat-card">
                <h3>Покрытие</h3>
                <div class="stat-number">
                    <?php 
                    $percentage = $total_posts > 0 ? round(($posts_with_images / $total_posts) * 100, 1) : 0;
                    echo $percentage . '%';
                    ?>
                </div>
                <div class="abp-progress-bar">
                    <div class="abp-progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Статистика генераций -->
        <?php if (!empty($generation_stats)): ?>
        <div class="abp-generation-overview">
            <h2>📈 Статистика генераций</h2>
            <table class="abp-table">
                <thead>
                    <tr>
                        <th>Статус</th>
                        <th>Количество</th>
                        <th>Процент</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_generations = array_sum(array_column($generation_stats, 'count'));
                    foreach ($generation_stats as $stat): 
                        $percentage = $total_generations > 0 ? round(($stat['count'] / $total_generations) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td>
                            <span class="abp-status abp-status-<?php echo esc_attr($stat['status']); ?>">
                                <?php echo esc_html($stat['status']); ?>
                            </span>
                        </td>
                        <td><?php echo intval($stat['count']); ?></td>
                        <td><?php echo $percentage; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Быстрые действия -->
        <div class="abp-quick-stats-actions">
            <h2>🚀 Быстрые действия</h2>
            <div class="abp-actions-grid">
                <button class="abp-btn abp-refresh-stats">🔄 Обновить статистику</button>
                <button class="abp-btn abp-btn-secondary abp-export-stats">📊 Экспорт данных</button>
                <button class="abp-btn abp-btn-secondary abp-generate-missing-images">🎨 Генерировать недостающие</button>
            </div>
        </div>

    </div>

    <!-- Статистика по дням -->
    <div id="daily" class="abp-tab-content" style="display: none;">
        <h2>📅 Статистика по дням (последние 30 дней)</h2>
        
        <?php if (!empty($daily_stats)): ?>
        <table class="abp-table">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Всего попыток</th>
                    <th>Успешных</th>
                    <th>Ошибок</th>
                    <th>Успешность</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_stats as $stat): ?>
                <?php 
                $success_rate = $stat['total_attempts'] > 0 ? round(($stat['successful'] / $stat['total_attempts']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo esc_html(date('d.m.Y', strtotime($stat['date']))); ?></td>
                    <td><?php echo intval($stat['total_attempts']); ?></td>
                    <td>
                        <span class="abp-status abp-status-success">
                            <?php echo intval($stat['successful']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="abp-status abp-status-error">
                            <?php echo intval($stat['errors']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="abp-success-rate">
                            <?php echo $success_rate; ?>%
                            <div class="abp-progress-bar" style="width: 100px; height: 10px; margin-top: 5px;">
                                <div class="abp-progress-fill" style="width: <?php echo $success_rate; ?>%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="abp-notice abp-notice-info">
            Нет данных о генерациях за последние 30 дней.
        </div>
        <?php endif; ?>
    </div>

    <!-- Статистика по моделям -->
    <div id="models" class="abp-tab-content" style="display: none;">
        <h2>🤖 Статистика по моделям</h2>
        
        <?php if (!empty($model_stats)): ?>
        <div class="abp-model-stats">
            <?php foreach ($model_stats as $model): ?>
            <div class="abp-model-card">
                <h3><?php echo esc_html($model['model_type'] ?: 'Неизвестная модель'); ?></h3>
                <div class="model-stats">
                    <div class="stat-item">
                        <span class="stat-label">Всего попыток:</span>
                        <span class="stat-value"><?php echo intval($model['count']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Успешных:</span>
                        <span class="stat-value"><?php echo intval($model['successful']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Успешность:</span>
                        <span class="stat-value">
                            <?php 
                            $success_rate = $model['count'] > 0 ? round(($model['successful'] / $model['count']) * 100, 1) : 0;
                            echo $success_rate . '%';
                            ?>
                        </span>
                    </div>
                </div>
                <div class="abp-progress-bar">
                    <div class="abp-progress-fill" style="width: <?php echo $success_rate; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="abp-notice abp-notice-info">
            Нет данных о моделях за последние 30 дней.
        </div>
        <?php endif; ?>
    </div>

    <!-- Статистика ошибок -->
    <div id="errors" class="abp-tab-content" style="display: none;">
        <h2>❌ Топ ошибок</h2>
        
        <?php if (!empty($error_stats)): ?>
        <table class="abp-table">
            <thead>
                <tr>
                    <th>Ошибка</th>
                    <th>Количество</th>
                    <th>Процент</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_errors = array_sum(array_column($error_stats, 'count'));
                foreach ($error_stats as $error): 
                    $percentage = $total_errors > 0 ? round(($error['count'] / $total_errors) * 100, 1) : 0;
                ?>
                <tr>
                    <td>
                        <div class="error-message">
                            <?php echo esc_html($error['error_message']); ?>
                        </div>
                    </td>
                    <td><?php echo intval($error['count']); ?></td>
                    <td>
                        <?php echo $percentage; ?>%
                        <div class="abp-progress-bar" style="width: 100px; height: 10px; margin-top: 5px;">
                            <div class="abp-progress-fill" style="width: <?php echo $percentage; ?>%; background: #dc3232;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="abp-notice abp-notice-success">
            Отлично! Ошибок за последние 30 дней не обнаружено.
        </div>
        <?php endif; ?>
    </div>

    <!-- Последние генерации -->
    <div id="recent" class="abp-tab-content" style="display: none;">
        <h2>🕒 Последние генерации</h2>
        
        <?php if (!empty($recent_generations)): ?>
        <table class="abp-table">
            <thead>
                <tr>
                    <th>Пост</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Промпт</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_generations as $generation): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($generation['post_title'] ?: 'Неизвестный пост'); ?></strong>
                        <br><small>ID: <?php echo intval($generation['post_id']); ?></small>
                    </td>
                    <td>
                        <span class="abp-status abp-status-<?php echo esc_attr($generation['status']); ?>">
                            <?php echo esc_html($generation['status']); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html(date('d.m.Y H:i', strtotime($generation['created_at']))); ?></td>
                    <td>
                        <div class="prompt-preview">
                            <?php echo esc_html(wp_trim_words($generation['prompt'], 10)); ?>
                        </div>
                    </td>
                    <td>
                        <button class="abp-btn abp-btn-small abp-generate-image" 
                                data-post-id="<?php echo intval($generation['post_id']); ?>">
                            Перегенерировать
                        </button>
                        <?php if ($generation['status'] === 'error' && $generation['error_message']): ?>
                        <button class="abp-btn abp-btn-small abp-btn-secondary abp-view-error" 
                                data-error="<?php echo esc_attr($generation['error_message']); ?>">
                            Просмотр ошибки
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="abp-notice abp-notice-info">
            Нет записей о генерациях.
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Инициализация табов
    $('.abp-nav-tab').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('href');
        
        // Обновляем активный таб
        $('.abp-nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Показываем соответствующий контент
        $('.abp-tab-content').hide();
        $(target).show();
    });

    // Обновление статистики
    $('.abp-refresh-stats').on('click', function() {
        location.reload();
    });

    // Экспорт статистики
    $('.abp-export-stats').on('click', function() {
        const csvContent = generateCSV();
        downloadCSV(csvContent, 'abp-image-generator-stats.csv');
    });

    // Генерация недостающих изображений
    $('.abp-generate-missing-images').on('click', function() {
        if (confirm('Вы уверены, что хотите сгенерировать изображения для всех постов без изображений?')) {
            // Переходим на главную страницу для массовой генерации
            window.location.href = '<?php echo admin_url('admin.php?page=abp-image-generator'); ?>';
        }
    });

    // Просмотр ошибки
    $(document).on('click', '.abp-view-error', function() {
        const error = $(this).data('error');
        showErrorModal(error);
    });

    // Функция генерации CSV
    function generateCSV() {
        let csv = 'Дата,Всего попыток,Успешных,Ошибок,Успешность\n';
        
        <?php if (!empty($daily_stats)): ?>
        <?php foreach ($daily_stats as $stat): ?>
        <?php $success_rate = $stat['total_attempts'] > 0 ? round(($stat['successful'] / $stat['total_attempts']) * 100, 1) : 0; ?>
        csv += '<?php echo date('d.m.Y', strtotime($stat['date'])); ?>,<?php echo intval($stat['total_attempts']); ?>,<?php echo intval($stat['successful']); ?>,<?php echo intval($stat['errors']); ?>,<?php echo $success_rate; ?>%\n';
        <?php endforeach; ?>
        <?php endif; ?>
        
        return csv;
    }

    // Функция скачивания CSV
    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    // Функция показа модального окна с ошибкой
    function showErrorModal(error) {
        const modal = $(`
            <div class="abp-modal" id="error-modal">
                <div class="abp-modal-content">
                    <div class="abp-modal-header">
                        <h3>Детали ошибки</h3>
                        <button class="abp-modal-close">&times;</button>
                    </div>
                    <div class="abp-error-details">
                        <pre>${error}</pre>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        $('#error-modal').show();
    }

    // Закрытие модальных окон
    $(document).on('click', '.abp-modal-close, .abp-modal', function(e) {
        if (e.target === this) {
            $(this).closest('.abp-modal').hide();
        }
    });
});
</script>



