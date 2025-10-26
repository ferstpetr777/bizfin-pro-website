/**
 * Smart Image Compressor - Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Инициализация при загрузке документа
    $(document).ready(function() {
        initTabs();
        initSingleCompression();
        initBatchProcessing();
        initSettings();
    });
    
    /**
     * Инициализация вкладок
     */
    function initTabs() {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href');
            
            // Обновляем активную вкладку
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Показываем соответствующий контент
            $('.sic-tab-content').removeClass('active');
            $(target).addClass('active');
        });
    }
    
    /**
     * Инициализация сжатия одного изображения
     */
    function initSingleCompression() {
        $(document).on('click', '.sic-compress-single', function() {
            var button = $(this);
            var imageId = button.data('id');
            var imageItem = button.closest('.sic-image-item');
            
            // Блокируем кнопку и показываем состояние загрузки
            button.prop('disabled', true).text('Сжатие...');
            imageItem.addClass('sic-loading');
            
            // Отправляем AJAX запрос
            $.ajax({
                url: sic_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sic_compress_image',
                    attachment_id: imageId,
                    nonce: sic_ajax.nonce
                },
                success: function(response) {
                    handleCompressionResponse(response, imageItem, button);
                },
                error: function(xhr, status, error) {
                    handleCompressionError(error, button);
                }
            });
        });
    }
    
    /**
     * Обработка ответа сжатия
     */
    function handleCompressionResponse(response, imageItem, button) {
        imageItem.removeClass('sic-loading');
        
        if (response.status === 'success') {
            // Обновляем статус изображения
            imageItem.removeClass('uncompressed').addClass('compressed');
            
            var statusElement = imageItem.find('.sic-status');
            statusElement.removeClass('uncompressed').addClass('compressed').text('Сжато');
            
            // Показываем информацию о сжатии
            var infoHtml = '<div class="sic-message success">';
            infoHtml += 'Изображение сжато с ' + response.original_size + 'KB до ' + response.new_size + 'KB ';
            infoHtml += '(экономия: ' + response.savings + 'KB)';
            infoHtml += '</div>';
            
            imageItem.find('.sic-image-info').append(infoHtml);
            
            // Удаляем кнопку сжатия
            button.remove();
            
            // Обновляем статистику
            updateStats();
            
            // Показываем уведомление
            showNotification('Изображение успешно сжато!', 'success');
            
        } else if (response.status === 'skipped') {
            // Изображение не требует сжатия
            button.text('Не требует сжатия').prop('disabled', true);
            showNotification(response.message, 'warning');
            
        } else {
            // Ошибка сжатия
            button.prop('disabled', false).text('Сжать');
            showNotification('Ошибка: ' + response.message, 'error');
        }
    }
    
    /**
     * Обработка ошибки сжатия
     */
    function handleCompressionError(error, button) {
        button.prop('disabled', false).text('Сжать');
        showNotification('Произошла ошибка при сжатии изображения: ' + error, 'error');
    }
    
    /**
     * Инициализация пакетной обработки
     */
    function initBatchProcessing() {
        var batchProcessing = false;
        var processedCount = 0;
        var totalCount = parseInt($('#sic-total-count').text()) || 0;
        
        $('#sic-batch-start').on('click', function() {
            if (batchProcessing) return;
            
            batchProcessing = true;
            processedCount = 0;
            
            // Обновляем интерфейс
            $(this).hide();
            $('#sic-batch-stop').show();
            $('#sic-batch-progress').show();
            $('#sic-batch-results').empty();
            
            // Начинаем обработку
            processBatch();
        });
        
        $('#sic-batch-stop').on('click', function() {
            batchProcessing = false;
            $(this).hide();
            $('#sic-batch-start').show();
            showNotification('Пакетная обработка остановлена', 'warning');
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
                    updateBatchProgress(processedCount, totalCount);
                    
                    // Показываем результаты
                    displayBatchResults(response.results);
                    
                    if (response.has_more && batchProcessing) {
                        // Продолжаем обработку с задержкой
                        setTimeout(processBatch, 1000);
                    } else {
                        // Завершено
                        finishBatchProcessing();
                    }
                },
                error: function(xhr, status, error) {
                    batchProcessing = false;
                    $('#sic-batch-stop').hide();
                    $('#sic-batch-start').show();
                    showNotification('Ошибка при пакетной обработке: ' + error, 'error');
                }
            });
        }
        
        function updateBatchProgress(processed, total) {
            var percentage = (processed / total) * 100;
            $('.sic-progress-fill').css('width', percentage + '%');
            $('#sic-processed-count').text(processed);
        }
        
        function displayBatchResults(results) {
            var resultsHtml = '';
            results.forEach(function(result) {
                resultsHtml += '<div class="sic-batch-result">';
                resultsHtml += '<strong>' + escapeHtml(result.title) + '</strong>: ';
                
                if (result.result.status === 'success') {
                    resultsHtml += 'Сжато с ' + result.result.original_size + 'KB до ' + result.result.new_size + 'KB ';
                    resultsHtml += '(экономия: ' + result.result.savings + 'KB)';
                } else {
                    resultsHtml += '<span style="color: #dc3545;">Ошибка: ' + escapeHtml(result.result.message) + '</span>';
                }
                
                resultsHtml += '</div>';
            });
            
            $('#sic-batch-results').append(resultsHtml);
        }
        
        function finishBatchProcessing() {
            batchProcessing = false;
            $('#sic-batch-stop').hide();
            $('#sic-batch-start').show();
            updateStats();
            showNotification('Пакетная обработка завершена!', 'success');
        }
    }
    
    /**
     * Инициализация настроек
     */
    function initSettings() {
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
        
        // Валидация формы настроек
        $('form').on('submit', function(e) {
            var maxSize = parseInt($('input[name="max_file_size"]').val());
            var quality = parseInt($('input[name="quality"]').val());
            
            if (maxSize < 50 || maxSize > 2000) {
                e.preventDefault();
                showNotification('Максимальный размер файла должен быть от 50 до 2000 KB', 'error');
                return false;
            }
            
            if (quality < 10 || quality > 100) {
                e.preventDefault();
                showNotification('Качество должно быть от 10 до 100%', 'error');
                return false;
            }
        });
    }
    
    /**
     * Обновление статистики
     */
    function updateStats() {
        // Перезагружаем страницу для обновления статистики
        setTimeout(function() {
            location.reload();
        }, 2000);
    }
    
    /**
     * Показ уведомлений
     */
    function showNotification(message, type) {
        type = type || 'info';
        
        var notification = $('<div class="notice notice-' + type + ' is-dismissible">');
        notification.html('<p>' + escapeHtml(message) + '</p>');
        
        $('.wrap h1').after(notification);
        
        // Автоматически скрываем уведомление через 5 секунд
        setTimeout(function() {
            notification.fadeOut();
        }, 5000);
    }
    
    /**
     * Экранирование HTML
     */
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Проверка поддержки WebP
     */
    function checkWebPSupport() {
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }
    
    // Показываем предупреждение если WebP не поддерживается
    if (!checkWebPSupport()) {
        showNotification('Ваш браузер не поддерживает WebP формат. Рекомендуется использовать JPEG.', 'warning');
    }
    
})(jQuery);

