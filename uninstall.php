<?php
/**
 * ClickSync Uninstallation File
 * 
 * Called when plugin is uninstalled via WordPress Admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$host = parse_url( site_url(), PHP_URL_HOST );

// Ping cloud to register uninstall before wiping options
wp_remote_post( 'https://clicksync-connect.apps.loopstates.com/api/save-config', array(
	'method'      => 'POST',
	'timeout'     => 5,
	'blocking'    => true, // Block slightly during uninstall to ensure the request is sent
	'headers'     => array(
		'Content-Type' => 'application/json',
	),
	'body'        => wp_json_encode( array(
		'shop'       => $host,
		'actionType' => 'uninstall_plugin',
		'payload'    => array()
	) ),
) );

// Clear plugin options from wp_options table
delete_option( 'clicksync_settings' );
delete_option( 'clicksync_account' );
delete_option( 'clicksync_user_mappings' );
