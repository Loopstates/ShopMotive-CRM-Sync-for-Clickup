<?php
/**
 * ShopMotive Uninstallation File
 * 
 * Called when plugin is uninstalled via WordPress Admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clear plugin options from wp_options table upon uninstall
delete_option( 'shopmotive_settings' );
delete_option( 'clicksync_settings' );
delete_option( 'shopmotive_account' );
delete_option( 'clicksync_account' );
delete_option( 'shopmotive_user_mappings' );
delete_option( 'clicksync_user_mappings' );
