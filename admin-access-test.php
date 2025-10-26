<?php
// Admin access test
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>Admin Access Test</h1>\n";

// Test 1: Check admin users
$admin_users = get_users(array('role' => 'administrator'));
echo "<h2>Administrator Users:</h2>\n";
foreach ($admin_users as $user) {
    echo "<p><strong>Login:</strong> {$user->user_login}, <strong>Email:</strong> {$user->user_email}</p>\n";
    
    // Check key capabilities
    echo "<p><strong>Key Capabilities:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>manage_options: " . (current_user_can_for_user($user->ID, 'manage_options') ? '✅' : '❌') . "</li>\n";
    echo "<li>edit_posts: " . (current_user_can_for_user($user->ID, 'edit_posts') ? '✅' : '❌') . "</li>\n";
    echo "<li>edit_pages: " . (current_user_can_for_user($user->ID, 'edit_pages') ? '✅' : '❌') . "</li>\n";
    echo "<li>activate_plugins: " . (current_user_can_for_user($user->ID, 'activate_plugins') ? '✅' : '❌') . "</li>\n";
    echo "</ul>\n";
}

// Test 2: Check AI-Scribe menu access
echo "<h2>AI-Scribe Menu Access:</h2>\n";
$ai_scribe_pages = array(
    'ai_scribe_help' => 'AI-Scribe Help',
    'ai_scribe_generate_article' => 'Generate Article',
    'ai_scribe_saved_shortcodes' => 'Saved Shortcodes',
    'ai_scribe_settings' => 'Settings'
);

foreach ($ai_scribe_pages as $page => $title) {
    $url = admin_url("admin.php?page=$page");
    echo "<p><strong>$title:</strong> <a href='$url' target='_blank'>$url</a></p>\n";
}

// Test 3: Check WordPress admin access
echo "<h2>WordPress Admin Access:</h2>\n";
$admin_urls = array(
    'Dashboard' => admin_url(),
    'Posts' => admin_url('edit.php'),
    'Pages' => admin_url('edit.php?post_type=page'),
    'Plugins' => admin_url('plugins.php'),
    'Users' => admin_url('users.php'),
    'Settings' => admin_url('options-general.php')
);

foreach ($admin_urls as $title => $url) {
    echo "<p><strong>$title:</strong> <a href='$url' target='_blank'>$url</a></p>\n";
}

echo "<h2>Test Complete!</h2>\n";
echo "<p>🎯 If you can access these links, admin rights are working correctly!</p>\n";
?>




