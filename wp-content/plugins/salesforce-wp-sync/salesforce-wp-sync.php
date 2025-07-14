<?php
/**
 * Plugin Name: Salesforce to WordPress Sync
 * Description: Sync certified company profiles from Salesforce into WordPress custom post type.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/auth.php';
require_once plugin_dir_path(__FILE__) . 'includes/sync.php';
require_once plugin_dir_path(__FILE__) . 'includes/logger.php';

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('sf_sync_salesforce_event')) {
        wp_schedule_event(time(), 'hourly', 'sf_sync_salesforce_event');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('sf_sync_salesforce_event');
});

add_action('sf_sync_salesforce_event', 'sf_sync_salesforce_companies');
