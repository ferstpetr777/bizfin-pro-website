<?php
/*
Plugin Name: MU Astra Fixes (Header/Hero Colors)
Description: Force white/transparent background in header and first hero section, black text.
*/
if (!defined('ABSPATH')) { exit; }
add_action('wp_head', function(){
  echo '<style id="mu-ast-fixes">\n'
    . '.site-header, .ast-primary-header-bar, .ast-above-header-bar, .ast-below-header-bar{background:#F5F5F5!important;box-shadow:none!important;}\n'
    . '.ast-desktop .ast-primary-header-bar .main-header-menu > .menu-item > a, .ast-builder-menu .menu-item > a, .site-branding a, .ast-header-html, .ast-mobile-header-wrap .menu-toggle, .ast-site-identity .site-title a{color:#000!important;}\n'
    . '.ast-custom-button-link .ast-custom-button, .ast-custom-button-link .ast-custom-button:not(:hover){color:#000!important;border:1px solid #000!important;background:#F5F5F5!important;}\n'
    . '.ast-custom-button-link:hover .ast-custom-button{color:#000!important;border:1px solid #000!important;background:#e5e5e5!important;}\n'
    . '.site-header .ast-custom-button-link .ast-custom-button, .ast-primary-header-bar .ast-custom-button-link .ast-custom-button{color:#000!important;border:1px solid #000!important;background:#F5F5F5!important;}\n'
    . '.site-header .ast-custom-button-link:hover .ast-custom-button, .ast-primary-header-bar .ast-custom-button-link:hover .ast-custom-button{color:#000!important;border:1px solid #000!important;background:#e5e5e5!important;}\n'
    . 'body.home .elementor-section:first-of-type, .elementor-editor-active .elementor-section:first-of-type{background:transparent!important;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-heading-title, .elementor-editor-active .elementor-section:first-of-type .elementor-heading-title, body.home .elementor-section:first-of-type .elementor-widget-heading h1, .elementor-editor-active .elementor-section:first-of-type .elementor-widget-heading h1, body.home .elementor-section:first-of-type .elementor-widget-heading h2, .elementor-editor-active .elementor-section:first-of-type .elementor-widget-heading h2, body.home .elementor-section:first-of-type .elementor-widget-text-editor, .elementor-editor-active .elementor-section:first-of-type .elementor-widget-text-editor, body.home .elementor-section:first-of-type .elementor-widget-text-editor p, .elementor-editor-active .elementor-section:first-of-type .elementor-widget-text-editor p, body.home .elementor-section:first-of-type .elementor-widget, .elementor-editor-active .elementor-section:first-of-type .elementor-widget, body.home .elementor-section:first-of-type .elementor-widget *, .elementor-editor-active .elementor-section:first-of-type .elementor-widget *{color:#000!important;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-icon-list-text, .elementor-editor-active .elementor-section:first-of-type .elementor-icon-list-text{color:#000!important;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-widget:not(.elementor-widget-button) a, .elementor-editor-active .elementor-section:first-of-type .elementor-widget:not(.elementor-widget-button) a{color:#000!important;text-decoration:underline;text-underline-offset:3px;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-button, .elementor-editor-active .elementor-section:first-of-type .elementor-button{background:#fff!important;color:#000!important;border:1px solid #e5e7eb!important;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-button:hover, .elementor-editor-active .elementor-section:first-of-type .elementor-button:hover{background:#f3f4f6!important;}\n'
    . 'body.home .elementor-section:first-of-type .elementor-background-overlay, .elementor-editor-active .elementor-section:first-of-type .elementor-background-overlay{background:transparent!important;opacity:0!important;}\n'
    . '</style>';
});
