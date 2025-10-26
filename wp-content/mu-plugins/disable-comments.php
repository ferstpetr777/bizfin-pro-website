<?php
/*
Plugin Name: Force Disable Comments Sitewide
Description: Globally disables comments and pings for all post types (frontend, admin, REST). Ensures new posts are created with comments closed.
Author: System
Version: 1.0
*/

// Ensure default options are closed
add_action('init', function () {
    if (get_option('default_comment_status') !== 'closed') {
        update_option('default_comment_status', 'closed');
    }
    if (get_option('default_ping_status') !== 'closed') {
        update_option('default_ping_status', 'closed');
    }
});

// Disable support for comments and trackbacks in post types
add_action('admin_init', function () {
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }
        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments and pings on the front end
add_filter('comments_open', '__return_false', 9999);
add_filter('pings_open', '__return_false', 9999);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 9999);

// Remove comments page from admin menu and toolbar
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
}, 9999);
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

// Disable REST API comments endpoints
add_filter('rest_endpoints', function ($endpoints) {
    unset($endpoints['comments']);
    unset($endpoints['/wp/v2/comments']);
    return $endpoints;
});

// Prevent XML-RPC comments
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['wp.newComment']);
    return $methods;
});

// Block direct comment submissions
add_action('pre_comment_on_post', function () {
    wp_die(__('Comments are disabled.'));
}, 0);

// Remove discussion settings section visibility (optional)
add_filter('option_page_capability_discussion', function () {
    return 'manage_options';
});
