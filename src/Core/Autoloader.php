<?php

namespace ShopMotive\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Autoloader
 * 
 * PSR-4 Autoloader for the ClickSync plugin namespace.
 */
class Autoloader {

	/**
	 * Register the autoloader callback with SPL.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload callback implementation.
	 *
	 * @param string $class Fully qualified class name.
	 */
	public static function autoload( $class ) {
		// Project-specific namespace prefix
		$prefix = 'ShopMotive\\';

		// Base directory for the namespace prefix
		$base_dir = untrailingslashit( SHOPMOTIVE_PATH ) . '/src/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class, $len ) ) {
			return;
		}

		// Get the relative class name
		$relative_class = substr( $class, $len );

		// Replace namespace separators with directory separators and append .php
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// If the file exists, require it
		if ( file_exists( $file ) ) {
			require $file;
		} else {
			// Try lowercase path fallback in case of FTP/server naming alterations
			$lowercase_file = strtolower( $file );
			if ( file_exists( $lowercase_file ) ) {
				require $lowercase_file;
			}
		}
	}
}
