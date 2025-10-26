/**
 * BizFin Gutenberg Block Manager JavaScript
 * 
 * Обеспечивает работу с Gutenberg блоками для точечных изменений
 */

(function($) {
    'use strict';

    const BizFinBlockManager = {
        
        /**
         * Инициализация
         */
        init: function() {
            this.bindEvents();
            this.registerBlocks();
        },

        /**
         * Привязка событий
         */
        bindEvents: function() {
            // Обработчик для обновления блока
            $(document).on('click', '.bsag-update-block', this.updateBlock);
            
            // Обработчик для удаления дублирующихся изображений
            $(document).on('click', '.bsag-remove-duplicates', this.removeDuplicateImages);
            
            // Обработчик для применения iOS-стилей к изображениям
            $(document).on('click', '.bsag-apply-ios-styles', this.applyIOSStyles);
        },

        /**
         * Регистрация кастомных блоков
         */
        registerBlocks: function() {
            if (typeof wp !== 'undefined' && wp.blocks) {
                // Регистрируем блок вводной секции
                wp.blocks.registerBlockType('bizfin/intro-section', {
                    title: 'BizFin Intro Section',
                    icon: 'admin-post',
                    category: 'bizfin',
                    attributes: {
                        simpleDefinition: {
                            type: 'string',
                            default: ''
                        },
                        sympatheticExample: {
                            type: 'string',
                            default: ''
                        },
                        tocContent: {
                            type: 'string',
                            default: ''
                        }
                    },
                    edit: function(props) {
                        return wp.element.createElement('div', {
                            className: 'bizfin-intro-section-editor'
                        }, [
                            wp.element.createElement('h3', {}, 'BizFin Intro Section'),
                            wp.element.createElement('textarea', {
                                placeholder: 'Простое определение...',
                                value: props.attributes.simpleDefinition,
                                onChange: function(event) {
                                    props.setAttributes({
                                        simpleDefinition: event.target.value
                                    });
                                }
                            }),
                            wp.element.createElement('textarea', {
                                placeholder: 'Симпатичный пример...',
                                value: props.attributes.sympatheticExample,
                                onChange: function(event) {
                                    props.setAttributes({
                                        sympatheticExample: event.target.value
                                    });
                                }
                            })
                        ]);
                    },
                    save: function(props) {
                        return wp.element.createElement('section', {
                            className: 'intro'
                        }, [
                            wp.element.createElement('p', {}, props.attributes.simpleDefinition),
                            wp.element.createElement('p', {}, props.attributes.sympatheticExample)
                        ]);
                    }
                });

                // Регистрируем блок изображения с iOS-стилями
                wp.blocks.registerBlockType('bizfin/article-image', {
                    title: 'BizFin Article Image',
                    icon: 'format-image',
                    category: 'bizfin',
                    attributes: {
                        imageUrl: {
                            type: 'string',
                            default: ''
                        },
                        altText: {
                            type: 'string',
                            default: ''
                        },
                        caption: {
                            type: 'string',
                            default: ''
                        },
                        position: {
                            type: 'string',
                            default: 'after_toc'
                        }
                    },
                    edit: function(props) {
                        return wp.element.createElement('div', {
                            className: 'bizfin-article-image-editor'
                        }, [
                            wp.element.createElement('h3', {}, 'BizFin Article Image'),
                            wp.element.createElement('input', {
                                type: 'url',
                                placeholder: 'URL изображения...',
                                value: props.attributes.imageUrl,
                                onChange: function(event) {
                                    props.setAttributes({
                                        imageUrl: event.target.value
                                    });
                                }
                            }),
                            wp.element.createElement('input', {
                                type: 'text',
                                placeholder: 'Alt текст...',
                                value: props.attributes.altText,
                                onChange: function(event) {
                                    props.setAttributes({
                                        altText: event.target.value
                                    });
                                }
                            })
                        ]);
                    },
                    save: function(props) {
                        return wp.element.createElement('figure', {
                            className: 'article-featured-image ios-style-image',
                            'data-position': props.attributes.position
                        }, [
                            wp.element.createElement('img', {
                                src: props.attributes.imageUrl,
                                alt: props.attributes.altText,
                                loading: 'lazy',
                                width: '960',
                                height: '540'
                            }),
                            props.attributes.caption && wp.element.createElement('figcaption', {}, props.attributes.caption)
                        ]);
                    }
                });
            }
        },

        /**
         * Обновление конкретного блока
         */
        updateBlock: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id');
            const blockType = $button.data('block-type');
            const blockIndex = $button.data('block-index');
            const blockAttributes = $button.data('block-attributes') || {};

            // Показываем индикатор загрузки
            $button.prop('disabled', true).text('Обновление...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bsag_update_block',
                    nonce: bsag_ajax.nonce,
                    post_id: postId,
                    block_type: blockType,
                    block_index: blockIndex,
                    block_attributes: blockAttributes
                },
                success: function(response) {
                    if (response.success) {
                        BizFinBlockManager.showNotice('Блок успешно обновлен', 'success');
                        // Перезагружаем страницу для отображения изменений
                        location.reload();
                    } else {
                        BizFinBlockManager.showNotice('Ошибка обновления блока: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BizFinBlockManager.showNotice('Ошибка соединения с сервером', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Обновить блок');
                }
            });
        },

        /**
         * Удаление дублирующихся изображений
         */
        removeDuplicateImages: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id');

            // Показываем индикатор загрузки
            $button.prop('disabled', true).text('Удаление дубликатов...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bsag_remove_duplicate_images',
                    nonce: bsag_ajax.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        BizFinBlockManager.showNotice('Дублирующиеся изображения удалены', 'success');
                        // Перезагружаем страницу для отображения изменений
                        location.reload();
                    } else {
                        BizFinBlockManager.showNotice('Ошибка удаления дубликатов: ' + response.data, 'error');
                    }
                },
                error: function() {
                    BizFinBlockManager.showNotice('Ошибка соединения с сервером', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Удалить дубликаты');
                }
            });
        },

        /**
         * Применение iOS-стилей к изображениям
         */
        applyIOSStyles: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const postId = $button.data('post-id');

            // Показываем индикатор загрузки
            $button.prop('disabled', true).text('Применение стилей...');

            // Находим все изображения в статье
            $('.article-featured-image').each(function() {
                const $img = $(this);
                if (!$img.hasClass('ios-style-image')) {
                    $img.addClass('ios-style-image');
                }
            });

            BizFinBlockManager.showNotice('iOS-стили применены к изображениям', 'success');
            $button.prop('disabled', false).text('Применить iOS-стили');
        },

        /**
         * Показ уведомлений
         */
        showNotice: function(message, type) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after($notice);
            
            // Автоматически скрываем уведомление через 5 секунд
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        /**
         * Создание блока изображения с правильными стилями
         */
        createStyledImageBlock: function(imageUrl, altText, caption, position) {
            return {
                blockName: 'bizfin/article-image',
                attrs: {
                    imageUrl: imageUrl,
                    altText: altText,
                    caption: caption || '',
                    position: position || 'after_toc'
                },
                innerHTML: '',
                innerContent: [''],
                innerBlocks: []
            };
        },

        /**
         * Парсинг Gutenberg блоков из контента
         */
        parseBlocks: function(content) {
            if (typeof wp !== 'undefined' && wp.blocks) {
                return wp.blocks.parse(content);
            }
            return [];
        },

        /**
         * Конвертация блоков обратно в контент
         */
        blocksToContent: function(blocks) {
            if (typeof wp !== 'undefined' && wp.blocks) {
                return wp.blocks.serialize(blocks);
            }
            return '';
        }
    };

    // Инициализация при загрузке документа
    $(document).ready(function() {
        BizFinBlockManager.init();
    });

    // Экспорт для использования в других скриптах
    window.BizFinBlockManager = BizFinBlockManager;

})(jQuery);

