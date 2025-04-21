<?php
/*
* Plugin Name:    Oliver Monschau - Customer Backend
* Plugin URI:     https://omonschau.de/
* Description:    Customizes the WordPress admin area for customers.
* Version:        1.0.5
* Author:         Oliver Monschau, Michael Amting
* Author URI:     https://omonschau.de/
*/

defined('ABSPATH') or die('No script kiddies please!');

// Automatically update plugin from GitHub
require_once plugin_dir_path(__FILE__) . 'github-updater.php';
new WP_GitHub_Updater(plugin_basename(__FILE__));

// Create a Options Page
require_once plugin_dir_path(__FILE__) . 'options-page.php';

// Include admin customizations
require_once plugin_dir_path(__FILE__) . 'admin-customizations.php';

// Add new "WebsiteAdmin" role with custom capabilities
function add_customer_role()
{
    // Rolle "WebsiteAdmin" hinzufügen, falls sie noch nicht existiert
    add_role('WebsiteAdmin', 'WebsiteAdmin', [
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => false,
        'upload_files' => true
    ]);

    // Remove Role 'Website-Admin'
    $role = get_role('Website-Admin');
    if ($role) {
        remove_role('Website-Admin');
    }

    // Fehlende Berechtigungen hinzufügen
    $role = get_role('WebsiteAdmin');
    if ($role) {
        $role->add_cap('manage_options');      // Zugriff auf allgemeine Einstellungen
        $role->add_cap('edit_theme_options');  // Zugriff auf Customizer und Design
    }
}
register_activation_hook(__FILE__, 'add_customer_role');

// Remove "WebsiteAdmin" role on plugin deactivation
function remove_customer_role()
{
    remove_role('WebsiteAdmin');
}
register_deactivation_hook(__FILE__, 'remove_customer_role');
