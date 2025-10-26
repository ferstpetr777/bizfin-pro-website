<?php
/*
Plugin Name: MU Tenor Sans Font
Description: Enqueue Tenor Sans globally and apply site-wide.
*/
if (!defined('ABSPATH')) { exit; }
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('tenor-sans-font', 'https://fonts.googleapis.com/css2?family=Tenor+Sans&display=swap', [], null);
}, 1);
add_action('wp_head', function(){
  echo '<style id="tenor-sans-global">body, body *{font-family:"Tenor Sans", sans-serif!important;}</style>';
});
