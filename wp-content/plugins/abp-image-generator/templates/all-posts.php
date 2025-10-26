<?php
/**
 * Шаблон страницы всех статей
 */

if (!defined('ABSPATH')) exit;

// Получаем данные для текущей страницы
$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$per_page = 20;

// Получаем статьи
$abp_generator = new ABP_Image_Generator();
$posts_data = $abp_generator->get_all_posts_with_images($current_page, $per_page, $search_query);
?>

<div class="wrap">
    <h1>🖼️ Все статьи - Управление изображениями</h1>
    
    <div class="abp-all-posts-header">
        <div class="abp-stats-summary">
            <div class="abp-stat-item">
                <span class="abp-stat-number"><?php echo $posts_data['total']; ?></span>
                <span class="abp-stat-label">Всего статей</span>
            </div>
            <div class="abp-stat-item">
                <span class="abp-stat-number"><?php echo count(array_filter($posts_data['posts'], function($post) { return $post['has_image']; })); ?></span>
                <span class="abp-stat-label">С изображениями</span>
            </div>
            <div class="abp-stat-item">
                <span class="abp-stat-number"><?php echo count(array_filter($posts_data['posts'], function($post) { return !$post['has_image']; })); ?></span>
                <span class="abp-stat-label">Без изображений</span>
            </div>
        </div>
        
        <div class="abp-search-box">
            <form method="get" action="">
                <input type="hidden" name="page" value="abp-image-generator-all-posts">
                <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Поиск по заголовку статьи..." class="regular-text">
                <input type="submit" class="button" value="Найти">
                <?php if (!empty($search_query)): ?>
                    <a href="?page=abp-image-generator-all-posts" class="button">Очистить</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="abp-posts-table-container">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 60px;">Изображение</th>
                    <th style="min-width: 200px;">Заголовок</th>
                    <th style="width: 80px;">Дата</th>
                    <th style="width: 50px;">Буква</th>
                    <th style="width: 120px;">AI Категория</th>
                    <th style="width: 60px;" title="Медиатека">📁</th>
                    <th style="width: 60px;" title="Статья">📄</th>
                    <th style="width: 60px;" title="Миниатюра">🖼️</th>
                    <th style="width: 60px;" title="Файловая система">💾</th>
                    <th style="width: 80px;" title="Прогресс регенерации">⏱️</th>
                    <th style="width: 120px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts_data['posts'])): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 20px;">
                            <?php if (!empty($search_query)): ?>
                                Статьи по запросу "<?php echo esc_html($search_query); ?>" не найдены.
                            <?php else: ?>
                                Статьи не найдены.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts_data['posts'] as $post): ?>
                        <tr data-post-id="<?php echo $post['id']; ?>">
                            <td><?php echo $post['id']; ?></td>
                            <td>
                                <?php if ($post['has_image']): ?>
                                    <img src="<?php echo esc_url($post['thumbnail_url']); ?>" 
                                         alt="<?php echo esc_attr($post['title']); ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div class="abp-no-image" style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">
                                        Нет изображения
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url($post['edit_url']); ?>" target="_blank">
                                        <?php echo esc_html($post['title']); ?>
                                    </a>
                                </strong>
                                <br>
                                <small>
                                    <a href="<?php echo esc_url($post['url']); ?>" target="_blank">Просмотреть</a>
                                </small>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($post['date'])); ?></td>
                            <td>
                                <?php if (!empty($post['first_letter'])): ?>
                                    <span class="abp-letter-badge"><?php echo esc_html($post['first_letter']); ?></span>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($post['ai_category'])): ?>
                                    <span class="abp-category-badge"><?php echo esc_html($post['ai_category']); ?></span>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($post['diagnostics']): ?>
                                    <?php echo $this->render_diagnostic_status($post['diagnostics']['media_library']); ?>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($post['diagnostics']): ?>
                                    <?php echo $this->render_diagnostic_status($post['diagnostics']['article']); ?>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($post['diagnostics']): ?>
                                    <?php echo $this->render_diagnostic_status($post['diagnostics']['thumbnail']); ?>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($post['diagnostics']): ?>
                                    <?php echo $this->render_diagnostic_status($post['diagnostics']['file_system']); ?>
                                <?php else: ?>
                                    <span class="abp-missing-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="abp-progress-column">
                                <div class="abp-progress-indicator" data-post-id="<?php echo $post['id']; ?>">
                                    <span class="abp-progress-text">—</span>
                                </div>
                            </td>
                            <td>
                                <button class="button button-primary abp-regenerate-btn" 
                                        data-post-id="<?php echo $post['id']; ?>"
                                        data-post-title="<?php echo esc_attr($post['title']); ?>">
                                    <span class="dashicons dashicons-update"></span>
                                    Регенерировать
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Пагинация -->
    <?php if ($posts_data['total_pages'] > 1): ?>
        <div class="abp-pagination">
            <?php
            $base_url = admin_url('admin.php?page=abp-image-generator-all-posts');
            if (!empty($search_query)) {
                $base_url .= '&s=' . urlencode($search_query);
            }
            
            echo paginate_links([
                'base' => $base_url . '&paged=%#%',
                'format' => '',
                'current' => $current_page,
                'total' => $posts_data['total_pages'],
                'prev_text' => '&laquo; Предыдущая',
                'next_text' => 'Следующая &raquo;'
            ]);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Модальное окно для подтверждения -->
    <div id="abp-regenerate-modal" class="abp-modal" style="display: none;">
        <div class="abp-modal-content">
            <div class="abp-modal-header">
                <h3>Регенерировать изображение</h3>
                <span class="abp-modal-close">&times;</span>
            </div>
            <div class="abp-modal-body">
                <p>Вы уверены, что хотите регенерировать изображение для статьи:</p>
                <p><strong id="abp-modal-post-title"></strong></p>
                <div class="abp-modal-warning">
                    <span class="dashicons dashicons-warning"></span>
                    <strong>Внимание:</strong> Текущее изображение будет удалено и заменено новым.
                </div>
            </div>
            <div class="abp-modal-footer">
                <button class="button" id="abp-modal-cancel">Отмена</button>
                <button class="button button-primary" id="abp-modal-confirm">
                    <span class="dashicons dashicons-update"></span>
                    Регенерировать
                </button>
            </div>
        </div>
    </div>
    
    <!-- Индикатор загрузки -->
    <div id="abp-loading-overlay" style="display: none;">
        <div class="abp-loading-spinner">
            <div class="abp-spinner"></div>
            <p>Генерация изображения...</p>
        </div>
    </div>
</div>

<style>
.abp-all-posts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.abp-stats-summary {
    display: flex;
    gap: 30px;
}

.abp-stat-item {
    text-align: center;
}

.abp-stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.abp-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.abp-search-box form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.abp-posts-table-container {
    margin: 20px 0;
}

.abp-letter-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #0073aa;
    color: white;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
}

.abp-category-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #28a745;
    color: white;
    border-radius: 4px;
    font-size: 11px;
}

.abp-missing-data {
    color: #999;
    font-style: italic;
}

.abp-regenerate-btn {
    position: relative;
}

.abp-regenerate-btn .dashicons {
    margin-right: 5px;
}

.abp-pagination {
    text-align: center;
    margin: 20px 0;
}

/* Модальное окно */
.abp-modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.abp-modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.abp-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.abp-modal-header h3 {
    margin: 0;
}

.abp-modal-close {
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.abp-modal-close:hover {
    color: #333;
}

.abp-modal-body {
    padding: 20px;
}

.abp-modal-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 10px;
    border-radius: 4px;
    margin-top: 15px;
    color: #856404;
}

.abp-modal-warning .dashicons {
    margin-right: 5px;
    color: #f39c12;
}

.abp-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.abp-modal-footer .button {
    margin-left: 10px;
}

/* Индикатор загрузки */
.abp-loading-overlay {
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
}

.abp-loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
}

.abp-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0073aa;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: abp-spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes abp-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes abp-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes abp-fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.abp-loading-spinner p {
    margin: 0;
    font-size: 16px;
}

.abp-diagnostics-panel {
    animation: abp-fadeIn 0.5s ease-out;
}

.abp-image-updating {
    position: relative;
}

.abp-update-success {
    position: absolute !important;
    top: -5px !important;
    right: -5px !important;
    background: #28a745 !important;
    color: white !important;
    border-radius: 50% !important;
    width: 20px !important;
    height: 20px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12px !important;
    animation: abp-pulse 2s !important;
    z-index: 10;
}

/* Стили для диагностических статусов */
.abp-diagnostic-status {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.abp-diagnostic-status:hover {
    background: rgba(0,0,0,0.05);
    transform: scale(1.1);
}

.abp-diagnostic-success {
    background: rgba(40, 167, 69, 0.1);
}

.abp-diagnostic-error {
    background: rgba(220, 53, 69, 0.1);
}

.abp-diagnostic-status .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Улучшенная адаптивность для таблицы */
.abp-posts-table-container {
    overflow-x: auto;
    max-width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.wp-list-table {
    min-width: 1000px;
    table-layout: fixed;
}

.wp-list-table th,
.wp-list-table td {
    padding: 8px 6px;
    font-size: 13px;
    line-height: 1.4;
}

/* Стили для колонки прогресса */
.abp-progress-column {
    text-align: center;
    vertical-align: middle;
}

.abp-progress-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
}

.abp-progress-text {
    font-size: 12px;
    font-weight: bold;
    color: #666;
}

.abp-progress-active {
    color: #0073aa;
    animation: abp-pulse 1s infinite;
}

.abp-progress-success {
    color: #28a745;
}

.abp-progress-error {
    color: #dc3545;
}

/* Компактные стили для диагностических статусов */
.abp-diagnostic-status {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.abp-diagnostic-status:hover {
    background: rgba(0,0,0,0.05);
    transform: scale(1.1);
}

.abp-diagnostic-status .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Компактные бейджи */
.abp-letter-badge {
    display: inline-block;
    padding: 2px 6px;
    background: #0073aa;
    color: white;
    border-radius: 3px;
    font-weight: bold;
    font-size: 11px;
}

.abp-category-badge {
    display: inline-block;
    padding: 2px 6px;
    background: #28a745;
    color: white;
    border-radius: 3px;
    font-size: 10px;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Компактная кнопка регенерации */
.abp-regenerate-btn {
    padding: 4px 8px;
    font-size: 12px;
    line-height: 1.2;
}

.abp-regenerate-btn .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    margin-right: 3px;
}

/* Адаптивность */
@media (max-width: 1400px) {
    .wp-list-table {
        min-width: 900px;
    }
    
    .wp-list-table th,
    .wp-list-table td {
        padding: 6px 4px;
        font-size: 12px;
    }
}

@media (max-width: 1200px) {
    .wp-list-table {
        min-width: 800px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentPostId = null;
    
    // Открытие модального окна
    $('.abp-regenerate-btn').on('click', function() {
        currentPostId = $(this).data('post-id');
        const postTitle = $(this).data('post-title');
        
        $('#abp-modal-post-title').text(postTitle);
        $('#abp-regenerate-modal').show();
    });
    
    // Закрытие модального окна
    $('.abp-modal-close, #abp-modal-cancel').on('click', function() {
        $('#abp-regenerate-modal').hide();
        currentPostId = null;
    });
    
    // Подтверждение регенерации
    $('#abp-modal-confirm').on('click', function() {
        if (!currentPostId) return;
        
        $('#abp-regenerate-modal').hide();
        $('#abp-loading-overlay').show();
        
        // Запускаем отслеживание прогресса
        startProgressTracking(currentPostId);
        
        // Сохраняем ID поста для использования в колбэках
        const postId = currentPostId;
        
        // Отправляем AJAX запрос
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'abp_regenerate_single_image',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce(ABP_Image_Generator::NONCE_ACTION); ?>'
            },
            success: function(response) {
                $('#abp-loading-overlay').hide();
                stopProgressTracking(postId);
                
                if (response.success) {
                    // Показываем уведомление об успехе
                    showNotice('success', response.data.message);
                    
                    // Показываем диагностику
                    showDiagnostics(response.data.diagnostics);
                    
                    // Обновляем изображение в таблице
                    updateTableRow(postId, response.data);
                    
                } else {
                    showNotice('error', response.data || 'Ошибка регенерации изображения');
                    setProgressError(postId);
                }
            },
            error: function() {
                $('#abp-loading-overlay').hide();
                stopProgressTracking(postId);
                setProgressError(postId);
                showNotice('error', 'Ошибка соединения с сервером');
            }
        });
        
        currentPostId = null;
    });
    
    // Функция обновления строки таблицы
    function updateTableRow(postId, data) {
        const row = $(`tr[data-post-id="${postId}"]`);
        const imageCell = row.find('td:nth-child(2)');
        
        // Обновляем изображение с анимацией
        imageCell.html(`
            <div class="abp-image-updating">
                <img src="${data.thumbnail_url}" 
                     alt="${postId}" 
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; opacity: 0; transition: opacity 0.5s;">
            </div>
        `);
        
        // Плавное появление изображения
        setTimeout(function() {
            imageCell.find('img').css('opacity', '1');
        }, 100);
        
        // Добавляем индикатор успешного обновления
        imageCell.append(`
            <div class="abp-update-success" style="position: absolute; top: -5px; right: -5px; background: #28a745; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; animation: abp-pulse 2s;">
                ✓
            </div>
        `);
        
        // Убираем индикатор через 3 секунды
        setTimeout(function() {
            imageCell.find('.abp-update-success').fadeOut();
        }, 3000);
        
        // Обновляем диагностические колонки
        updateDiagnosticColumns(row, data.diagnostics);
    }
    
    // Функция обновления диагностических колонок
    function updateDiagnosticColumns(row, diagnostics) {
        // Медиатека (колонка 7)
        const mediaCell = row.find('td:nth-child(7)');
        mediaCell.html(renderDiagnosticStatus(diagnostics.media_library));
        
        // Статья (колонка 8)
        const articleCell = row.find('td:nth-child(8)');
        articleCell.html(renderDiagnosticStatus(diagnostics.article));
        
        // Миниатюра (колонка 9)
        const thumbnailCell = row.find('td:nth-child(9)');
        thumbnailCell.html(renderDiagnosticStatus(diagnostics.thumbnail));
        
        // Файловая система (колонка 10)
        const fileSystemCell = row.find('td:nth-child(10)');
        fileSystemCell.html(renderDiagnosticStatus(diagnostics.file_system));
    }
    
    // Функции для отслеживания прогресса
    let progressTimers = {};
    
    function startProgressTracking(postId) {
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        progressText.removeClass('abp-progress-success abp-progress-error').addClass('abp-progress-active');
        progressText.text('0с');
        
        let seconds = 0;
        progressTimers[postId] = setInterval(function() {
            seconds++;
            progressText.text(seconds + 'с');
        }, 1000);
    }
    
    function stopProgressTracking(postId) {
        if (progressTimers[postId]) {
            clearInterval(progressTimers[postId]);
            delete progressTimers[postId];
        }
        
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        progressText.removeClass('abp-progress-active').addClass('abp-progress-success');
        progressText.text('✓');
        
        // Через 3 секунды возвращаем к исходному состоянию
        setTimeout(function() {
            progressText.removeClass('abp-progress-success').text('—');
        }, 3000);
    }
    
    function setProgressError(postId) {
        if (progressTimers[postId]) {
            clearInterval(progressTimers[postId]);
            delete progressTimers[postId];
        }
        
        const progressIndicator = $(`.abp-progress-indicator[data-post-id="${postId}"]`);
        const progressText = progressIndicator.find('.abp-progress-text');
        
        progressText.removeClass('abp-progress-active').addClass('abp-progress-error');
        progressText.text('✗');
        
        // Через 5 секунд возвращаем к исходному состоянию
        setTimeout(function() {
            progressText.removeClass('abp-progress-error').text('—');
        }, 5000);
    }
    
    // Функция рендеринга статуса диагностики
    function renderDiagnosticStatus(diagnostic) {
        const statusClass = diagnostic.status === 'success' ? 'abp-diagnostic-success' : 'abp-diagnostic-error';
        const icon = diagnostic.status === 'success' ? 'dashicons-yes' : 'dashicons-no';
        const color = diagnostic.status === 'success' ? '#28a745' : '#dc3545';
        
        return `
            <div class="abp-diagnostic-status ${statusClass}" title="${diagnostic.message}">
                <span class="dashicons ${icon}" style="color: ${color};"></span>
            </div>
        `;
    }
    
    // Функция показа диагностики
    function showDiagnostics(diagnostics) {
        const diagnosticHtml = `
            <div class="abp-diagnostics-panel" style="margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #0073aa;">
                <h4 style="margin: 0 0 15px 0; color: #0073aa;">
                    <span class="dashicons dashicons-yes-alt"></span>
                    Диагностика регенерации изображения
                </h4>
                <div class="abp-diagnostics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                    ${generateDiagnosticItem('Медиатека', diagnostics.media_library)}
                    ${generateDiagnosticItem('Статья', diagnostics.article)}
                    ${generateDiagnosticItem('Миниатюра', diagnostics.thumbnail)}
                    ${generateDiagnosticItem('Файловая система', diagnostics.file_system)}
                </div>
            </div>
        `;
        
        $('.wrap h1').after(diagnosticHtml);
        
        // Автоматическое скрытие через 10 секунд
        setTimeout(function() {
            $('.abp-diagnostics-panel').fadeOut();
        }, 10000);
    }
    
    // Функция генерации элемента диагностики
    function generateDiagnosticItem(title, diagnostic) {
        const statusClass = diagnostic.status === 'success' ? 'abp-success' : 'abp-error';
        const icon = diagnostic.status === 'success' ? 'dashicons-yes' : 'dashicons-no';
        
        return `
            <div class="abp-diagnostic-item" style="padding: 10px; background: white; border-radius: 4px; border: 1px solid #ddd;">
                <div class="abp-diagnostic-header" style="display: flex; align-items: center; margin-bottom: 8px;">
                    <span class="dashicons ${icon} ${statusClass}" style="margin-right: 8px; color: ${diagnostic.status === 'success' ? '#28a745' : '#dc3545'};"></span>
                    <strong>${title}</strong>
                </div>
                <div class="abp-diagnostic-message" style="font-size: 13px; color: #666;">
                    ${diagnostic.message}
                </div>
            </div>
        `;
    }
    
    // Функция показа уведомлений
    function showNotice(type, message) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
        $('.wrap h1').after(notice);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
        
        // Обработка кнопки закрытия
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut();
        });
    }
});
</script>
