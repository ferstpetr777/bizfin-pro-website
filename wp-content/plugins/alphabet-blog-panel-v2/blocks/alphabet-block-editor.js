(function() {
    'use strict';
    
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    
    registerBlockType('abp-v2/alphabet-menu', {
        title: __('Алфавитное меню', 'abp-v2'),
        description: __('Алфавитная навигация по блогу', 'abp-v2'),
        icon: 'list-view',
        category: 'widgets',
        keywords: [
            __('алфавит', 'abp-v2'),
            __('навигация', 'abp-v2'),
            __('блог', 'abp-v2')
        ],
        
        attributes: {
            showSearch: {
                type: 'boolean',
                default: true
            },
            showTitle: {
                type: 'boolean',
                default: true
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { showSearch, showTitle } = attributes;
            
            return createElement('div', {
                className: 'abp-v2-alphabet-block-editor'
            }, [
                createElement(InspectorControls, {
                    key: 'inspector'
                }, [
                    createElement(PanelBody, {
                        title: __('Настройки', 'abp-v2'),
                        initialOpen: true
                    }, [
                        createElement(ToggleControl, {
                            label: __('Показывать заголовок', 'abp-v2'),
                            checked: showTitle,
                            onChange: (value) => setAttributes({ showTitle: value })
                        }),
                        createElement(ToggleControl, {
                            label: __('Показывать поиск', 'abp-v2'),
                            checked: showSearch,
                            onChange: (value) => setAttributes({ showSearch: value })
                        })
                    ])
                ]),
                createElement('div', {
                    key: 'preview',
                    className: 'abp-v2-alphabet-preview'
                }, [
                    showTitle && createElement('div', {
                        key: 'title',
                        className: 'abp-v2-preview-title'
                    }, __('Алфавитное меню блога', 'abp-v2')),
                    createElement('div', {
                        key: 'alphabet',
                        className: 'abp-v2-preview-alphabet'
                    }, 'А Б В Г Д Е Ж З И К Л М Н О П Р С Т У Ф Х Ц Ч Ш Щ Э Ю Я'),
                    showSearch && createElement('div', {
                        key: 'search',
                        className: 'abp-v2-preview-search'
                    }, __('[Поиск по ключевым словам]', 'abp-v2'))
                ])
            ]);
        },
        
        save: function() {
            // Блок рендерится на сервере
            return null;
        }
    });
})();
