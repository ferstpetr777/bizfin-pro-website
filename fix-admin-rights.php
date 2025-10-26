<?php
// Fix admin rights and capabilities
require_once('/var/www/bizfin_pro_r_usr/data/www/bizfin-pro.ru/wp-config.php');

echo "<h1>Admin Rights Fix</h1>\n";

// Get all users with admin role
$admin_users = get_users(array('role' => 'administrator'));

echo "<h2>Current Administrator Users:</h2>\n";
foreach ($admin_users as $user) {
    echo "<p><strong>ID:</strong> {$user->ID}, <strong>Login:</strong> {$user->user_login}, <strong>Email:</strong> {$user->user_email}</p>\n";
    
    // Check current capabilities
    $caps = $user->allcaps;
    echo "<p><strong>Capabilities:</strong></p>\n";
    echo "<ul>\n";
    foreach ($caps as $cap => $has_cap) {
        if ($has_cap) {
            echo "<li>✅ $cap</li>\n";
        }
    }
    echo "</ul>\n";
    
    // Ensure user has all admin capabilities
    $user->set_role('administrator');
    
    // Add additional admin capabilities
    $admin_caps = array(
        'manage_options' => true,
        'manage_network' => true,
        'manage_sites' => true,
        'manage_network_users' => true,
        'manage_network_themes' => true,
        'manage_network_plugins' => true,
        'manage_network_options' => true,
        'unfiltered_html' => true,
        'edit_users' => true,
        'list_users' => true,
        'create_users' => true,
        'delete_users' => true,
        'promote_users' => true,
        'edit_theme_options' => true,
        'switch_themes' => true,
        'edit_themes' => true,
        'activate_plugins' => true,
        'edit_plugins' => true,
        'delete_plugins' => true,
        'install_plugins' => true,
        'update_plugins' => true,
        'edit_files' => true,
        'import' => true,
        'export' => true,
        'edit_dashboard' => true,
        'customize' => true,
        'delete_site' => true,
        'update_core' => true,
        'update_themes' => true,
        'install_themes' => true,
        'delete_themes' => true,
        'edit_others_posts' => true,
        'edit_others_pages' => true,
        'edit_published_posts' => true,
        'edit_published_pages' => true,
        'publish_posts' => true,
        'publish_pages' => true,
        'delete_posts' => true,
        'delete_pages' => true,
        'delete_others_posts' => true,
        'delete_others_pages' => true,
        'delete_published_posts' => true,
        'delete_published_pages' => true,
        'read_private_posts' => true,
        'read_private_pages' => true,
        'edit_private_posts' => true,
        'edit_private_pages' => true,
        'delete_private_posts' => true,
        'delete_private_pages' => true
    );
    
    foreach ($admin_caps as $cap => $value) {
        $user->add_cap($cap, $value);
    }
    
    // Update user meta
    update_user_meta($user->ID, 'wp_capabilities', array('administrator' => true));
    update_user_meta($user->ID, 'wp_user_level', 10);
    
    echo "<p>✅ <strong>Fixed capabilities for user: {$user->user_login}</strong></p>\n";
}

// Check if there are any users at all
$all_users = get_users();
if (empty($all_users)) {
    echo "<p>❌ No users found in database!</p>\n";
} else {
    echo "<p>✅ Found " . count($all_users) . " users in database</p>\n";
}

// Create a super admin user if needed
$super_admin = get_user_by('login', 'superadmin');
if (!$super_admin) {
    echo "<h2>Creating Super Admin User:</h2>\n";
    $user_id = wp_create_user('superadmin', 'TempPassword123!', 'admin@bizfin-pro.ru');
    if (!is_wp_error($user_id)) {
        $user = new WP_User($user_id);
        $user->set_role('administrator');
        
        // Add all possible capabilities
        $all_caps = array(
            'administrator' => true,
            'manage_options' => true,
            'manage_network' => true,
            'manage_sites' => true,
            'manage_network_users' => true,
            'manage_network_themes' => true,
            'manage_network_plugins' => true,
            'manage_network_options' => true,
            'unfiltered_html' => true,
            'edit_users' => true,
            'list_users' => true,
            'create_users' => true,
            'delete_users' => true,
            'promote_users' => true,
            'edit_theme_options' => true,
            'switch_themes' => true,
            'edit_themes' => true,
            'activate_plugins' => true,
            'edit_plugins' => true,
            'delete_plugins' => true,
            'install_plugins' => true,
            'update_plugins' => true,
            'edit_files' => true,
            'import' => true,
            'export' => true,
            'edit_dashboard' => true,
            'customize' => true,
            'delete_site' => true,
            'update_core' => true,
            'update_themes' => true,
            'install_themes' => true,
            'delete_themes' => true
        );
        
        foreach ($all_caps as $cap => $value) {
            $user->add_cap($cap, $value);
        }
        
        update_user_meta($user_id, 'wp_capabilities', array('administrator' => true));
        update_user_meta($user_id, 'wp_user_level', 10);
        
        echo "<p>✅ Created super admin user: superadmin</p>\n";
        echo "<p>📧 Email: admin@bizfin-pro.ru</p>\n";
        echo "<p>🔑 Password: TempPassword123!</p>\n";
    } else {
        echo "<p>❌ Failed to create super admin user: " . $user_id->get_error_message() . "</p>\n";
    }
} else {
    echo "<p>✅ Super admin user already exists</p>\n";
}

// Clear any cached user data
wp_cache_flush();

echo "<h2>Fix Complete!</h2>\n";
echo "<p>🎉 Admin rights have been fixed!</p>\n";
echo "<p>🔗 <a href='/wp-admin/'>Try accessing admin panel now</a></p>\n";
?>




