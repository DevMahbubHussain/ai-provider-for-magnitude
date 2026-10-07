<?php
/**
 * Plugin Name:       AI Provider for Magnitude
 * Description:       Magnitude provider for the WordPress AI Client.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Mahbub Hussain
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       ai-provider-for-magnitude
 *
 * @package AiProviderForMagnitude
 */

declare( strict_types=1 );

namespace AiProviderForMagnitude;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_PROVIDER_FOR_MAGNITUDE_VERSION', '0.1.0' );
define( 'AI_PROVIDER_FOR_MAGNITUDE_PLUGIN_FILE', __FILE__ );
define( 'AI_PROVIDER_FOR_MAGNITUDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Loads a plugin class file from the includes directory.
 *
 * @since 0.1.0
 *
 * @param string $class_name Fully qualified class name.
 */
function autoload( string $class_name ): void {
	$prefix = __NAMESPACE__ . '\\';

	if ( 0 !== strpos( $class_name, $prefix ) ) {
		return;
	}

	$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
	$file     = AI_PROVIDER_FOR_MAGNITUDE_PLUGIN_DIR . 'includes/' . $relative . '.php';

	if ( is_readable( $file ) ) {
		require_once $file;
	}
}

/**
 * Shows an admin notice about an unmet requirement.
 *
 * @since 0.1.0
 *
 * @param string $message The notice text.
 */
function requirement_notice( string $message ): void {
	add_action(
		'admin_notices',
		static function () use ( $message ): void {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
		}
	);
}

/**
 * Starts the plugin once PHP and WordPress meet the requirements.
 *
 * @since 0.1.0
 */
function load(): void {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		requirement_notice(
			sprintf(
				/* translators: %s: Current PHP version. */
				__( 'AI Provider for Magnitude requires PHP 7.4 or higher. You are running PHP %s.', 'ai-provider-for-magnitude' ),
				PHP_VERSION
			)
		);
		return;
	}

	if ( ! is_wp_version_compatible( '7.0' ) ) {
		requirement_notice( __( 'AI Provider for Magnitude requires WordPress 7.0 or higher.', 'ai-provider-for-magnitude' ) );
		return;
	}

	spl_autoload_register( __NAMESPACE__ . '\\autoload' );

	( new Plugin( AI_PROVIDER_FOR_MAGNITUDE_PLUGIN_FILE ) )->init();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\load' );
