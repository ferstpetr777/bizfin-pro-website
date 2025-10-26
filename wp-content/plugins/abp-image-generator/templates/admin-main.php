<?php
/**
 * Главная страница админки ABP Image Generator
 */

if (!defined('ABSPATH')) exit;

// Получаем статистику
global $wpdb;
$total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'");
$posts_with_images = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts} p 
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
    WHERE p.post_type = 'post' AND p.post_status = 'publish' 
    AND pm.meta_key = '_thumbnail_id' AND pm.meta_value != ''
");
$posts_without_images = $total_posts - $posts_with_images;

// Получаем последние генерации
$recent_generations = $wpdb->get_results("
    SELECT ig.*, p.post_title 
    FROM {$wpdb->prefix}abp_image_generations ig
    LEFT JOIN {$wpdb->posts} p ON ig.post_id = p.ID
    ORDER BY ig.created_at DESC 
    LIMIT 10
", ARRAY_A);
?>

<div class="wrap abp-image-generator">
    <h1>🎨 ABP Image Generator</h1>
    
    <!-- Навигация -->
    <nav class="nav-tab-wrapper abp-nav-tabs">
        <a href="#main" class="nav-tab nav-tab-active abp-nav-tab">Главная</a>
        <a href="#posts" class="nav-tab abp-nav-tab">Посты без изображений</a>
        <a href="#logs" class="nav-tab abp-nav-tab">Логи</a>
        <a href="<?php echo admin_url('admin.php?page=abp-image-generator-settings'); ?>" class="nav-tab">Настройки</a>
        <a href="<?php echo admin_url('admin.php?page=abp-image-generator-stats'); ?>" class="nav-tab">Статистика</a>
    </nav>

    <!-- Главная вкладка -->
    <div id="main" class="abp-tab-content">
        
        <!-- Статистика -->
        <div class="abp-stats-grid">
            <div class="abp-stat-card abp-stat-total-posts">
                <h3>Всего постов</h3>
                <div class="stat-number"><?php echo intval($total_posts); ?></div>
                <div class="stat-label">Опубликованных статей</div>
            </div>
            
            <div class="abp-stat-card abp-stat-posts-with-images">
                <h3>С изображениями</h3>
                <div class="stat-number"><?php echo intval($posts_with_images); ?></div>
                <div class="stat-label">Постов с главными изображениями</div>
            </div>
            
            <div class="abp-stat-card abp-stat-posts-without-images">
                <h3>Без изображений</h3>
                <div class="stat-number"><?php echo intval($posts_without_images); ?></div>
                <div class="stat-label">Постов без главных изображений</div>
            </div>
            
            <div class="abp-stat-card">
                <h3>Процент покрытия</h3>
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

        <!-- Быстрые действия -->
        <div class="abp-quick-actions">
            <h2>🚀 Быстрые действия</h2>
            <div class="abp-actions-grid">
                <div class="abp-action-card">
                    <h3>📊 Обновить статистику</h3>
                    <p>Обновить данные о постах и изображениях</p>
                    <button class="abp-btn abp-refresh-stats">Обновить</button>
                </div>
                
                <div class="abp-action-card">
                    <h3>🔍 Найти посты без изображений</h3>
                    <p>Показать все посты, которым нужны изображения</p>
                    <button class="abp-btn abp-btn-secondary" onclick="$('.abp-nav-tab[href=\"#posts\"]').click()">
                        Показать
                    </button>
                </div>
                
                <div class="abp-action-card">
                    <h3>🖼️ Генерация для буквы</h3>
                    <p>Сгенерировать изображения для постов, начинающихся на указанную букву</p>
                    <div>
                        <input id="abp-letter-input" type="text" placeholder="А" maxlength="1" style="width:60px;text-transform:uppercase;"> 
                        <button class="abp-btn abp-generate-by-letter">Сгенерировать</button>
                        <button class="abp-btn abp-btn-secondary abp-generate-by-letter" data-letter="А">А</button>
                    </div>
                </div>

                <div class="abp-action-card">
                    <h3>🧪 Тест OpenAI API</h3>
                    <p>Проверить работу API генерации изображений</p>
                    <button class="abp-btn abp-btn-secondary abp-test-api">Тестировать</button>
                </div>
                
                <div class="abp-action-card">
                    <h3>📝 Просмотр логов</h3>
                    <p>Посмотреть последние записи в логах</p>
                    <button class="abp-btn abp-btn-secondary abp-view-logs">Просмотреть</button>
                </div>
            </div>
        </div>

        <!-- Интеграция с системой блога -->
        <div class="abp-blog-integration">
            <h3>🔗 Интеграция с системой блога</h3>
            <div class="abp-integration-status abp-alphabet-blog-status">
                <span class="status-icon"></span>
                <span>Alphabet Blog Panel v2</span>
            </div>
            <div class="abp-integration-status abp-yoast-status">
                <span class="status-icon"></span>
                <span>Yoast SEO Integration</span>
            </div>
            <p>Плагин автоматически интегрируется с системой алфавитного блога и SEO-оптимизацией.</p>
        </div>

        <!-- Последние генерации -->
        <?php if (!empty($recent_generations)): ?>
        <div class="abp-recent-generations">
            <h2>📈 Последние генерации</h2>
            <table class="abp-table">
                <thead>
                    <tr>
                        <th>Пост</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_generations as $generation): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($generation['post_title']); ?></strong>
                            <br><small>ID: <?php echo intval($generation['post_id']); ?></small>
                        </td>
                        <td>
                            <span class="abp-status abp-status-<?php echo esc_attr($generation['status']); ?>">
                                <?php echo esc_html($generation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('d.m.Y H:i', strtotime($generation['created_at']))); ?></td>
                        <td>
                            <button class="abp-btn abp-btn-small abp-generate-image" 
                                    data-post-id="<?php echo intval($generation['post_id']); ?>">
                                Перегенерировать
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

    <!-- Вкладка постов без изображений -->
    <div id="posts" class="abp-tab-content" style="display: none;">
        <h2>📝 Посты без изображений</h2>
        
        <!-- Массовые действия -->
        <div class="abp-bulk-actions" style="display: none;">
            <h3>Массовые действия</h3>
            <div class="bulk-controls">
                <span>Выбрано: <strong class="abp-selected-count">0</strong> постов</span>
                <button class="abp-btn abp-btn-success abp-bulk-generate">
                    Сгенерировать изображения для выбранных
                </button>
                <button class="abp-btn abp-btn-secondary" onclick="clearSelection()">
                    Очистить выбор
                </button>
            </div>
        </div>

        <!-- Прогресс массовых операций -->
        <div class="abp-bulk-progress">
            <div class="abp-progress-info">
                <span class="abp-progress-text">Обработано: 0 из 0 (0%)</span>
            </div>
            <div class="abp-progress-bar">
                <div class="abp-progress-fill" style="width: 0%"></div>
            </div>
        </div>

        <!-- Контроль выбора -->
        <div class="abp-selection-controls">
            <label>
                <input type="checkbox" class="abp-select-all"> Выбрать все посты
            </label>
        </div>

        <!-- Список постов -->
        <div class="abp-posts-without-images">
            <div class="abp-notice abp-notice-info">
                Загружаем посты без изображений...
            </div>
        </div>
    </div>

    <!-- Вкладка логов -->
    <div id="logs" class="abp-tab-content" style="display: none;">
        <h2>📋 Логи генерации изображений</h2>
        
        <div class="abp-logs-controls">
            <button class="abp-btn abp-refresh-logs">Обновить логи</button>
            <button class="abp-btn abp-btn-secondary abp-clear-logs">Очистить логи</button>
        </div>

        <div class="abp-logs-container">
            <div class="abp-logs">
                Загружаем логи...
            </div>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Загружаем посты без изображений при переходе на вкладку
    $('.abp-nav-tab[href="#posts"]').on('click', function() {
        if ($('.abp-posts-without-images').html().includes('Загружаем')) {
            ABPImageGeneratorAdmin.loadPostsWithoutImages();
        }
    });

    // Функция очистки выбора
    window.clearSelection = function() {
        $('.abp-post-checkbox').prop('checked', false);
        $('.abp-select-all').prop('checked', false);
        updateBulkActions();
    };

    // Функция загрузки постов без изображений
    window.ABPImageGeneratorAdmin.loadPostsWithoutImages = function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'abp_get_posts_without_images',
                nonce: '<?php echo wp_create_nonce('abp_image_generator'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayPostsWithoutImages(response.data.posts);
                } else {
                    $('.abp-posts-without-images').html('<div class="abp-notice abp-notice-error">Ошибка загрузки постов</div>');
                }
            },
            error: function() {
                $('.abp-posts-without-images').html('<div class="abp-notice abp-notice-error">Ошибка AJAX запроса</div>');
            }
        });
    };

    // Функция отображения постов без изображений
    function displayPostsWithoutImages(posts) {
        let html = '';

        if (posts.length === 0) {
            html = '<div class="abp-notice abp-notice-success">Все посты имеют изображения!</div>';
        } else {
            html = '<div class="abp-posts-grid">';
            
            posts.forEach(function(post) {
                html += `
                    <div class="abp-post-card">
                        <h4>${post.title}</h4>
                        <div class="post-meta">
                            ID: ${post.id} | Дата: ${post.date} | Автор: ${post.author}
                        </div>
                        <div class="post-excerpt">${post.excerpt}</div>
                        <div class="post-actions">
                            <label><input type="checkbox" class="abp-post-checkbox" value="${post.id}"> Выбрать</label>
                            <button class="abp-btn abp-btn-small abp-generate-image" data-post-id="${post.id}">
                                Сгенерировать изображение
                            </button>
                            <button class="abp-btn abp-btn-small abp-btn-secondary abp-check-seo" data-post-id="${post.id}">
                                Проверить SEO
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        }

        $('.abp-posts-without-images').html(html);
    }
});
</script>



