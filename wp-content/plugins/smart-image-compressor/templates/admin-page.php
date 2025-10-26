<?php
// Предотвращаем прямой доступ
if (!defined('ABSPATH')) {
    exit;
}

// Получаем все изображения
$args = array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC'
);

$all_images = get_posts($args);
$uncompressed_images = array();

foreach ($all_images as $image) {
    $compressed = get_post_meta($image->ID, '_sic_compressed', true);
    if (!$compressed) {
        $uncompressed_images[] = $image;
    }
}

$total_images = count($all_images);
$uncompressed_count = count($uncompressed_images);
$compressed_count = $total_images - $uncompressed_count;
?>

<div class="wrap">
    <h1>Smart Image Compressor</h1>
    
    <div class="sic-dashboard">
        <div class="sic-stats">
            <div class="sic-stat-box">
                <h3>Всего изображений</h3>
                <span class="sic-stat-number"><?php echo $total_images; ?></span>
            </div>
            <div class="sic-stat-box">
                <h3>Сжато</h3>
                <span class="sic-stat-number sic-success"><?php echo $compressed_count; ?></span>
            </div>
            <div class="sic-stat-box">
                <h3>Не сжато</h3>
                <span class="sic-stat-number sic-warning"><?php echo $uncompressed_count; ?></span>
            </div>
        </div>
        
        <div class="sic-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#all-images" class="nav-tab nav-tab-active">Все изображения</a>
                <a href="#uncompressed" class="nav-tab">Не сжатые</a>
                <a href="#batch-process" class="nav-tab">Пакетная обработка</a>
            </nav>
            
            <div id="all-images" class="sic-tab-content active">
                <h2>Все изображения в медиатеке</h2>
                <div class="sic-image-grid">
                    <?php foreach ($all_images as $image): ?>
                        <?php
                        $file_path = get_attached_file($image->ID);
                        $file_size = file_exists($file_path) ? round(filesize($file_path) / 1024) : 0;
                        $compressed = get_post_meta($image->ID, '_sic_compressed', true);
                        $thumbnail = wp_get_attachment_image($image->ID, 'thumbnail');
                        ?>
                        <div class="sic-image-item <?php echo $compressed ? 'compressed' : 'uncompressed'; ?>">
                            <div class="sic-image-thumbnail">
                                <?php echo $thumbnail; ?>
                            </div>
                            <div class="sic-image-info">
                                <h4><?php echo esc_html($image->post_title); ?></h4>
                                <p><strong>Размер:</strong> <?php echo $file_size; ?> KB</p>
                                <p><strong>Статус:</strong> 
                                    <?php if ($compressed): ?>
                                        <span class="sic-status compressed">Сжато</span>
                                    <?php else: ?>
                                        <span class="sic-status uncompressed">Не сжато</span>
                                    <?php endif; ?>
                                </p>
                                <?php if (!$compressed): ?>
                                    <button class="button button-primary sic-compress-single" 
                                            data-id="<?php echo $image->ID; ?>">
                                        Сжать
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div id="uncompressed" class="sic-tab-content">
                <h2>Не сжатые изображения</h2>
                <?php if (empty($uncompressed_images)): ?>
                    <div class="notice notice-success">
                        <p>Все изображения сжаты! 🎉</p>
                    </div>
                <?php else: ?>
                    <div class="sic-image-grid">
                        <?php foreach ($uncompressed_images as $image): ?>
                            <?php
                            $file_path = get_attached_file($image->ID);
                            $file_size = file_exists($file_path) ? round(filesize($file_path) / 1024) : 0;
                            $thumbnail = wp_get_attachment_image($image->ID, 'thumbnail');
                            ?>
                            <div class="sic-image-item uncompressed">
                                <div class="sic-image-thumbnail">
                                    <?php echo $thumbnail; ?>
                                </div>
                                <div class="sic-image-info">
                                    <h4><?php echo esc_html($image->post_title); ?></h4>
                                    <p><strong>Размер:</strong> <?php echo $file_size; ?> KB</p>
                                    <button class="button button-primary sic-compress-single" 
                                            data-id="<?php echo $image->ID; ?>">
                                        Сжать
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="batch-process" class="sic-tab-content">
                <h2>Пакетная обработка</h2>
                <div class="sic-batch-controls">
                    <p>Обработать все не сжатые изображения последовательно:</p>
                    <button id="sic-batch-start" class="button button-primary button-large">
                        Начать пакетную обработку (<?php echo $uncompressed_count; ?> изображений)
                    </button>
                    <button id="sic-batch-stop" class="button button-secondary" style="display: none;">
                        Остановить
                    </button>
                </div>
                
                <div id="sic-batch-progress" style="display: none;">
                    <h3>Прогресс обработки</h3>
                    <div class="sic-progress-bar">
                        <div class="sic-progress-fill"></div>
                    </div>
                    <p class="sic-progress-text">Обработано: <span id="sic-processed-count">0</span> из <span id="sic-total-count"><?php echo $uncompressed_count; ?></span></p>
                    <div id="sic-batch-results"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Переключение вкладок
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.sic-tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Сжатие одного изображения
    $('.sic-compress-single').on('click', function() {
        var button = $(this);
        var imageId = button.data('id');
        var imageItem = button.closest('.sic-image-item');
        
        button.prop('disabled', true).text('Сжатие...');
        
        $.ajax({
            url: sic_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sic_compress_image',
                attachment_id: imageId,
                nonce: sic_ajax.nonce
            },
            success: function(response) {
                if (response.status === 'success') {
                    imageItem.removeClass('uncompressed').addClass('compressed');
                    imageItem.find('.sic-status').removeClass('uncompressed').addClass('compressed').text('Сжато');
                    button.remove();
                    
                    // Обновляем статистику
                    updateStats();
                } else {
                    alert('Ошибка: ' + response.message);
                    button.prop('disabled', false).text('Сжать');
                }
            },
            error: function() {
                alert('Произошла ошибка при сжатии изображения');
                button.prop('disabled', false).text('Сжать');
            }
        });
    });
    
    // Пакетная обработка
    var batchProcessing = false;
    var processedCount = 0;
    var totalCount = <?php echo $uncompressed_count; ?>;
    
    $('#sic-batch-start').on('click', function() {
        if (batchProcessing) return;
        
        batchProcessing = true;
        processedCount = 0;
        
        $(this).hide();
        $('#sic-batch-stop').show();
        $('#sic-batch-progress').show();
        
        processBatch();
    });
    
    $('#sic-batch-stop').on('click', function() {
        batchProcessing = false;
        $(this).hide();
        $('#sic-batch-start').show();
    });
    
    function processBatch() {
        if (!batchProcessing) return;
        
        $.ajax({
            url: sic_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sic_batch_compress',
                offset: processedCount,
                nonce: sic_ajax.nonce
            },
            success: function(response) {
                processedCount += response.processed;
                
                // Обновляем прогресс
                var percentage = (processedCount / totalCount) * 100;
                $('.sic-progress-fill').css('width', percentage + '%');
                $('#sic-processed-count').text(processedCount);
                
                // Показываем результаты
                var resultsHtml = '';
                response.results.forEach(function(result) {
                    resultsHtml += '<div class="sic-batch-result">';
                    resultsHtml += '<strong>' + result.title + '</strong>: ';
                    if (result.result.status === 'success') {
                        resultsHtml += 'Сжато с ' + result.result.original_size + 'KB до ' + result.result.new_size + 'KB';
                    } else {
                        resultsHtml += 'Ошибка: ' + result.result.message;
                    }
                    resultsHtml += '</div>';
                });
                $('#sic-batch-results').append(resultsHtml);
                
                if (response.has_more && batchProcessing) {
                    setTimeout(processBatch, 1000); // Пауза между пакетами
                } else {
                    // Завершено
                    batchProcessing = false;
                    $('#sic-batch-stop').hide();
                    $('#sic-batch-start').show();
                    updateStats();
                }
            },
            error: function() {
                alert('Ошибка при пакетной обработке');
                batchProcessing = false;
                $('#sic-batch-stop').hide();
                $('#sic-batch-start').show();
            }
        });
    }
    
    function updateStats() {
        // Обновляем статистику на странице
        location.reload();
    }
});
</script>

