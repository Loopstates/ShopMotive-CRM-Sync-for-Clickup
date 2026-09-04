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
		// Load text domain for translations
		add_action( 'init', array( $this, 'load_textdomain' ) );

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
	 * Load plugin translation text domain.
	 */
	public function load_textdomain() {
		// Translation files are automatically loaded by WordPress.org Translate system
	}

	/**
	 * Plugin activation hook callback.
	 */
	public static function activate() {
		Options::get_settings();
		Options::get_account();
	}

	/**
	 * Plugin deactivation hook callback.
	 */
	public static function deactivate() {
		// Clean up any temporary transients or state if needed.
	}
}
