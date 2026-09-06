<?php

namespace ShopMotive\Core;

use ShopMotive\Admin\AdminMenu;
use ShopMotive\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 * 
 * Main plugin orchestrator singleton.
 */
class Plugin {

	/**
	 * Singleton instance holder.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize plugin hooks.
	 */
	private function init_hooks() {
		// Initialize Admin Interface
		if ( is_admin() ) {
			AdminMenu::init();
		}

		// Initialize WooCommerce Listener
		WooCommerce::init();

		// Initialize REST API endpoints
		add_action( 'rest_api_init', array( \ShopMotive\Api\Webhook::class, 'register_routes' ) );
	}

	/**
	 * Plugin activation hook callback.
	 */
	public static function activate() {
		Options::get_settings();
		Options::get_account();
		Options::get_secret_key();
	}

	/**
	 * Plugin deactivation hook callback.
	 */
	public static function deactivate() {
		// Clean up any temporary transients or state if needed.
	}
}
